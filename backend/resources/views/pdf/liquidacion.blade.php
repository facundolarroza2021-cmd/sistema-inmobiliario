<!DOCTYPE html>
<html>
<head>
    <title>Liquidación a Propietario</title>
    <style>
        body { font-family: sans-serif; padding: 20px; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #444; margin-bottom: 20px; padding-bottom: 10px; }
        .info-box { width: 100%; margin-bottom: 20px; }
        .info-box td { padding: 5px; }
        .table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 12px; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .table th { background-color: #f2f2f2; }
        .totales { margin-top: 20px; text-align: right; width: 100%; }
        .totales td { padding: 5px; font-size: 14px; }
        .neto { font-size: 18px; font-weight: bold; color: #2c3e50; border-top: 2px solid #333; }
    </style>
</head>
<body>
    <div class="header">
        <h2>LIQUIDACIÓN DE ALQUILERES</h2>
        <p>Comprobante #{{ $liquidacion->id }} - Fecha: {{ $fecha->format('d/m/Y') }}</p>
    </div>

    <table class="info-box">
        <tr>
            <td><strong>Propietario:</strong> {{ $propietario->nombre }} {{ $propietario->apellido }}</td>
            <td><strong>Periodo:</strong> {{ $liquidacion->periodo }}</td>
        </tr>
        <tr>
            <td><strong>DNI/CUIT:</strong> {{ $propietario->dni ?? '-' }}</td>
            <td><strong>Email:</strong> {{ $propietario->email }}</td>
        </tr>
    </table>

    <h3>Detalle de Conceptos Cobrados</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Propiedad</th>
                <th>Inquilino</th>
                <th>Concepto</th>
                <th>Importe</th>
            </tr>
        </thead>
        <tbody>
            @foreach($detalles as $item)
            <tr>
                <td>{{ $item->contrato->propiedad->direccion ?? 'Propiedad' }}</td>
                <td>{{ $item->contrato->inquilino->nombre_completo ?? 'Inquilino' }}</td>
                <td>Cuota #{{ $item->numero_cuota }}</td>
                <td>${{ number_format($item->importe, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totales" align="right">
        <tr>
            <td>Total Recaudado:</td>
            <td><strong>${{ number_format($liquidacion->total_ingresos, 2) }}</strong></td>
        </tr>
        <tr>
            <td>(-) Comisión Inmobiliaria:</td>
            <td style="color: #c0392b;">- ${{ number_format($liquidacion->comision_inmobiliaria, 2) }}</td>
        </tr>
        @if($liquidacion->total_gastos > 0)
        <tr>
            <td>(-) Gastos / Reparaciones:</td>
            <td style="color: #c0392b;">- ${{ number_format($liquidacion->total_gastos, 2) }}</td>
        </tr>
        @endif
        <tr>
            <td class="neto">TOTAL A PAGAR:</td>
            <td class="neto">${{ number_format($liquidacion->monto_neto, 2) }}</td>
        </tr>
    </table>

    <div style="margin-top: 60px; text-align: center; font-size: 10px; color: #777;">
        <p>Documento generado electrónicamente por Sistema de Gestión Inmobiliaria</p>
    </div>
</body>
</html>