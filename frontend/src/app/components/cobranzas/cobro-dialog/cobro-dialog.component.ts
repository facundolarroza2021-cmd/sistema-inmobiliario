// app/components/cobranzas/cobro-dialog/cobro-dialog.component.ts

import { Component, Inject, inject } from '@angular/core';
import { MAT_DIALOG_DATA, MatDialogRef, MatDialogModule } from '@angular/material/dialog';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { ApiService } from '../../../services/api.service';
import { MensajeService } from '../../../services/mensaje.service';
import { MatDividerModule} from '@angular/material/divider';
import { MatCardModule } from '@angular/material/card';
import Swal from 'sweetalert2';

@Component({
  selector: 'app-cobro-dialog',
  standalone: true,
  imports: [
    CommonModule, FormsModule, MatDialogModule, MatFormFieldModule, 
    MatInputModule, MatSelectModule, MatButtonModule, MatIconModule,
    MatProgressSpinnerModule, MatDividerModule, MatCardModule
  ],
  templateUrl: './cobro-dialog.component.html',
  styleUrls: ['./cobro-dialog.component.css'] // Asegúrate de crear este archivo
})
export class CobroDialogComponent {
  private api = inject(ApiService);
  private mensaje = inject(MensajeService);
  
  // Variables del Formulario
  montoFinal: number; // Monto editable (puede incluir redondeo)
  formaPago: string = 'Efectivo';
  observacion: string = '';
  cargando: boolean = false;
  
  // Datos inyectados desde cobranzas.component.ts
  totalCuotas: number; // El total original calculado
  cuotasSeleccionadas: any[]; // Array de cuotas seleccionadas
  
  constructor(
    public dialogRef: MatDialogRef<CobroDialogComponent>,
    @Inject(MAT_DIALOG_DATA) public data: any
  ) {
    this.totalCuotas = data.total;
    this.montoFinal = data.total; // Inicialmente, el monto a pagar es el total
    this.cuotasSeleccionadas = data.cuotas; // Se pasa el array de cuotas

    // Validación básica si el monto es 0
    if (this.totalCuotas <= 0) {
      this.mensaje.mostrarError('El monto total debe ser mayor a cero.');
      this.dialogRef.close(false);
    }
  }

  onNoClick(): void {
    this.dialogRef.close(false);
  }

  procesarCobro() {
    if (this.montoFinal <= 0 || this.montoFinal > this.totalCuotas) {
        this.mensaje.mostrarError('El monto a pagar debe ser válido y no exceder la deuda total.');
        return;
    }

    this.cargando = true;
    
    // Preparar el Payload para la API
    const payload = {
      cuotas_ids: this.cuotasSeleccionadas.map(c => c.id),
      monto_total: this.montoFinal, // Usamos el monto editable
      medio_pago: this.formaPago,
      observacion: this.observacion,
    };

    // Llamada a la API (endpoint: /pagos/multiple)
    this.api.registrarPagoMultiple(payload).subscribe({
      next: (res: any) => {
        this.cargando = false;
        
        // Alerta de éxito con opción de PDF (como se definió en cobranzas.component.ts)
        Swal.fire({
          title: '¡Cobro Exitoso! ',
          text: 'El pago múltiple se registró correctamente.',
          icon: 'success',
          showCancelButton: true,
          confirmButtonText: 'Ver Recibo PDF',
          cancelButtonText: 'Cerrar'
        }).then((result) => {
          if (result.isConfirmed && res.data && res.data.url_pdf) {
            window.open(res.data.url_pdf, '_blank');
          }
        });
        
        this.dialogRef.close({ cobroRegistrado: true });
      },
      error: (err) => {
        this.cargando = false;
        const msg = err.error?.message || 'Error al conectar con el servidor.';
        this.mensaje.mostrarError(msg);
      }
    });
  }
}