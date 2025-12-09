import { Component, inject, OnInit, Inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { MatDialogRef, MAT_DIALOG_DATA, MatDialogModule } from '@angular/material/dialog';
import { ApiService } from '../../../services/api.service';
import { MensajeService } from '../../../services/mensaje.service';

// Material Imports
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatButtonModule } from '@angular/material/button';
import { MatSelectModule } from '@angular/material/select';
import { MatIconModule } from '@angular/material/icon';


@Component({
  selector: 'app-propiedad-dialog',
  standalone: true,
  imports: [
    CommonModule, FormsModule, MatDialogModule, MatFormFieldModule, 
    MatInputModule, MatButtonModule, MatSelectModule, MatIconModule
  ],
  templateUrl: './propiedad-dialog.component.html'
})
export class PropiedadDialogComponent implements OnInit {
  private api = inject(ApiService);
  private mensaje = inject(MensajeService);

  // Variables del formulario
  nuevaPropiedad = { direccion: '', tipo: 'Casa', propietario_id: null, comision: 10 };
  listaPropietarios: any[] = [];

  constructor(
    public dialogRef: MatDialogRef<PropiedadDialogComponent>,
    @Inject(MAT_DIALOG_DATA) public data: any // <--- Recibimos datos para editar
  ) {}

  ngOnInit() {
    this.api.getPropietarios().subscribe(res => this.listaPropietarios = res);

    if (this.data) {
      // MODO EDICIÓN
      this.nuevaPropiedad = { 
        direccion: this.data.direccion,
        tipo: this.data.tipo,
        comision: this.data.comision,
        // TRUCO: Si viene el objeto propietario, sacamos su ID. Si no, usamos el ID directo.
        propietario_id: this.data.propietario ? this.data.propietario.id : this.data.propietario_id
      };
    }
  }

  guardar() {
    if (!this.nuevaPropiedad.direccion || !this.nuevaPropiedad.propietario_id) {
      this.mensaje.error('Dirección y Propietario son obligatorios');
      return;
    }

    if (this.data) {
      // --- MODO EDICIÓN ---
      this.api.editarPropiedad(this.data.id, this.nuevaPropiedad).subscribe(() => {
        this.mensaje.exito('Propiedad actualizada');
        this.dialogRef.close(true);
      }, err => this.mensaje.error('Error al actualizar'));
    } else {
      // --- MODO CREACIÓN ---
      this.api.crearPropiedad(this.nuevaPropiedad).subscribe(() => {
        this.mensaje.exito('Propiedad creada');
        this.dialogRef.close(true);
      }, err => this.mensaje.error('Error al crear'));
    }
  }
}