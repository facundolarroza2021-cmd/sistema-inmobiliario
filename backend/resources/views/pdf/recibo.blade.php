<!DOCTYPE html>
<html>
<head>
    <title>Recibo de Pago</title>
    <style>
        body { font-family: sans-serif; padding: 30px; }
        .header { text-align: center; border-bottom: 2px solid #333; margin-bottom: 20px; padding-bottom: 10px; }
        .info { margin-bottom: 10px; font-size: 14px; }
        .label { font-weight: bold; width: 120px; display: inline-block; }
        .total { 
            font-size: 1.5em; 
            font-weight: bold; 
            margin-top: 30px; 
            padding: 10px; 
            border: 1px solid #333; 
            text-align: center; 
            background-color: #f0f0f0; 
        }
        .footer { margin-top: 50px; font-size: 10px; color: #777; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h2>RECIBO DE PAGO</h2>
        <p>Inmobiliaria</p>
        <p>Fecha: {{ $pago->fecha_pago->format('d/m/Y H:i') }}</p>
    </div>

    <div class="content">
        <div class="info">
            <span class="label">Recibí de:</span> {{ $inquilino }}
        </div>
        
        <div class="info">
            <span class="label">Propiedad:</span> {{ $direccion }}
        </div>

        <div class="info">
            <span class="label">Concepto:</span> 
            Alquiler Cuota #{{ $cuota->numero_cuota }} 
            (Vencimiento: {{ \Carbon\Carbon::parse($cuota->fecha_vencimiento)->format('d/m/Y') }})
        </div>

        <div class="info">
            <span class="label">Forma de Pago:</span> {{ $pago->forma_pago }}
        </div>

        @if(isset($contrato))
        <div class="info">
            <span class="label">Contrato N°:</span> {{ $contrato->id }}
        </div>
        @endif

        <div class="total">
            TOTAL: ${{ number_format($pago->monto_pagado, 2) }}
        </div>
    </div>

    <div class="footer">
        <p>Comprobante generado automáticamente por el sistema - ID Pago: {{ $pago->id }}</p>
    </div>
</body>
</html>