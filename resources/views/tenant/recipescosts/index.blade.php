@extends('tenant.layouts.app')

@section('content')

    <tenant-recipescosts-index
        :soap-company="{{ json_encode($soap_company) }}"
        :type-user="{{ json_encode(auth()->user()->type) }}"
        :configuration="{{\App\Models\Tenant\Configuration::getPublicConfig()}}"
    ></tenant-recipescosts-index>

@endsection
