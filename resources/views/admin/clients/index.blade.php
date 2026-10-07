@extends('admin.layouts.app')

<x-assets.datatables />

@push('page-header')
    <div class="col-sm-7 col-auto">
        <h3 class="page-title">Clients</h3>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Clients</li>
        </ul>
    </div>
    <div class="col-sm-5 col">
        <a href="{{ route('clients.create') }}" class="btn btn-primary float-right mt-2">Add New</a>
    </div>
@endpush

@section('content')
    <div class="row">
        <div class="col-md-12">

            <!-- Clients -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="client-table" class="datatable table table-hover table-center mb-0">
                            <thead>
                            <tr>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Address</th>
                                <th class="action-btn">Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($clients as $client)
                                <tr>
                                    <td>{{ $client->name }}</td>
                                    <td>{{ $client->phone }}</td>
                                    <td>{{ $client->email }}</td>
                                    <td>{{ $client->address }}</td>
                                    <td>
                                        <div class="actions">
                                            <a class="btn btn-sm bg-success-light" href="{{ route('clients.edit', $client) }}">
                                                <i class="fe fe-pencil"></i> Edit
                                            </a>
                                            <form action="{{ route('clients.destroy', $client) }}" method="POST" style="display:inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm bg-danger-light" type="submit">
                                                    <i class="fe fe-trash"></i> Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                        {{-- pagination Laravel si tu en as besoin --}}
                        {{ $clients->links() }}
                    </div>
                </div>
            </div>
            <!-- /Clients-->

        </div>
    </div>
@endsection

@push('page-js')
    <script>
        $(document).ready(function () {
            $('#client-table').DataTable(); // UNE SEULE initialisation, sans serverSide ni ajax
        });
    </script>
@endpush
