@extends('tenant.layouts.app')

@section('content')

    <tenant-finance-statewinlose-index
        :ismovements="{{$isMovements}}"
        :configuration="{{\App\Models\Tenant\Configuration::getPublicConfig()}}"
    ></tenant-finance-statewinlose-index>

@endsection
