<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $title = 'Suppliers';

        if($request->ajax()){
            // Récupérer tous les fournisseurs
            $suppliers = Supplier::select(['id', 'name', 'email', 'phone', 'company', 'address', 'comment']);

            return DataTables::of($suppliers)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $editbtn = auth()->user()->hasPermissionTo('edit-supplier') 
                        ? '<a href="'.route("suppliers.edit", $row->id).'" class="editbtn"><button class="btn btn-primary"><i class="fas fa-edit"></i></button></a>'
                        : '';
                    
                    $deletebtn = auth()->user()->hasPermissionTo('destroy-supplier') 
                        ? '<a data-id="'.$row->id.'" data-route="'.route('suppliers.destroy', $row->id).'" href="javascript:void(0)" id="deletebtn"><button class="btn btn-danger"><i class="fas fa-trash"></i></button></a>'
                        : '';

                    return $editbtn.' '.$deletebtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('admin.suppliers.index', compact('title'));
    }

    public function create()
    {
        $title = 'Create Supplier';
        return view('admin.suppliers.create', compact('title'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|min:3|max:255',
            'email' => 'nullable|email|string|max:255',
            'phone' => 'nullable|string|min:10|max:20',
            'company' => 'nullable|string|max:200',
            'address' => 'nullable|string|max:200',
            'comment' => 'nullable|string|max:255',
        ]);

        Supplier::create($request->only(['name','email','phone','company','address','comment']));

        $notification = notify("Supplier has been added");
        return redirect()->route('suppliers.index')->with($notification);
    }

    public function edit(Supplier $supplier)
    {
        $title = 'Edit Supplier';
        return view('admin.suppliers.edit', compact('title', 'supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $request->validate([
            'name' => 'required|min:3|max:255',
            'email' => 'nullable|email|string|max:255',
            'phone' => 'nullable|string|min:10|max:20',
            'company' => 'nullable|string|max:200',
            'address' => 'nullable|string|max:200',
            'comment' => 'nullable|string|max:255',
        ]);

        $supplier->update($request->only(['name','email','phone','company','address','comment']));

        $notification = notify("Supplier has been updated");
        return redirect()->route('suppliers.index')->with($notification);
    }

    public function destroy(Request $request)
    {
        Supplier::findOrFail($request->id)->delete();
        return response()->json(['success' => 'Supplier deleted successfully.']);
    }
}
