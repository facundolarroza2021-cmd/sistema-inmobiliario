<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recibo de Pago</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; color: #000; padding: 20px; }
        .container { border: 1px solid #333; padding: 15px; }
        
        /* Header */
        .header-table { width: 100%; margin-bottom: 20px; }
        .logo { font-size: 24px; font-weight: bold; text-transform: uppercase; }
        .recibo-box { text-align: right; }
        .titulo { font-size: 18px; font-weight: bold; background: #eee; padding: 5px; display: inline-block; }
        
        /* Datos */
        .info-table { width: 100%; margin-bottom: 15px; border-collapse: collapse; }
        .info-table td { padding: 4px; }
        .label { font-weight: bold; width: 100px; }

        /* TABLA PRINCIPAL DETALLADA */
        .conceptos-table { width: 100%; border-collapse: collapse; margin-top: 10px; border: 1px solid #000; }
        .conceptos-table th { background: #e0e0e0; padding: 8px; border: 1px solid #000; text-align: center; font-size: 10px; }
        .conceptos-table td { padding: 8px; border: 1px solid #000; }
        
        .col-concepto { text-align: left; }
        .col-numero { text-align: right; width: 80px; }
        
        /* Totales */
        .total-box { margin-top: 20px; text-align: right; }
        .total-label { font-size: 14px; font-weight: bold; }
        .total-valor { font-size: 18px; font-weight: bold; background: #eee; padding: 5px 10px; border: 1px solid #000; }

        .nota { margin-top: 30px; font-size: 10px; color: #555; border-top: 1px dashed #999; padding-top: 5px; }
    </style>
</head>
<body>
    <div class="container">
        
        <table class="header-table">
            <tr>
                <td>
                    <div class="logo">INMOBILIARIA</div>
                    <div>Administración de Propiedades</div>
                </td>
                <td class="recibo-box">
                    <div class="titulo">RECIBO DE COBRO</div>
                    <br>
                    <strong>Nº:</strong> {{ $pago->codigo_comprobante }}<br>
                    <strong>Fecha:</strong> {{ $pago->fecha_pago->format('d/m/Y') }}
                </td>
            </tr>
        </table>

        <div style="border-top: 2px solid #000; border-bottom: 2px solid #000; padding: 10px 0; margin-bottom: 20px;">
            <table class="info-table">
                <tr>
                    <td class="label">Inquilino:</td>
                    <td>{{ $contrato->inquilino->nombre_completo }} (DNI: {{ $contrato->inquilino->dni }})</td>
                </tr>
                <tr>
                    <td class="label">Propiedad:</td>
                    <td>{{ $contrato->propiedad->direccion }}</td>
                </tr>
            </table>
        </div>

        <h4 style="margin-bottom: 5px;">Detalle de Conceptos</h4>
        <table class="conceptos-table">
            <thead>
                <tr>
                    <th>PERIODO</th>
                    <th>VALOR CUOTA</th>
                    <th>SALDO RESTANTE</th>
                    <th>ABONADO HOY</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cuotas as $item)
                <tr>
                    <td class="col-concepto">
                        <strong>ALQUILER {{ $item->periodo }}</strong>
                        
                        <div style="font-size: 9px; color: #444; margin-top: 4px; line-height: 1.4;">
                            
                            <div style="display: flex; justify-content: space-between; width: 90%;">
                                <span>• Alquiler Base:</span>
                                <span>$ {{ number_format($item->monto_original, 2, ',', '.') }}</span>
                            </div>

                            @if($item->monto_gastos > 0)
                            <div style="display: flex; justify-content: space-between; width: 90%; color: #d32f2f;">
                                <span>• Gastos / Expensas:</span>
                                <span>$ {{ number_format($item->monto_gastos, 2, ',', '.') }}</span>
                            </div>
                            @endif

                        </div>

                        @if($item->estado == 'PAGADO')
                            <div style="margin-top: 4px;"><span style="color: green; font-weight: bold; font-size: 9px; border: 1px solid green; padding: 1px 3px; border-radius: 3px;">CANCELADO</span></div>
                        @else
                            <div style="margin-top: 4px;"><span style="color: #e65100; font-weight: bold; font-size: 9px; border: 1px solid #e65100; padding: 1px 3px; border-radius: 3px;">PAGO PARCIAL</span></div>
                        @endif
                    </td>

                    <td class="col-numero" style="color: #000; font-weight: bold;">
                        $ {{ number_format($item->monto_original + $item->monto_gastos, 2, ',', '.') }}
                    </td>

                    <td class="col-numero">
                        @if($item->detalle_saldo_restante > 0)
                            <strong style="color: #d32f2f;">$ {{ number_format($item->detalle_saldo_restante, 2, ',', '.') }}</strong>
                        @else
                            <span style="color: green;">-</span>
                        @endif
                    </td>

                    <td class="col-numero" style="font-weight: bold; background-color: #f0f4c3;">
                        $ {{ number_format($item->detalle_pago_hoy, 2, ',', '.') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="total-box">
            <span class="total-label">TOTAL PAGADO:</span>
            <span class="total-valor">$ {{ number_format($pago->monto_pagado, 2, ',', '.') }}</span>
        </div>

        <div class="nota">
            <strong>Forma de Pago:</strong> {{ $pago->forma_pago }}
            @if($pago->observacion)
                | <strong>Obs:</strong> {{ $pago->observacion }}
            @endif
            <br><br>
            <i>* Este documento es válido como comprobante de pago por los montos detallados en la columna "Abonado Hoy".</i>
        </div>

    </div>
</body>
</html>