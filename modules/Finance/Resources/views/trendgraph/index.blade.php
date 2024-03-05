@extends('tenant.layouts.app')

@section('content')

    <tenant-finance-trendgraph-index
        :ismovements="{{$isMovements}}"
        :configuration="{{\App\Models\Tenant\Configuration::getPublicConfig()}}"
    ></tenant-finance-trendgraph-index>

@endsection
