@extends('admin.layouts.app')

@push('page-css')
@endpush

@push('page-header')
    <div class="col-sm-12">
        <h3 class="page-title">Edit Client</h3>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Edit Client</li>
        </ul>
    </div>
@endpush

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body custom-edit-service">

                    <!-- Edit Client -->
                    <form method="post" action="{{ route('clients.update', $client) }}">
                        @csrf
                        @method('PUT')

                        <div class="service-fields mb-3">
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>Name <span class="text-danger">*</span></label>
                                        <input class="form-control" type="text"
                                               name="name"
                                               value="{{ $client->name ?? old('name') }}">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <label>Email <span class="text-danger">*</span></label>
                                    <input class="form-control" type="email"
                                           name="email"
                                           value="{{ $client->email ?? old('email') }}">
                                </div>
                            </div>
                        </div>

                        <div class="service-fields mb-3">
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>Phone <span class="text-danger">*</span></label>
                                        <input class="form-control" type="text"
                                               name="phone"
                                               value="{{ $client->phone ?? old('phone') }}">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <label>Address <span class="text-danger">*</span></label>
                                    <input class="form-control" type="text"
                                           name="address"
                                           value="{{ $client->address ?? old('address') }}">
                                </div>
                            </div>
                        </div>

                        <div class="submit-section">
                            <button class="btn btn-primary submit-btn" type="submit" name="form_submit" value="submit">
                                Submit
                            </button>
                        </div>
                    </form>
                    <!-- /Edit Client -->

                </div>
            </div>
        </div>
    </div>
@endsection

@push('page-js')
    <script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}"></script>
@endpush
