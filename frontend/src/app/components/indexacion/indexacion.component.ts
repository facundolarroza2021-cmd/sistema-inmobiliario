// frontend/src/app/components/indexacion/indexacion.component.ts

import { Component, OnInit, ViewChild, OnDestroy } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { MatTableDataSource } from '@angular/material/table';
import { MatPaginator } from '@angular/material/paginator';
import { MatSort } from '@angular/material/sort';
import { SelectionModel } from '@angular/cdk/collections';
import { MatStepper } from '@angular/material/stepper'; 
import { MatCardModule } from '@angular/material/card';
import { ApiService } from '../../services/api.service';
import { MensajeService } from '../../services/mensaje.service';
import { Subscription } from 'rxjs';
import { MatStepperModule } from '@angular/material/stepper'; // Resuelve 'mat-stepper'
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { MatDatepickerModule } from '@angular/material/datepicker'; 
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatTableModule } from '@angular/material/table';
import { MatPaginatorModule } from '@angular/material/paginator';
import { MatSortModule } from '@angular/material/sort';
import { MatCheckboxModule } from '@angular/material/checkbox';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner'; // Para mat-spinner
import { MatChipsModule } from '@angular/material/chips'; // Para mat-chip
import { CommonModule } from '@angular/common'; // <-- Necesario para *ngIf, *ngFor, etc.
import { ReactiveFormsModule } from '@angular/forms';
import { MatNativeDateModule } from '@angular/material/core';
import { Title } from '@angular/platform-browser';
import { inject } from '@angular/core';


// ** Interfaz/Modelo de Contrato para la previsualización **
// (Esto debería ir en un archivo de modelos compartido, pero lo incluimos aquí para la completitud)
interface ContratoIndexable {
  id: number;
  inquilino: { nombre_completo: string };
  monto_alquiler: number;
  cuotas_afectadas: number; // Número de cuotas futuras que se ajustarán
  // Puedes añadir más campos relevantes como dirección de la propiedad, etc.
}

@Component({
  selector: 'app-indexacion',
  templateUrl: './indexacion.component.html',
  styleUrls: ['./indexacion.component.css'],
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    MatCardModule,
    MatStepperModule,
    MatInputModule,
    MatSelectModule,
    MatDatepickerModule,
    MatNativeDateModule,
    MatButtonModule,
    MatIconModule,
    MatTableModule,
    MatPaginatorModule,
    MatSortModule,
    MatCheckboxModule,
    MatProgressSpinnerModule,
    MatChipsModule
  ]
})
export class IndexacionComponent implements OnInit, OnDestroy {
  
  // Referencias a elementos de Material
  @ViewChild(MatPaginator) paginator!: MatPaginator;
  @ViewChild(MatSort) sort!: MatSort;
  @ViewChild(MatStepper) stepper!: MatStepper;

  // Propiedades de estado y datos
  ajusteForm: FormGroup;
  isApplying = false;
  private subscriptions: Subscription = new Subscription();
  private title = inject(Title);
  
  // Datos de la tabla y selección
  contratosAjustados: ContratoIndexable[] = []; // Datos crudos recibidos
  dataSource!: MatTableDataSource<ContratoIndexable>;
  
  // Columnas a mostrar en la tabla (deben coincidir con matColumnDef en el HTML)
  displayedColumns: string[] = ['select', 'contrato', 'montoActual', 'montoNuevo', 'cuotasAfectadas'];
  
  // Objeto para manejar la selección de filas
  selection = new SelectionModel<ContratoIndexable>(true, []);

  constructor(
    private fb: FormBuilder,
    private apiService: ApiService,
    private mensajeService: MensajeService,
    

  ) {
    this.title.setTitle('Gestion | Indexación');
    // Inicialización del formulario reactivo
    this.ajusteForm = this.fb.group({
      tipoAjuste: ['porcentaje', Validators.required],
      valorAjuste: [0, [Validators.required, Validators.min(0)]],
      // Se recomienda usar un selector de mes y año para 'fechaAplicacion', 
      // pero usamos Date por simplicidad de Material
      fechaAplicacion: [new Date(), Validators.required], 
    });
  }

  ngOnInit(): void {}
  
  ngOnDestroy(): void {
    this.subscriptions.unsubscribe();
  }

