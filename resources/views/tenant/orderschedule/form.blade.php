@extends('tenant.layouts.app')

@section('content')

    <tenant-orderschedule-form
        :id="{{ json_encode($id) }}"
        :type-user="{{json_encode(Auth::user()->type)}}"
        :auth-user="{{json_encode(Auth::user()->getDataOnlyAuthUser())}}"
        :configuration="{{\App\Models\Tenant\Configuration::getPublicConfig()}}"
    ></tenant-orderschedule-form>

@endsection