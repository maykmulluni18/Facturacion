<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Recetas-Sub-Recetas</title>
  <link href="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
 <style>

.breadcrumb li {
    display: inline-block;
    font-size: 18px;
}
.breadcrumb li::after {
    content: "\0000a0 \0000a0 \0000a0 \0000a0    ";
}
.breadcrumb li:last-child::after {
    content: "";
}
.breadcrumb {
    list-style: none;
}
</style>
</head>
<body>
<div class="container">
    <div class="row">
        <div class="col-12">
          <label for=""> <span>Nombre de la  {{ $records['type_doc'] == 'recipe' ? "Receta" : "Sub-Receta" }} : </span> {{ $records['name'] }} </label>
          <br>
          <br>
          <label for=""> <span>Precio de Venta :</span> S/  {{ $records['sale_price'] }} </label>
          <br>
          <br>
          <label for=""><span>Fecha de Creacion :</span>  {{ $records['created_at'] }} </label>
          <br>
          <br>
       </div> 
   </div>
   <table class="bordered" style="width: 100%;">
    <thead>
        <tr>
            <th class="border-top-bottom text-center py-2" style="border-left:1px solid black;background:#cecaca"> <span>Items Receta/Sub-Receta</span></th>
            <th class="border-top-bottom text-center py-2" style="border-left:1px solid black;background:#cecaca"> <span>CIF</span></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td style="background:#f0ffff;text-align: left; vertical-align: top;" >
                @foreach($records['subrecipes_supplies'] as $row)
                    - Nombre:   {{ $row->name }}
                    <br/>
                    Cantidad: {{ $row->quantity }}
                    <br/>
                    Unidad: {{ $row->unit }}
                    <br/>
                    Costo de Item: S/ {{ $row->costs_by_grams }}
                    <br/>

                @endforeach
            </td>
            <td class="" style="background:#f0ffff;text-align: left; vertical-align: top;"  >
                @foreach($records['cif'] as $rowww)
                    - Nombre:   {{ $rowww->name }}
                    <br/>
                    Gasto Mensual S/ {{ $rowww->spent_month }}
                    <br/>
                    Horas Trabajadas por dia: {{ $rowww->hours_work_day }}
                    <br/>
                    Horas Utiles en el Proceso : {{ $rowww->hours_util_process }}
                    <br/>
                    Costo de CIF : S/ {{ $rowww->costs_total }} 
                    <br/>

                @endforeach
            </td>
        </tr>
    </tbody>
   </table>
   <br>
   <hr>
   
</div>
<ul class="breadcrumb">
    <li><span>-Total Gramos: </span> {{ $records['total_grams'] }} <span> - Total Costos: S/ </span> {{ $records['total_costs'] }}</li>
    <li></li>
    <li><span >-Total CIF: S/ </span> {{ $records['sum_cif'] }} </li>
</ul>

<p style="text-align: center;" for=""> <span>Costo Total + CIF: S/ </span> {{ $records['sum_cif'] + $records['total_costs'] }} </p>
<hr> 
<br>     
@if ($records['type_doc'] == 'recipe')
    <label for=""> <span>Margen de Costo Soles:S/ </span> {{ $records['costs']->margin_costs_soles }} </label>
    <br>
    <br>
    <label for=""> <span>Margen de Costo Porcentage: </span> {{ $records['costs']->margin_costs_procentage }} % </label>
@endif

</body>
</html>