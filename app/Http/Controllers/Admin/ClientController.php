<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class ClientController extends Controller
{
    /**
     * Liste des clients (vue + DataTables Ajax).
     */
    public function index(Request $request)
    {
        $title = 'clients';

        // Réponse JSON pour DataTables (appel Ajax)
        if ($request->ajax()) {
            $clients = Client::query();

            return DataTables::of($clients)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $editbtn = '<a href="'.route("clients.edit", $row->id).'" class="editbtn">
                                    <button class="btn btn-primary"><i class="fas fa-edit"></i></button>
                                </a>';

                    $deletebtn = '<a data-id="'.$row->id.'" data-route="'.route('clients.destroy', $row->id).'"
                                      href="javascript:void(0)" id="deletebtn">
                                      <button class="btn btn-danger"><i class="fas fa-trash"></i></button>
                                  </a>';

                    return $editbtn.' '.$deletebtn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        // Données pour l’affichage initial de la vue
        $clients = Client::latest()->paginate(10);

        return view('admin.clients.index', compact('title', 'clients'));
    }

    /**
     * Formulaire de création.
     */
    public function create()
    {
        $title = 'create client';
        return view('admin.clients.create', compact('title'));
    }

    /**
     * Enregistrement d’un nouveau client.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'nullable|email|max:255',
            'phone'   => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
        ]);

        Client::create($request->only('name', 'email', 'phone', 'address'));

        return redirect()
            ->route('clients.index')
            ->with(notify('Client ajouté avec succès.'));
    }

    /**
     * Formulaire d’édition.
     */
    public function edit(Client $client)
    {
        $title = 'edit client';
        return view('admin.clients.edit', compact('title', 'client'));
    }

    /**
     * Mise à jour d’un client.
     */
    public function update(Request $request, Client $client)
    {
        $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'nullable|email|max:255',
            'phone'   => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
        ]);

        $client->update($request->only('name', 'email', 'phone', 'address'));

        return redirect()
            ->route('clients.index')
            ->with(notify('Client mis à jour avec succès.'));
    }

    /**
     * Suppression via Ajax (comme SupplierController).
     */
    public function destroy(Request $request)
    {
        return Client::findOrFail($request->id)->delete();
    }
}
