import { Component, Inject, OnInit, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { MatDialogRef, MAT_DIALOG_DATA, MatDialogModule } from '@angular/material/dialog';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatChipsModule } from '@angular/material/chips';
import { MensajeService } from '../../../services/mensaje.service';
import { ApiService } from '../../../services/api.service';

@Component({
  selector: 'app-propietario-dialog',
  standalone: true,
  imports: [CommonModule, FormsModule, MatDialogModule, MatFormFieldModule, MatInputModule, MatButtonModule, MatIconModule, MatChipsModule],
  templateUrl: './propietario-dialog.component.html'
})
export class PropietarioDialogComponent implements OnInit {
  private mensaje = inject(MensajeService);
  private api = inject(ApiService);   
  
  nuevoPropietario = { 
    nombre_completo: '', 
    dni: '', 
    email: '', 
    telefono: '', 
    direccion: '',
    cbu: null
  };

  constructor(
    public dialogRef: MatDialogRef<PropietarioDialogComponent>,
    @Inject(MAT_DIALOG_DATA) public data: any // Aquí recibiríamos datos si fuera para editar
  ) {}

  ngOnInit() {
    if (this.data) {
      // MAPEAMOS los datos de la tabla a lo que el Backend requiere para validar
      this.nuevoPropietario = { 
        ...this.data,
        nombre_completo: this.data.nombre,        // 'nombre' de la tabla -> 'nombre_completo' del backend
        dni: this.data.identificacion,           // 'identificacion' de la tabla -> 'dni' del backend
        email: this.data.contacto?.email || ''   // Extraemos el email si viene anidado
      }; 
    }
  }

  guardar() {
    if (this.data && this.data.id) {
      // IMPORTANTE: Laravel a veces requiere _method: 'PUT' si envías como POST
      // o simplemente usa el método editarPropietario que definimos en api.php
      this.api.editarPropietario(this.data.id, this.nuevoPropietario).subscribe({
        next: () => {
          this.mensaje.mostrarExito('Propietario actualizado correctamente');
          this.dialogRef.close(true);
        },
        error: (err) => this.mensaje.mostrarError('Error al actualizar')
      });
    } else {
      this.api.crearPropietario(this.nuevoPropietario).subscribe({
        next: () => {
          this.mensaje.mostrarExito('Propietario registrado');
          this.dialogRef.close(true);
        }
      });
    }
  }
}