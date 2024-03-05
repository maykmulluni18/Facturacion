<html>
<head>
    {{--<title>{{ $tittle }}</title>--}}
    {{--<link href="{{ $path_style }}" rel="stylesheet" />--}}
</head>
<body>


<table class="full-width">
    <tr>
        <td width="40%" class="align-top"> <span>Receta :</span> </td>
        <td width="70%" >{{ $records['name']}}</td>
    </tr>
    <tr>
        <td width="40%" class="align-top">Precio de Venta: </td>
        <td width="70%">S/ {{ $records['sale_price'] }}</td>
    </tr>
    <tr>
        <td width="40%" class="align-top">Fecha Creada: </td>
        <td width="70%">{{ $records['created_at'] }}</td>
    </tr>
</table>

<table class="full-width">
    <thead class="">
    <tr class="bg-grey">
        <th class="border-top-bottom text-center py-2" style="border-left:1px solid black;background:#808080" width="35%">Items Receta/Sub-Receta </th>
        <th class="border-top-bottom text-center py-2" style="border-left:1px solid black;background:#808080" width="35%">CIF </th>
    </tr>
    </thead>
    <tbody>
        <tr >
            <td style="background:#f0ffff;text-align: left; vertical-align: top;" >
                @foreach($records['subrecipes_supplies'] as $row)
                    - Nombre:   {{ $row->name }}
                    <br/>
                    Cantidad: {{ $row->quantity }}
                    <br/>
                    Unidad: {{ $row->unit }}
                    <br/>
                    Costo de Item: {{ $row->costs_by_grams }}
                    <br/>

                @endforeach
            </td>
            <td class="" style="background:#f0ffff;text-align: left; vertical-align: top;"  >
                @foreach($records['cif'] as $rowww)
                    - Nombre:   {{ $rowww->name }}
                    <br/>
                    Gasto Mensual {{ $rowww->spent_month }}
                    <br/>
                    Horas Trabajadas por dia: {{ $rowww->hours_work_day }}
                    <br/>
                    Horas Utiles en el Proceso : {{ $rowww->hours_util_process }}
                    <br/>
                    Costo de CIF : S/{{ $rowww->costs_total }} 
                    <br/>

                @endforeach
            </td>
        </tr>
       
    </tbody>
</table>
<table>
    <tr class="full-width">
        <td width="30%" class="border-box py-4 px-2 text-center">
            sdlfsodfsoi
        </td>
    </tr>
</table>
<table class="full-width">
    <tr class="bg-grey">
        <th class="border-top-bottom text-center py-2" style="border-left:1px solid black;" width="35%">
            Total Gramos: {{ $records['total_grams'] }}
            <br>
            Total Costos: {{ $records['total_costs'] }}
        </th>
        <th class="border-top-bottom text-left" style="border-left:1px solid black;vertical-align:top" width="35%">
            Total CIF: {{ $records['sum_cif'] }}
        </th>
    </tr>
</table>
<table class="full-width">
        <td class="border-top-bottom text-center py-2" style="border-left:1px solid black;vertical-align:top;text-align: center" width="35%">Costo Total + CIF</td>
        <td class="" style="text-align: left; vertical-align: top;"> {{ $records['sum_cif'] + $records['total_costs'] }}</td>

</table>

<table class="full-width">
        @if ($records['type_doc'] == 'recipe')
            <td class="border-top-bottom text-center py-2" style="border-left:1px solid black;vertical-align:top;text-align: center" width="35%">Costos </td>
            <td class="" style="text-align: left; vertical-align: top;" >
                - Margen de Costo Soles:S/ {{ $records['costs']->margin_costs_soles }}
                <br/>
                - Margen de Costo Porcentage: {{ $records['costs']->margin_costs_procentage }} %
            </td>
        @endif
    
</table>
</body>
</html>
