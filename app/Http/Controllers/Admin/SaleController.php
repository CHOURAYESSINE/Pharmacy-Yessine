<?php

namespace App\Http\Controllers\Admin;

use App\Models\Sale;
use App\Models\Product;
use App\Models\Purchase;
use Illuminate\Http\Request;
use App\Events\PurchaseOutStock;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $title = 'sales';
        if($request->ajax()){
            $sales = Sale::latest();
            return DataTables::of($sales)
                    ->addIndexColumn()
                    ->addColumn('product',function($sale){
                        $image = '';
                        if(!empty($sale->product)){
                            $image = null;
                            if(!empty($sale->product->purchase->image)){
                                $image = '<span class="avatar avatar-sm mr-2">
                                <img class="avatar-img" src="'.asset("storage/purchases/".$sale->product->purchase->image).'" alt="image">
                                </span>';
                            }
                            return $sale->product->purchase->product. ' ' . $image;
                        }                 
                    })
                    ->addColumn('total_price',function($sale){                   
                        return settings('app_currency','$').' '. $sale->total_price;
                    })
                    ->addColumn('date',function($row){
                        return date_format(date_create($row->created_at),'d M, Y');
                    })
                    ->addColumn('action', function ($row) {
                        $editbtn = '<a href="'.route("sales.edit", $row->id).'" class="editbtn"><button class="btn btn-primary"><i class="fas fa-edit"></i></button></a>';
                        $deletebtn = '<a data-id="'.$row->id.'" data-route="'.route('sales.destroy', $row->id).'" href="javascript:void(0)" id="deletebtn"><button class="btn btn-danger"><i class="fas fa-trash"></i></button></a>';
                        if (!auth()->user()->hasPermissionTo('edit-sale')) {
                            $editbtn = '';
                        }
                        if (!auth()->user()->hasPermissionTo('destroy-sale')) {
                            $deletebtn = '';
                        }
                        $btn = $editbtn.' '.$deletebtn;
                        return $btn;
                    })
                    ->rawColumns(['product','action'])
                    ->make(true);

        }
        $products = Product::get();
        return view('admin.sales.index',compact(
            'title','products',
        ));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = 'create sales';
        $products = Product::get();
        return view('admin.sales.create',compact(
            'title','products'
        ));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request,[
            'product'=>'required',
            'quantity'=>'required|integer|min:1'
        ]);
        DB::transaction(function() use ($request) {
            $product=Product::findOrFail($request->product);
            $purchase=Purchase::whereKey($product->purchase_id)->lockForUpdate()->firstOrFail();
            if (\Illuminate\Support\Carbon::parse($purchase->expiry_date)->startOfDay()->lte(now()->startOfDay())) {
                throw \Illuminate\Validation\ValidationException::withMessages(['product'=>'This product is expired.']);
            }
            if ((int)$purchase->quantity < (int)$request->quantity) {
                throw \Illuminate\Validation\ValidationException::withMessages(['quantity'=>'Insufficient stock.']);
            }
            $purchase->update(['quantity'=>(int)$purchase->quantity-(int)$request->quantity]);
            Sale::create(['product_id'=>$product->id,'quantity'=>(int)$request->quantity,'total_price'=>round($request->quantity*$product->price,2)]);
            if ($purchase->quantity<=1) event(new PurchaseOutStock($purchase));
        });
        return redirect()->route('sales.index')->with(notify('Product has been sold.'));
    }

    

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \app\Models\Sale $sale
     * @return \Illuminate\Http\Response
     */
    public function edit(Sale $sale)
    {
        $title = 'edit sale';
        $products = Product::get();
        return view('admin.sales.edit',compact(
            'title','sale','products'
        ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \app\Models\Sale $sale
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Sale $sale)
    {
        $this->validate($request,[
            'product'=>'required',
            'quantity'=>'required|integer|min:1'
        ]);
        $request->validate(['product'=>'required|exists:products,id','quantity'=>'required|integer|min:1']);
        DB::transaction(function() use($request,$sale) {
            $sale=Sale::whereKey($sale->id)->lockForUpdate()->firstOrFail();
            $old=Product::withTrashed()->findOrFail($sale->product_id);
            $product=Product::findOrFail($request->product);
            $ids=[$old->purchase_id,$product->purchase_id];sort($ids);
            $locked=Purchase::whereIn('id',$ids)->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $oldPurchase=$locked[$old->purchase_id];$purchase=$locked[$product->purchase_id];
            if (\Illuminate\Support\Carbon::parse($purchase->expiry_date)->startOfDay()->lte(now()->startOfDay())) {
                throw \Illuminate\Validation\ValidationException::withMessages(['product'=>'This product is expired.']);
            }
            $available=(int)$purchase->quantity+($old->purchase_id===$product->purchase_id?(int)$sale->quantity:0);
            if($available < (int)$request->quantity)throw \Illuminate\Validation\ValidationException::withMessages(['quantity'=>'Insufficient stock.']);
            if($old->purchase_id!==$product->purchase_id)$oldPurchase->update(['quantity'=>(int)$oldPurchase->quantity+(int)$sale->quantity]);
            $purchase->update(['quantity'=>$available-(int)$request->quantity]);
            $sale->update(['product_id'=>$product->id,'quantity'=>(int)$request->quantity,'total_price'=>round($request->quantity*$product->price,2)]);
            if ($purchase->quantity<=1) event(new PurchaseOutStock($purchase));
        });
        return redirect()->route('sales.index')->with(notify('Sale updated.'));
    }

    /**
     * Generate sales reports index
     *
     * @return \Illuminate\Http\Response
     */
    public function reports(Request $request){
        $title = 'sales reports';
        return view('admin.sales.reports',compact(
            'title'
        ));
    }

    /**
     * Generate sales report form post
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function generateReport(Request $request){
        $this->validate($request,[
            'from_date' => 'required',
            'to_date' => 'required',
        ]);
        $title = 'sales reports';
        $sales = Sale::whereBetween(DB::raw('DATE(created_at)'), array($request->from_date, $request->to_date))->get();
        return view('admin.sales.reports',compact(
            'sales','title'
        ));
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        return DB::transaction(function()use($request){
            $sale=Sale::whereKey($request->id)->lockForUpdate()->firstOrFail();
            $product=Product::withTrashed()->findOrFail($sale->product_id);
            $purchase=Purchase::whereKey($product->purchase_id)->lockForUpdate()->firstOrFail();
            $purchase->update(['quantity'=>(int)$purchase->quantity+(int)$sale->quantity]);
            return $sale->delete();
        });
    }
}
