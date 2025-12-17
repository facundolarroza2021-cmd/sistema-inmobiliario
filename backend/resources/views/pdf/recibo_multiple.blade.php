<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recibo de Pago - {{ $codigo_comprobante }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; font-size: 12px; }
        .header { text-align: center; border-bottom: 2px solid #e91e63; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { color: #e91e63; margin: 0; text-transform: uppercase; font-size: 20px; }
        
        .info-section { width: 100%; margin-bottom: 20px; }
        .info-box { width: 48%; display: inline-block; vertical-align: top; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #f8f9fa; border-bottom: 1px solid #dee2e6; padding: 10px; text-align: left; color: #666; }
        td { padding: 10px; border-bottom: 1px solid #eee; }
        
        .total-section { margin-top: 30px; text-align: right; }
        .total-box { display: inline-block; background: #f8f9fa; padding: 15px; border-radius: 5px; border: 1px solid #eee; }
        .total-amount { font-size: 18px; color: #e91e63; font-weight: bold; }
        
        .footer { margin-top: 50px; text-align: center; font-size: 10px; color: #999; border-top: 1px solid #eee; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Recibo de Pago Inmobiliario</h1>
        <p>Nro. Comprobante: <strong>{{ $codigo_comprobante }}</strong> | Fecha: {{ date('d/m/Y H:i') }}</p>
    </div>

    <div class="info-section">
        <div class="info-box">
            <strong>Inquilino:</strong> {{ $inquilino->nombre_completo }}<br>
            <strong>DNI:</strong> {{ $inquilino->dni }}
        </div>
        <div class="info-box" style="text-align: right;">
            <strong>Propiedad:</strong> {{ $contrato->propiedad->direccion }}<br>
            <strong>Localidad:</strong> {{ $propiedad->localidad ?? 'Concordia, Entre Ríos' }}<br>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Período</th>
                <th>Concepto</th>
                <th style="text-align: right;">Monto Aplicado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pagos as $p)
            <tr>
                <td>{{ $p->cuota->periodo }}</td>
                <td>Pago de Cuota Nro. {{ $p->cuota->numero_cuota }}</td>
                <td style="text-align: right;">$ {{ number_format($p->monto_pagado, 2, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-section">
        <div class="total-box">
            <span>TOTAL RECIBIDO:</span><br>
            <span class="total-amount">$ {{ number_format($monto_total, 2, ',', '.') }}</span>
        </div>
    </div>

    <div class="footer">
        <p>Este documento es un comprobante de pago válido emitido por el Sistema de Gestión Inmobiliaria.</p>
        <p>Forma de Pago: {{ $pago_principal->forma_pago }}</p>
    </div>
</body>
</html>