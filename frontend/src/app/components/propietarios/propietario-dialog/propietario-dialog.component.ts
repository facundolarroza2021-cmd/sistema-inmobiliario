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
  // Objeto temporal
  nuevoPropietario = {
    nombre_completo: '', dni: '', email: '', telefono: '', cbu: ''
  };

  constructor(
    public dialogRef: MatDialogRef<PropietarioDialogComponent>,
    @Inject(MAT_DIALOG_DATA) public data: any // Aquí recibiríamos datos si fuera para editar
  ) {}

  guardar() {
      // Validar campos básicos
      if (!this.nuevoPropietario.nombre_completo || !this.nuevoPropietario.dni) {
        this.mensaje.error('Nombre y DNI son obligatorios');
        return;
      }

      // --- CORRECCIÓN: Forzamos que el DNI sea String ---
      const datosParaEnviar = {
        ...this.nuevoPropietario,
        dni: this.nuevoPropietario.dni.toString() // <--- ¡Esto soluciona el error!
      };

      if (this.data) {
        // --- LÓGICA DE EDICIÓN ---
        // Usamos datosParaEnviar en lugar de this.nuevoPropietario
        this.api.editarPropietario(this.data.id, datosParaEnviar).subscribe({
          next: () => {
            this.mensaje.exito('Propietario actualizado');
            this.dialogRef.close(true); 
          },
          error: (err) => this.mensaje.error('Error al actualizar (¿DNI duplicado?)')
        });

      } else {
        // --- LÓGICA DE CREACIÓN ---
        // Usamos datosParaEnviar
        this.api.crearPropietario(datosParaEnviar).subscribe({
          next: () => {
            this.mensaje.exito('Propietario creado');
            this.dialogRef.close(true);
          },
          error: () => this.mensaje.error('Error al crear')
        });
      }
    }

  ngOnInit(): void {
    if (this.data) {
      this.nuevoPropietario = { ...this.data }; // Si es para editar, copiamos los datos
    }
  }
}