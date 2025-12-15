// src/app/pages/indexacion/indexacion.component.ts

import { Component, inject, OnInit } from '@angular/core';
import { CommonModule, DatePipe } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { ApiService } from '../../services/api.service';
import { MensajeService } from '../../services/mensaje.service';

// Material Imports
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatAutocompleteModule } from '@angular/material/autocomplete';
import { MatDatepickerModule } from '@angular/material/datepicker';
import { MatNativeDateModule } from '@angular/material/core';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatDividerModule } from '@angular/material/divider';


@Component({
  selector: 'app-indexacion',
  standalone: true,
  imports: [
    CommonModule, FormsModule, MatCardModule, MatButtonModule, MatIconModule,
    MatFormFieldModule, MatInputModule, MatAutocompleteModule, MatDatepickerModule,
    MatNativeDateModule, MatProgressSpinnerModule, MatDividerModule, DatePipe
  ],
  templateUrl: './indexacion.component.html',
  styleUrls: ['./indexacion.component.css']
})
export class IndexacionComponent implements OnInit {
  private api = inject(ApiService);
  private mensaje = inject(MensajeService);

  // --- Data
  contratos: any[] = [];
  contratosFiltrados: any[] = [];
  
  contratoSeleccionado: any = null; // Objeto completo del contrato seleccionado
  cargando: boolean = false;

  // --- Formulario
  ajusteData = {
    porcentaje: null,
    fechaAplicacion: new Date().toISOString().split('T')[0], // Formato YYYY-MM-DD
    motivo: '',
    tipo: 'FIXED' as string, // Estado inicial por defecto (FIXED, INDEX, USD)
    indice: '',
    periodo: ''
  };

  ngOnInit() {
    this.cargarContratosAptos();
  }

  /**
   * Función utilizada por mat-autocomplete para mostrar el Contrato
   * como una cadena de texto legible en el campo de input.
   */
  displayContrato(contrato: any): string {
    if (contrato) {
      // Formato legible: Dirección (Inquilino) - Monto
      return `${contrato.propiedad?.direccion || 'N/A'} (${contrato.inquilino?.nombre_completo || 'N/A'})`;
    }
    return '';
  }
  cargarContratosAptos() {
    this.api.getContratosParaIndexar().subscribe({
      next: (res) => {
        this.contratos = res;
        this.contratosFiltrados = res;
      },
      error: (err) => {
        this.mensaje.error('No se pudieron cargar contratos activos.');
        console.error(err);
      }
    });
  }

  // --- Lógica de Búsqueda y Autocompletado ---
  filtrarContratos(event: any) {
    const valor = typeof event === 'string' ? event : (event?.target as HTMLInputElement)?.value;
    
    if (!valor) {
        this.contratosFiltrados = this.contratos;
        this.contratoSeleccionado = null;
        return;
    }
    const filtro = valor.toLowerCase();
    
    this.contratosFiltrados = this.contratos.filter(c => 
        c.inquilino.nombre_completo.toLowerCase().includes(filtro) ||
        c.propiedad.direccion.toLowerCase().includes(filtro)
    );
  }

  seleccionarContrato(contrato: any) {
    this.contratoSeleccionado = contrato;
    this.ajusteData.porcentaje = null;
    this.ajusteData.motivo = '';
  }

  // --- Lógica de Cálculo y Vista Previa ---

  /**
   * Calcula el nuevo monto de alquiler para la vista previa.
   */
  calcularNuevoMonto(): number {
    if (!this.contratoSeleccionado || !this.ajusteData.porcentaje) {
      return this.contratoSeleccionado?.monto_alquiler || 0;
    }
    const montoBase = this.contratoSeleccionado.monto_alquiler;
    const factor = 1 + (this.ajusteData.porcentaje / 100);
    return montoBase * factor;
  }
  
  /**
   * Calcula (de forma burda) cuántas cuotas futuras serán afectadas.
   */
  get cuotasAfectadas(): number {
    if (!this.contratoSeleccionado || !this.ajusteData.fechaAplicacion) return 0;

    const fechaFin = new Date(this.contratoSeleccionado.fecha_fin);
    const fechaAplicacion = new Date(this.ajusteData.fechaAplicacion);

    // Calcular la diferencia en meses
    let meses = (fechaFin.getFullYear() - fechaAplicacion.getFullYear()) * 12;
    meses -= fechaAplicacion.getMonth();
    meses += fechaFin.getMonth();

    // Sumamos 1 porque la cuota del mes de aplicación también cuenta
    return Math.max(0, meses + 1); 
  }

  // --- Lógica de Envío al Backend ---

  aplicarAjuste() {
    if (!this.contratoSeleccionado || !this.ajusteData.porcentaje) {
      this.mensaje.error('Debe seleccionar un contrato y definir el porcentaje.');
      return;
    }

    this.cargando = true;
    
    const payload = {
      contrato_id: this.contratoSeleccionado.id,
      porcentaje: this.ajusteData.porcentaje,
      fecha_aplicacion: this.ajusteData.fechaAplicacion,
      motivo: this.ajusteData.motivo
    };

    this.api.aplicarIndexacion(payload).subscribe({
      next: (res) => {
        this.mensaje.exito('Indexación aplicada. El nuevo monto rige en las cuotas futuras.');
        // Limpiar y refrescar
        this.contratoSeleccionado = res.contrato; // Mostrar el contrato actualizado
        this.ajusteData.porcentaje = null;
        this.cargando = false;
        this.cargarContratosAptos(); // Recargar la lista de contratos
      },
      error: (err) => {
        this.mensaje.error(err.error?.message || 'Error desconocido al aplicar ajuste.');
        this.cargando = false;
      }
    });
  }
}