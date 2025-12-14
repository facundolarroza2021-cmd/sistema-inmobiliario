import { Component, Inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MAT_DIALOG_DATA, MatDialogRef, MatDialogModule } from '@angular/material/dialog';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { ApiService } from '../../../services/api.service';
import { MensajeService } from '../../../services/mensaje.service';
import { inject } from '@angular/core';

@Component({
  selector: 'app-contrato-detalle',
  standalone: true,
  imports: [CommonModule, MatDialogModule, MatButtonModule, MatIconModule],
  templateUrl: './contrato-detalle.component.html'
})
export class ContratoDetalleComponent {
  private api = inject(ApiService);       
  private mensaje = inject(MensajeService); 
  constructor(
    public dialogRef: MatDialogRef<ContratoDetalleComponent>,
    @Inject(MAT_DIALOG_DATA) public data: any
    
  ) {console.log('Datos del contrato recibidos:', this.data);}

  finalizarContrato() {
    if(confirm('¿Seguro que deseas finalizar este contrato? Pasará a estado INACTIVO.')) {
      

      this.api.finalizarContrato(this.data.id).subscribe(() => {
        
        this.mensaje.exito('Contrato finalizado correctamente');
        this.data.activo = 0; 

      }, (error) => {
        this.mensaje.error('Error: ' + error.message);
      });
    }
  }
}