  /**
   * Pasa al siguiente paso y llama al backend para previsualizar los contratos afectados.
   */
  previsualizarAjuste(): void {
    if (this.ajusteForm.invalid) {
      this.ajusteForm.markAllAsTouched();
      this.mensajeService.mostrarError('Complete todos los campos del formulario de ajuste.');
      return;
    }
    this.isApplying = true;
    
    // El payload debe formatear la fecha a un formato YYYY-MM-DD que el backend (Laravel) entienda.
    const fecha = this.ajusteForm.get('fechaAplicacion')?.value;
    const payload = {
        ...this.ajusteForm.value,
        fechaAplicacion: fecha ? this.formatDate(fecha) : null
    };

    // Llama al endpoint de previsualización
    const sub = this.apiService.previsualizarAjuste(payload).subscribe({
      next: (data) => {
        this.contratosAjustados = data.contratos || [];
        this.dataSource = new MatTableDataSource(this.contratosAjustados);
        this.dataSource.paginator = this.paginator;
        this.dataSource.sort = this.sort;
        this.selection.clear(); // Limpiar selección anterior
        this.isApplying = false;
        
        // Mover al siguiente paso: Previsualización
        this.stepper.next();
      },
      error: (err) => {
        this.isApplying = false;
        this.mensajeService.mostrarError(err.error?.message || 'Error al previsualizar la indexación.');
      }
    });
    this.subscriptions.add(sub);
  }

  /**
   * Aplica el ajuste a los contratos seleccionados.
   */
  aplicarAjuste(): void {
    if (this.selection.selected.length === 0) {
      this.mensajeService.mostrarAdvertencia('Debe seleccionar al menos un contrato para aplicar el ajuste.');
      return;
    }

    this.isApplying = true;
    const contratosIds = this.selection.selected.map(c => c.id);
    const fecha = this.ajusteForm.get('fechaAplicacion')?.value;

    const payload = {
      ...this.ajusteForm.value,
      fechaAplicacion: fecha ? this.formatDate(fecha) : null,
      contratos_ids: contratosIds,
    };

    // Llama al endpoint final para aplicar el ajuste
    const sub = this.apiService.aplicarAjusteMasivo(payload).subscribe({
      next: (data) => {
        this.isApplying = false;
        this.mensajeService.mostrarExito(data.message || 'Ajuste aplicado con éxito.');
        this.resetWorkflow();
      },
      error: (err) => {
        this.isApplying = false;
        this.mensajeService.mostrarError(err.error?.message || 'Error al aplicar el ajuste.');
      }
    });
    this.subscriptions.add(sub);
  }

  /**
   * Resetea el formulario y el flujo del Stepper.
   */
  resetWorkflow(): void {
    this.ajusteForm.reset({ 
      tipoAjuste: 'porcentaje', 
      valorAjuste: 0, 
      fechaAplicacion: new Date() 
    });
    this.contratosAjustados = [];
    this.selection.clear();
    this.dataSource = new MatTableDataSource<ContratoIndexable>([]);
    this.stepper.reset();
  }

  /**
   * Calcula el nuevo monto de alquiler basado en la configuración del formulario.
   */
  calcularNuevoMonto(montoActual: number): number {
    const valorAjuste = this.ajusteForm.get('valorAjuste')?.value || 0;
    const tipoAjuste = this.ajusteForm.get('tipoAjuste')?.value;

    if (tipoAjuste === 'porcentaje') {
      return montoActual * (1 + valorAjuste / 100);
    } else if (tipoAjuste === 'monto_fijo') {
      return montoActual + valorAjuste;
    }
    return montoActual;
  }

  // ** Lógica de Selección de Filas (Mat-Table) **

  isAllSelected() {
    const numSelected = this.selection.selected.length;
    const numRows = this.dataSource.data.length;
    return numSelected === numRows;
  }

  masterToggle() {
    this.isAllSelected() ?
        this.selection.clear() :
        this.dataSource.data.forEach(row => this.selection.select(row));
  }

  checkboxLabel(row?: ContratoIndexable): string {
    if (!row) {
      return `${this.isAllSelected() ? 'select' : 'deselect'} all`;
    }
    return `${this.selection.isSelected(row) ? 'deselect' : 'select'} row ${row.id}`;
  }

  // ** Utilidades **

  /**
   * Formatea un objeto Date a string YYYY-MM-DD para el backend.
   */
  private formatDate(date: Date): string {
    const d = new Date(date);
    let month = '' + (d.getMonth() + 1);
    let day = '' + d.getDate();
    const year = d.getFullYear();

    if (month.length < 2) 
        month = '0' + month;
    if (day.length < 2) 
        day = '0' + day;

    return [year, month, day].join('-');
  }
}