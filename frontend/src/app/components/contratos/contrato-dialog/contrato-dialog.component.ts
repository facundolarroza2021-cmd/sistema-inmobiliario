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
import { MatDatepickerModule } from '@angular/material/datepicker'; 
import { MatNativeDateModule } from '@angular/material/core';

@Component({
  selector: 'app-contrato-dialog',
  standalone: true,
  imports: [
    CommonModule, FormsModule, MatDialogModule, MatFormFieldModule, 
    MatInputModule, MatButtonModule, MatSelectModule, MatIconModule,
    MatDatepickerModule, MatNativeDateModule
  ],
  templateUrl: './contrato-dialog.component.html'
})
export class ContratoDialogComponent implements OnInit {
  
  // Inyecciones
  private api = inject(ApiService);
  private mensaje = inject(MensajeService);
  
  // Variables
  archivoSeleccionado: File | null = null;
  listaInquilinos: any[] = [];
  listaPropiedades: any[] = [];

  // Datos del Contrato
  nuevoContrato = {
    inquilino_id: '', 
    propiedad_id: '', 
    monto_actual: '',
    fecha_inicio: '', 
    duracion_meses: 24, // Valor por defecto
    dia_vencimiento: 10
  };

  // Variables para Garantes
  listaGarantes: any[] = [];
  nuevoGarante = { 
    nombre_completo: '', 
    dni: '', 
    telefono: '', 
    tipo: 'RECIBO', 
    detalle: '' 
  };
  
  constructor(public dialogRef: MatDialogRef<ContratoDialogComponent>) {}

  ngOnInit() {
    // CARGAR INQUILINOS
    this.api.getInquilinos().subscribe((d: any) => {
        // Truco: Si viene 'data' (paginado antiguo) o directo (get nuevo), funciona igual.
        this.listaInquilinos = d.data || d; 
        console.log('Inquilinos:', this.listaInquilinos); // Mira la consola del navegador (F12)
    });

    // CARGAR PROPIEDADES
    this.api.getPropiedades().subscribe((d: any) => {
        this.listaPropiedades = d.data || d;
    });
  }

  // ESTA ES LA FUNCIÓN QUE RELLENA EL PRECIO
  alSeleccionarPropiedad() {
    const id = this.nuevoContrato.propiedad_id;
    // Buscamos la propiedad seleccionada en la memoria
    const prop = this.listaPropiedades.find(p => p.id == id);

    if (prop) {
        // Asignamos el precio al contrato
        this.nuevoContrato.monto_actual = prop.precio_alquiler;
    }
  }

  // --- MANEJO DE ARCHIVO ---
  onArchivoSeleccionado(event: any) {
    const file = event.target.files[0];
    if (file) {
        this.archivoSeleccionado = file;
    }
  }

  // --- GARANTES ---
  agregarGarante() {
    // Validar datos mínimos
    if (!this.nuevoGarante.nombre_completo || !this.nuevoGarante.dni) {
      this.mensaje.mostrarError('El Nombre y DNI del garante son obligatorios');
      return;
    }

    // Agregamos una COPIA del objeto a la lista
    this.listaGarantes.push({ ...this.nuevoGarante });

    // Limpiamos los campos para cargar otro
    this.nuevoGarante = { 
        nombre_completo: '', 
        dni: '', 
        telefono: '', 
        tipo: 'RECIBO', 
        detalle: '' 
    };
  }

  eliminarGarante(index: number) {
    this.listaGarantes.splice(index, 1);
  }

  // --- GUARDAR CONTRATO ---
  guardar() {
    // 1. Validaciones básicas
    if (!this.nuevoContrato.inquilino_id || !this.nuevoContrato.propiedad_id || !this.nuevoContrato.monto_actual) {
        this.mensaje.mostrarError('Faltan datos obligatorios (Inquilino, Propiedad o Monto)');
        return;
    }

    // 2. CREAMOS EL FORM DATA
    const formData = new FormData();

    // Agregamos campos simples (Convertidos a String explícitamente)
    formData.append('inquilino_id', this.nuevoContrato.inquilino_id.toString());
    formData.append('propiedad_id', this.nuevoContrato.propiedad_id.toString());
    formData.append('monto_actual', this.nuevoContrato.monto_actual.toString());
    formData.append('dia_vencimiento', this.nuevoContrato.dia_vencimiento.toString());
    
    // IMPORTANTE: Enviamos 'meses' para que Laravel calcule la fecha fin
    formData.append('meses', (this.nuevoContrato.duracion_meses || 24).toString());

    // 3. TRATAMIENTO DE FECHA (Manual YYYY-MM-DD para evitar errores)
    let fechaLimpia = '';
    const fechaRaw = this.nuevoContrato.fecha_inicio;

    if (fechaRaw) {
        const d = new Date(fechaRaw);
        if (!isNaN(d.getTime())) {
            const year = d.getFullYear();
            const month = ('0' + (d.getMonth() + 1)).slice(-2);
            const day = ('0' + d.getDate()).slice(-2);
            fechaLimpia = `${year}-${month}-${day}`;
        }
    }
    // Si la fecha es inválida o vacía, enviamos string vacío (el backend fallará si es required)
    formData.append('fecha_inicio', fechaLimpia);

    // 4. ARCHIVO
    if (this.archivoSeleccionado) {
        formData.append('archivo', this.archivoSeleccionado);
    }

    // 5. GARANTES (Como JSON String)
    formData.append('garantes', JSON.stringify(this.listaGarantes));

    // --- ENVIAMOS ---
    this.api.crearContrato(formData).subscribe({
        next: () => {
            this.mensaje.mostrarExito('Contrato guardado con éxito');
            this.dialogRef.close(true);
        },
        error: (err) => {
            console.error('Error del servidor:', err);
            // Mostramos el mensaje exacto que devuelve Laravel si existe
            const errorMsg = err.error?.message || 'Error al guardar contrato';
            this.mensaje.mostrarError(errorMsg);
        }
    });
  }
}