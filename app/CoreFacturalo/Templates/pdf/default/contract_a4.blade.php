@php
    $establishment = $document->establishment;
    $customer = $document->customer;
    $phone = $document->account_number;
    $document->delivery_hour = date( "g:i a", strtotime( $document->delivery_hour ) );

    //$path_style = app_path('CoreFacturalo'.DIRECTORY_SEPARATOR.'Templates'.DIRECTORY_SEPARATOR.'pdf'.DIRECTORY_SEPARATOR.'style.css');
    $accounts = \App\Models\Tenant\BankAccount::all();
    $tittle = $document->prefix.'-'.str_pad($document->id, 8, '0', STR_PAD_LEFT);
@endphp
<html>
<head>
    {{--<title>{{ $tittle }}</title>--}}
    {{--<link href="{{ $path_style }}" rel="stylesheet" />--}}
</head>
<body>
<table class="full-width">
    <tr>
        @if($company->logo)
            <td width="20%">
                <div class="company_logo_box">
                    <img src="data:{{mime_content_type(public_path("storage/uploads/logos/{$company->logo}"))}};base64, {{base64_encode(file_get_contents(public_path("storage/uploads/logos/{$company->logo}")))}}" alt="{{$company->name}}" class="company_logo" style="max-width: 150px;">
                </div>
            </td>
        @else
            <td width="20%">
                {{--<img src="{{ asset('logo/logo.jpg') }}" class="company_logo" style="max-width: 150px">--}}
            </td>
        @endif
        <td width="50%" class="pl-3">
            <div class="text-left">
                <h4 class="">{{ $company->name }}</h4>
                <h5>{{ 'RUC '.$company->number }}</h5>
                <h6 style="text-transform: uppercase;">
                    {{ ($establishment->address !== '-')? $establishment->address : '' }}
                    {{ ($establishment->district_id !== '-')? ', '.$establishment->district->description : '' }}
                    {{ ($establishment->province_id !== '-')? ', '.$establishment->province->description : '' }}
                    {{ ($establishment->department_id !== '-')? '- '.$establishment->department->description : '' }}
                </h6>

                @isset($establishment->trade_address)
                    <h6>{{ ($establishment->trade_address !== '-')? 'D. Comercial: '.$establishment->trade_address : '' }}</h6>
                @endisset
                <h6>{{ ($establishment->telephone !== '-')? 'Central telefónica: '.$establishment->telephone : '' }}</h6>

                <h6>{{ ($establishment->email !== '-')? 'Email: '.$establishment->email : '' }}</h6>

                @isset($establishment->web_address)
                    <h6>{{ ($establishment->web_address !== '-')? 'Web: '.$establishment->web_address : '' }}</h6>
                @endisset

                @isset($establishment->aditional_information)
                    <h6>{{ ($establishment->aditional_information !== '-')? $establishment->aditional_information : '' }}</h6>
                @endisset
            </div>
        </td>
        <td width="30%" class="border-box py-4 px-2 text-center">
            <h5 class="text-center">CONTRATO</h5>
            <h3 class="text-center">{{ $tittle }}</h3>
        </td>
    </tr>
</table>
<table class="full-width mt-5">
    <tr>
        <td style="font-size:12px" width="15%">Cliente:</td>
        <td style="font-size:12px" width="45%">{{ $customer->name }}</td>
        <td style="font-size:12px" width="25%">Fecha de emisión:</td>
        <td style="font-size:12px" width="15%">{{ $document->date_of_issue->format('Y-m-d') }}</td>
    </tr>
    <tr>
        <td style="font-size:12px">{{ $customer->identity_document_type->description }}:</td>
        <td style="font-size:12px">{{ $customer->number }}</td>
        @if($document->date_of_due)
            <td style="font-size:12px" width="25%">Fecha de vencimiento:</td>
            <td style="font-size:12px" width="15%">{{ $document->date_of_due->format('Y-m-d') }}</td>
        @endif
    </tr>
    @if ($customer->address !== '')
    <tr>
        <td style="font-size:12px" class="align-top">Dirección:</td>
        <td style="font-size:12px" colspan="">
            {{ $customer->address }}
            {{ ($customer->district_id !== '-')? ', '.$customer->district->description : '' }}
            {{ ($customer->province_id !== '-')? ', '.$customer->province->description : '' }}
            {{ ($customer->department_id !== '-')? '- '.$customer->department->description : '' }}
        </td>
        @if($document->delivery_date)
            <td style="font-size:12px" width="25%">Fecha de entrega:</td>
            <td style="font-size:12px" width="15%">{{ $document->delivery_date->format('Y-m-d') }}</td>
        @endif
        
    </tr>
    @endif
    @if ($document->payment_method_type)
    <tr>
        <td style="font-size:12px" class="align-top">T. Pago:</td>
        <td style="font-size:12px" colspan="">
            {{ $document->payment_method_type->description }}
        </td>
        @if($document->quotation)
            <td style="font-size:12px" width="25%">Cotización:</td>
            <td style="font-size:12px" width="15%">{{ $document->quotation->number_full }}</td>
        @endif

        @if($document->delivery_hour)
            <td style="font-size:12px" width="25%">Hora de entrega:</td>
            <td style="font-size:12px" width="15%">{{ $document->delivery_hour }}</td>
        @endif
    </tr>
    @endif
    
    @if ($document->shipping_address)
    <tr>
        <td style="font-size:12px" class="align-top">Dir. Envío:</td>
        <td style="font-size:12px" colspan="3">
            {{ $document->shipping_address }}
        </td>
    </tr>
    @endif
    @if ( $document->telephone )
    <tr>
        <td style="font-size:12px" class="align-top">Teléfono:</td>
        <td style="font-size:12px" colspan="3">
            {{ $document->telephone }}
        </td>
    </tr>
    @endif
    <tr>
        <td style="font-size:12px" class="align-top">Vendedor:</td>
        <td style="font-size:12px" colspan="3">
            {{ $document->user->name }}
        </td>
    </tr>
