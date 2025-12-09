<!DOCTYPE html>
<html>
<head>
    <title>Liquidación a Propietario</title>
    <style>
        body { font-family: sans-serif; padding: 20px; }
        .header { text-align: center; border-bottom: 2px solid #333; margin-bottom: 20px; }
        .titulo { font-size: 22px; font-weight: bold; }
        .subtitulo { color: #555; }
        .tabla { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .tabla th, .tabla td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .tabla th { background-color: #f2f2f2; }
        .totales { margin-top: 30px; float: right; width: 300px; }
        .fila-total { display: flex; justify-content: space-between; font-weight: bold; margin-bottom: 5px; }
        .final { font-size: 18px; border-top: 2px solid #333; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="titulo">LIQUIDACIÓN DE ALQUILERES</div>
        <div class="subtitulo">Periodo: {{ $liquidacion->periodo }}</div>
        <p>Propietario: {{ $propietario->nombre_completo }} (CBU: {{ $propietario->cbu }})</p>
    </div>

    <h3>Detalle de Cobros</h3>
    <table class="tabla">
        <thead>
            <tr>
                <th>Propiedad</th>
                <th>Inquilino</th>
                <th>Monto Cobrado</th>
                <th>Comisión (%)</th>
                <th>Descuento</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cuotasCobradas as $item)
            <tr>
                <td>{{ $item->contrato->propiedad->direccion }}</td>
                <td>{{ $item->contrato->inquilino->nombre_completo }}</td>
                <td>${{ number_format($item->monto_total, 2) }}</td>
                <td>{{ $item->contrato->propiedad->comision }}%</td>
                <td>
                    ${{ number_format($item->monto_total * ($item->contrato->propiedad->comision/100), 2) }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totales">
        <div class="fila-total">
            <span>Total Recaudado:</span>
            <span>$ {{ number_format($liquidacion->monto_total_cobrado, 2) }}</span>
        </div>
        <div class="fila-total" style="color: red;">
            <span>Menos Honorarios:</span>
            <span>- $ {{ number_format($liquidacion->comision_cobrada, 2) }}</span>
        </div>
        <div class="fila-total final">
            <span>A TRANSFERIR:</span>
            <span>$ {{ number_format($liquidacion->monto_entregado, 2) }}</span>
        </div>
    </div>
</body>
</html>