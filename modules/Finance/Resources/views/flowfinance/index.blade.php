@extends('tenant.layouts.app')

@section('content')

    <tenant-finance-flowfinance-index
        :ismovements="{{$isMovements}}"
        :configuration="{{\App\Models\Tenant\Configuration::getPublicConfig()}}"
    ></tenant-finance-flowfinance-index>

@endsection