</table>

<table class="full-width mt-3">
    @if ($document->description)
        <tr>
            <td style="font-size:12px" width="15%" class="align-top">Descripción: </td>
            <td style="font-size:12px" width="85%">{{ $document->description }}</td>
        </tr>
    @endif
</table>

@if ($document->guides)
<br/>
{{--<strong>Guías:</strong>--}}
<table>
    @foreach($document->guides as $guide)
        <tr>
            @if(isset($guide->document_type_description))
            <td>{{ $guide->document_type_description }}</td>
            @else
            <td>{{ $guide->document_type_id }}</td>
            @endif
            <td>:</td>
            <td>{{ $guide->number }}</td>
        </tr>
    @endforeach
</table>
@endif

<table class="full-width mt-10 mb-10">
    <thead class="">
    <tr class="bg-grey">
        <!-- <th class="border-top-bottom text-center py-2" style="border-left:1px solid black" width="5%">CANT</th> -->
        <!-- <th class="border-top-bottom text-center py-2" style="border-left:1px solid black" width="10%">UNIDAD</th> -->
        <th class="border-top-bottom text-center py-2" style="border-left:1px solid black" width="16%">Descripción</th>
        <!-- <th class="border-top-bottom text-center py-2" style="border-left:1px solid black" width="20%">TEMATICA</th> -->
        <th class="border-top-bottom text-center py-2" style="border-left:1px solid black" width="25%">Detalles y Temática</th>
        <th class="border-top-bottom text-center py-2" style="border-left:1px solid black" width="24%">Imagen Referencial</th>
    </tr>
    </thead>
    <tbody>
    @foreach($document->items as $row)
        <tr style="border-bottom: 1px solid #ccc">
            <!-- <td class="text-center align-top">
                @if(((int)$row->quantity != $row->quantity))
                    {{ $row->quantity }}
                @else
                    {{ number_format($row->quantity, 0) }}
                @endif
            </td>
            <td class="text-center align-top" >{{ $row->item->unit_type_id }}</td> -->
            <td width="35%" class="text-left align-top" >
                
                @if($row->name_product_pdf)
                    {!!$row->name_product_pdf!!}
                @else
                    {!!$row->item->description!!} 
                @endif
                
                @if (!empty($row->item->presentation)) {!!$row->item->presentation->description!!} @endif
                
                @if($row->attributes)
                    @foreach($row->attributes as $attr)
                        <br/><br/><span style="font-size: 11px">{!! $attr->description !!} : {{ $attr->value }}</span>
                    @endforeach
                @endif
                @if($row->discounts)
                    @foreach($row->discounts as $dtos)
                        <br/><span style="font-size: 9px">{{ $dtos->factor * 100 }}% {{$dtos->description }}</span>
                    @endforeach
                @endif

                @if($row->item->is_set == 1)
                 <br>
                @inject('itemSet', 'App\Services\ItemSetService')
                    {{join( "-", $itemSet->getItemsSet($row->item_id) )}}
                @endif

            </td>
            <!-- <td class="text-left align-top" >{{ $row->tematica }}</td> -->
            <td width="15%" class="text-left align-top" >{{ $row->details }}</td>
            <td width="35%" class="text-right align-top">
                <div style="display: flex;justify-content: center; align-items: center;">
                    <img src="{{ storage_path('tmp_items/' . $row->imageurl) }}" style="width:40%;height:auto;">
                </div>
            </td>
        </tr>
    @endforeach
       
    </tbody>
</table>
<table class="full-width">
    
    <tr>
        {{-- <td width="65%">
            @foreach($document->legends as $row)
                <p>Son: <span class="font-bold">{{ $row->value }} {{ $document->currency_type->description }}</span></p>
            @endforeach
            <br/>
            <strong>Información adicional</strong>
            @foreach($document->additional_information as $information)
                <p>@if(\App\CoreFacturalo\Helpers\Template\TemplateHelper::canShowNewLineOnObservation())
                            {!! \App\CoreFacturalo\Helpers\Template\TemplateHelper::SetHtmlTag($information) !!}
                        @else
                            {{$information}}
                        @endif</p>
            @endforeach
        </td> --}}
    </tr>
</table>
<br>

@if ($document->seller->name)
<br>
<table class="full-width">
    <tr>
        <td>
            <strong>Vendedor:</strong>
        </td>
    </tr>
    <tr>
        <td>{{ $document->seller->name }}</td>
    </tr>
</table>
@endif
</body>
</html>
