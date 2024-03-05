@extends('tenant.layouts.app')

@section('content')

    <tenant-finance-breakpointmonth-index
        :ismovements="{{$isMovements}}"
        :configuration="{{\App\Models\Tenant\Configuration::getPublicConfig()}}"
    ></tenant-finance-breakpointmonth-index>

@endsection
