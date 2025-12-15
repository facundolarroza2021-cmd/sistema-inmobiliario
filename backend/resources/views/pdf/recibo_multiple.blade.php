<!DOCTYPE html>
<html>
<head>
    <title>Recibo de Pago</title>
    <style>
        body { font-family: sans-serif; padding: 20px; border: 1px solid #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 1px solid #ccc; padding-bottom: 10px; }
        .info { margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .total { font-size: 20px; font-weight: bold; text-align: right; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>RECIBO DE PAGO OFICIAL</h2>
        <p>Comprobante: {{ $pago_principal->codigo_comprobante }}</p>
        <p>Fecha: {{ $pago_principal->fecha_pago->format('d/m/Y') }}</p>
    </div>

    <div class="info">
        <strong>Inquilino:</strong> {{ $contrato->inquilino->nombre_completo }} <br>
        <strong>Propiedad:</strong> {{ $contrato->propiedad->direccion }} <br>
        <strong>Forma de Pago:</strong> {{ $pago_principal->forma_pago }}
    </div>

    <h3>Conceptos Abonados:</h3>
    <table>
        <thead>
            <tr>
                <th>Concepto / Periodo</th>
                <th>Monto</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($cuotas as $cuota)
                <tr>
                    <td>{{ $cuota->periodo }}</td> <td>{{ $cuota->importe }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total">
        TOTAL PAGADO: $ {{ number_format($pago_principal->monto_pagado, 2) }}
    </div>
</body>
</html>