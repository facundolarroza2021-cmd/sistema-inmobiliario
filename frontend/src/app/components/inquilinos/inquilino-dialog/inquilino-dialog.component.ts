import { Component, Inject, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { MatDialogRef, MAT_DIALOG_DATA, MatDialogModule } from '@angular/material/dialog';
import { ApiService } from '../../../services/api.service';
import { MensajeService } from '../../../services/mensaje.service';
import { OnInit } from '@angular/core';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';

@Component({
  selector: 'app-inquilino-dialog',
  standalone: true,
  imports: [
    CommonModule, FormsModule, MatDialogModule, MatFormFieldModule, 
    MatInputModule, MatButtonModule, MatIconModule
  ],
  templateUrl: './inquilino-dialog.component.html'
})
export class InquilinoDialogComponent implements OnInit {
  private api = inject(ApiService);
  private mensaje = inject(MensajeService);

  nuevoInquilino = { nombre_completo: '', dni: '', email: '', telefono: '' };

  constructor(
    public dialogRef: MatDialogRef<InquilinoDialogComponent>,
    @Inject(MAT_DIALOG_DATA) public data: any
  ) {}

  ngOnInit() {
    if (this.data) {
      this.nuevoInquilino = { ...this.data }; 
    }
  }
  guardar() {
    if (!this.nuevoInquilino.nombre_completo || !this.nuevoInquilino.dni) {
      this.mensaje.error('El nombre y DNI son obligatorios');
      return;
    }

    if (this.data) {
      // --- (PUT) ---
      this.api.editarInquilino(this.data.id, this.nuevoInquilino).subscribe(() => {
        this.mensaje.exito('Inquilino actualizado');
        this.dialogRef.close(true);
      }, (err) => this.mensaje.error(err.error.message || err.message));

    } else {
      // ---(POST) ---
      this.api.crearInquilino(this.nuevoInquilino).subscribe(() => {
        this.mensaje.exito('Inquilino registrado');
        this.dialogRef.close(true);
      }, (err) => this.mensaje.error(err.error.message || err.message));
    }
  }
}