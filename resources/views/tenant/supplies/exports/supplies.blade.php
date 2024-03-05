@php
    @endphp<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type"
          content="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Insumos</title>
</head>
<body>
<div>
    <h3 align="center" class="title"><strong>Reporte Insumos</strong></h3>

</div>
<br>
@if(!empty($records))
    <div class="">
        <div class=" ">
            <table class="">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Nombre</th>
                    <th>Nombre alternativo</th>
                    <th>Precio Unitario</th>
                    <th>Unidad de medida</th>
                    <th>Categoría</th>
                </tr>
                </thead>
                <tbody>
                @foreach($records as $key => $value)
                    <tr>
                        <td class="celda">{{$loop->iteration}}</td>
                        <td class="celda">{{$value->name}}</td>
                        <td class="celda">{{$value->second_name}}</td>
                        <td class="celda">{{$value->costs_unit }}</td>
                        <td class="celda">{{$value->unit }}</td>
                        <td class="celda">{{$value->category_name }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@else
    <div>
        <p>No se encontraron registros.</p>
    </div>
@endif
</body>
</html>
