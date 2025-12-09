<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recibo de Pago</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #000;
            padding: 20px;
        }
        .container {
            border: 2px solid #000;
            padding: 10px;
        }
        /* Header con Logo y Datos del Recibo */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .header-table td {
            vertical-align: top;
        }
        .logo-box {
            border: 2px solid #000;
            width: 350px;
            height: 80px;
            text-align: center;
            vertical-align: middle !important;
            font-size: 24px;
            font-weight: bold;
            line-height: 80px; /* Para centrar verticalmente */
        }
        .tipo-comprobante {
            border: 2px solid #000;
            width: 60px;
            height: 60px;
            text-align: center;
            font-size: 40px;
            font-weight: bold;
            line-height: 60px;
            margin: 0 auto;
        }
        .info-fiscal {
            text-align: center;
            font-size: 9px;
        }
        .datos-recibo {
            text-align: right;
        }
        .recibo-titulo {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        /* Datos de la Empresa */
        .empresa-info {
            text-align: left;
            margin-bottom: 10px;
            font-size: 11px;
        }

        /* Sección Cliente/Propiedad */
        .section-box {
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            padding: 5px 0;
            margin-bottom: 10px;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
        }
        .details-table td {
            padding: 3px;
        }
        .label {
            font-weight: bold;
            width: 120px;
        }

        /* Conceptos y Totales */
        .conceptos-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .conceptos-table th, .conceptos-table td {
            padding: 5px;
        }
        .conceptos-table th {
            text-align: left;
            border-bottom: 1px solid #000;
        }
        .monto {
            text-align: right;
            width: 150px;
        }
        .total-row {
            font-size: 16px;
            font-weight: bold;
            border-top: 2px solid #000;
        }
    </style>
</head>
<body>
    <div class="container">
        <table class="header-table">
            <tr>
                <td style="width: 40%;">
                    <div class="logo-box">
                        Mi Inmobiliaria
                    </div>
                </td>
                <td style="width: 20%; text-align: center;">
                    <div class="tipo-comprobante">X</div>
                    <div class="info-fiscal">
                        DOCUMENTO<br>NO VÁLIDO<br>COMO FACTURA
                    </div>
                </td>
                <td style="width: 40%;" class="datos-recibo">
                    <div class="recibo-titulo">RECIBO</div>
                    <div>Nº: {{ $pago->codigo_comprobante }}</div>
                    <div>Fecha: {{ $pago->fecha_pago->format('d/m/Y') }}</div>
                </td>
            </tr>
        </table>

        <div class="empresa-info">
            Calle Falsa 123 | Tel: (011) 4444-5555<br>
            (1000) CABA | Buenos Aires<br>
            <strong>Responsable Monotributo</strong>
        </div>

        <div class="section-box">
            <div style="font-weight: bold; text-align: center; margin-bottom: 5px;">
                COBRO POR CUENTA Y ORDEN DE TERCEROS
            </div>
            <table class="details-table">
                <tr>
                    <td class="label">Cliente:</td>
                    <td>{{ $contrato->inquilino->nombre_completo }}</td>
                    <td class="label" style="text-align: right;">DNI/CUIT:</td>
                    <td style="text-align: right;">{{ $contrato->inquilino->dni }}</td>
                </tr>
                <tr>
                    <td class="label">Dirección:</td>
                    <td colspan="3">{{ $contrato->propiedad->direccion }} ({{ $contrato->propiedad->tipo }})</td>
                </tr>
            </table>
        </div>

        <div class="section-box">
            <table class="details-table">
                <tr>
                    <td class="label">Contrato:</td>
                    <td>#{{ $contrato->id }}</td>
                    <td class="label" style="text-align: right;">Inicio:</td>
                    <td style="text-align: right;">{{ \Carbon\Carbon::parse($contrato->fecha_inicio)->format('d/m/Y') }}</td>
                    <td class="label" style="text-align: right;">Fin:</td>
                    <td style="text-align: right;">{{ \Carbon\Carbon::parse($contrato->fecha_fin)->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td class="label">Propietario:</td>
                    <td colspan="5">{{ $contrato->propiedad->propietario->nombre_completo }}</td>
                </tr>
            </table>
        </div>

        <table class="conceptos-table">
            <thead>
                <tr>
                    <th>Concepto</th>
                    <th>Detalle</th>
                    <th class="monto">Importe</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cuotas as $item)
                <tr>
                    <td>ALQUILER</td>
                    <td>Correspondiente al periodo: <strong>{{ $item->periodo }}</strong></td>
                    <td class="monto">$ {{ number_format($item->monto_total, 2, ',', '.') }}</td>
                </tr>
                @endforeach
                
                </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" style="height: 20px;"></td> </tr>
                <tr class="total-row">
                    <td colspan="2" style="text-align: right; padding-top: 10px;">TOTAL PAGADO:</td>
                    <td class="monto" style="padding-top: 10px;">$ {{ number_format($pago->monto_pagado, 2, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
        
        <div style="margin-top: 30px; font-size: 11px;">
            <strong>Forma de Pago:</strong> {{ $pago->forma_pago }}
            <br><br>
            <p style="text-align: center; font-style: italic;">Gracias por su pago.</p>
        </div>

    </div>
</body>
</html>