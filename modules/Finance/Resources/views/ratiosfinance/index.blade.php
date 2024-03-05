@extends('tenant.layouts.app')

@section('content')

    <tenant-finance-ratiosfinance-index
        :ismovements="{{$isMovements}}"
        :configuration="{{\App\Models\Tenant\Configuration::getPublicConfig()}}"
    ></tenant-finance-ratiosfinance-index>

@endsection
