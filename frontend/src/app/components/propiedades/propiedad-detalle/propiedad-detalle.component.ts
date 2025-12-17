import { Component, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ActivatedRoute } from '@angular/router';
import { ApiService } from '../../../services/api.service';
import { MensajeService } from '../../../services/mensaje.service';

import { FormsModule } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatCardModule } from '@angular/material/card';
import { MatTabsModule } from '@angular/material/tabs';
import { EventosService } from '../../../services/eventos.service';

@Component({
  selector: 'app-propiedad-detalle',
  standalone: true,
  imports: [CommonModule, MatButtonModule, MatIconModule, MatCardModule, MatTabsModule, FormsModule],
  templateUrl: './propiedad-detalle.component.html',
  styleUrl: './propiedad-detalle.component.css' // Asegurate de tener el CSS vinculado
})
export class PropiedadDetalleComponent implements OnInit {
  private route = inject(ActivatedRoute);
  private api = inject(ApiService);
  private mensaje = inject(MensajeService);

  propiedad: any = null;

  private eventosService: EventosService = inject(EventosService);
  ngOnInit() {
    const id = this.route.snapshot.paramMap.get('id');
    if(id) this.cargarDatos(+id);
  }
  cargarDatos(id: number) {
    this.api.getPropiedadDetalle(id).subscribe(res => {
        this.propiedad = res;
        this.cargarGastos(); 
    });
  }
  subirFoto(event: any) {
    const archivo = event.target.files[0];
    if (archivo) {
      this.api.subirFotoPropiedad(this.propiedad.id, archivo).subscribe(res => {
        this.mensaje.mostrarExito('Foto subida correctamente');
        // Recargar datos para ver la foto nueva
        this.cargarDatos(this.propiedad.id);
      }, err => this.mensaje.mostrarError('Error al subir imagen'));
    }
  }
  // Variables
  listaGastos: any[] = [];
  nuevoGasto = { concepto: '', monto: null, fecha: new Date().toISOString().split('T')[0], responsable: 'PROPIETARIO' };

  // En ngOnInit o cargarDatos:
  cargarGastos() {
    this.api.getGastosPropiedad(this.propiedad.id).subscribe(res => this.listaGastos = res);
  }

  guardarGasto() {
    // 1. Depuración: Ver en consola si entra a la función
    console.log('Botón presionado. Datos actuales:', this.nuevoGasto);

    // 2. Validación con Mensaje
    if (!this.nuevoGasto.concepto || !this.nuevoGasto.monto) {
      this.mensaje.mostrarError('Por favor completa el Concepto y el Monto');
      return;
    }
    
    // 3. Verificar que tengamos el ID de la propiedad
    if (!this.propiedad || !this.propiedad.id) {
      this.mensaje.mostrarError('Error: No se identificó la propiedad');
      return;
    }

    const data = { 
        ...this.nuevoGasto, 
        propiedad_id: this.propiedad.id 
    };

    console.log('Enviando al backend:', data);

    this.api.crearGasto(data).subscribe({
      next: () => {
        this.mensaje.mostrarExito('Gasto registrado correctamente');
        // Resetear formulario
        this.nuevoGasto = { concepto: '', monto: null, fecha: new Date().toISOString().split('T')[0], responsable: 'PROPIETARIO' }; 
        // Recargar la lista
        this.cargarGastos();

        this.eventosService.emitirCuotasActualizadas();
      },
      error: (err) => {
        console.error('Error API:', err);
        this.mensaje.mostrarError('Error al guardar el gasto');
      }
    });
  }

  borrarGasto(id: number) {
      if(confirm('¿Borrar gasto?')) {
          this.api.eliminarGasto(id).subscribe(() => {
              this.mensaje.mostrarExito('Eliminado');
              this.cargarGastos();
          });
      }
  }
}