import { Component, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { MatDialogRef, MatDialogModule } from '@angular/material/dialog';
import { ApiService } from '../../../services/api.service';
import { MensajeService } from '../../../services/mensaje.service';

// Material Imports
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatButtonModule } from '@angular/material/button';
import { MatSelectModule } from '@angular/material/select';
import { MatIconModule } from '@angular/material/icon';

@Component({
  selector: 'app-liquidacion-dialog',
  standalone: true,
  imports: [
    CommonModule, FormsModule, MatDialogModule, MatFormFieldModule, 
    MatInputModule, MatButtonModule, MatSelectModule, MatIconModule
  ],
  templateUrl: './liquidacion-dialog.component.html',
  styleUrl: './liquidacion-dialog.component.css'
})
export class LiquidacionDialogComponent implements OnInit {
  private api = inject(ApiService);
  private mensaje = inject(MensajeService);

  listaPropietarios: any[] = [];
  data = { propietario_id: '', periodo: '' };

  constructor(public dialogRef: MatDialogRef<LiquidacionDialogComponent>) {}

  ngOnInit() {
    this.api.getPropietarios().subscribe(res => {
        // *** CORRECCIÓN CLAVE AQUÍ ***
        // Asumiendo que la respuesta es { data: [...] } o similar
        if (Array.isArray(res)) {
            this.listaPropietarios = res; // Si el API devuelve el array directamente
        } else if (res && res.data && Array.isArray(res.data)) {
            this.listaPropietarios = res.data; // Si el API devuelve { data: [...] }
        } else {
            // Manejo de error si el formato es inesperado
            this.mensaje.mostrarError('Error al cargar propietarios: formato de datos inesperado.');
            console.error('API Response format error:', res);
        }
    });
  }

  confirmar() {
    if (!this.data.propietario_id || !this.data.periodo) {
      this.mensaje.mostrarError('Completa los datos');
      return;
    }
    // Cerramos y devolvemos los datos para que el componente padre haga la llamada
    this.dialogRef.close(this.data);
  }
}