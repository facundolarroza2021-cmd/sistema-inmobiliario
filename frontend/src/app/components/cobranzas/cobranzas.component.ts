import { Component, inject, OnInit, ViewChild } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ApiService } from '../../services/api.service';
import { MensajeService } from '../../services/mensaje.service';
import { SelectionModel } from '@angular/cdk/collections'; 
import { FormsModule } from '@angular/forms'; // Añadido: Necesario para el input de filtro
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatTableDataSource, MatTableModule } from '@angular/material/table';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatPaginator, MatPaginatorModule } from '@angular/material/paginator';
import { MatSort, MatSortModule } from '@angular/material/sort';
import { MatChipsModule } from '@angular/material/chips';
import { MatCheckboxModule } from '@angular/material/checkbox'; 
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatTooltipModule } from '@angular/material/tooltip'; // Añadido
import { MatDialog } from '@angular/material/dialog';
import { CobroDialogComponent } from './cobro-dialog/cobro-dialog.component';
import Swal from 'sweetalert2';
import { EventosService } from '../../services/eventos.service';

@Component({
  selector: 'app-cobranzas',
  standalone: true,
  imports: [
    CommonModule, FormsModule, MatCardModule, MatButtonModule, MatTableModule, 
    MatIconModule, MatInputModule, MatFormFieldModule, MatPaginatorModule, 
    MatSortModule, MatChipsModule, MatProgressSpinnerModule, MatCheckboxModule,
    MatTooltipModule // Agregado para usar matTooltip
  ],
  templateUrl: './cobranzas.component.html',
  styleUrls: ['./cobranzas.component.css']
})
export class CobranzasComponent implements OnInit {
  private api = inject(ApiService);
  private mensaje = inject(MensajeService);
  private dialog = inject(MatDialog); // Inyección del Dialog
  

  private eventosService = inject(EventosService);

  @ViewChild(MatPaginator) paginator!: MatPaginator;
  @ViewChild(MatSort) sort!: MatSort;

  // Variables
  dataSource = new MatTableDataSource<any>([]);
  columnas: string[] = ['select', 'periodo', 'inquilino', 'propiedad', 'monto', 'estado', 'accion'];
  selection = new SelectionModel<any>(true, []); 
  cargando: boolean = false;

  ngOnInit() {
    this.cargarDeudas(); // Carga inicial
    this.suscribirAEventos(); // <-- Añadir suscripción
  }

  suscribirAEventos() {
    this.eventosService.cuotasActualizadas$.subscribe(() => {
      this.mensaje.exito('El listado de deudas se ha actualizado automáticamente.');
      this.cargarDeudas(); // Llama a la función que refresca la lista de cuotas desde la API
    });
  }

  ngAfterViewInit() {
    this.dataSource.paginator = this.paginator;
    this.dataSource.sort = this.sort;
  }

  /**
   * Carga todas las cuotas pendientes desde el backend y las procesa.
   * Es la función principal de recarga de datos.
   */
  cargarDeudas() {
    this.cargando = true;
    this.selection.clear();

    this.api.getDeudas().subscribe({
        next: (res: any) => {
            
            const datosBrutos = res.data || res; 

            if (!datosBrutos || !Array.isArray(datosBrutos)) {
                this.dataSource.data = [];
                this.cargando = false;
                return;
            }

            const deudasProcesadas = datosBrutos.map((item: any) => ({
                ...item,
                saldo_pendiente: parseFloat(item.saldo_pendiente) || 0 
            }));
            
            this.dataSource.data = deudasProcesadas;
            this.cargando = false;
        
            if (this.paginator) {
                this.dataSource.paginator = this.paginator;
            }
            if (this.sort) {
                this.dataSource.sort = this.sort;
            }
        },
        error: (err) => {
            this.mensaje.error('No se pudieron cargar las deudas pendientes. Intente más tarde.');
            this.cargando = false;
            this.dataSource.data = [];
            console.error(err);
        }
    });
}
  

  aplicarFiltro(event: Event) {
    const filterValue = (event.target as HTMLInputElement).value;
    this.dataSource.filter = filterValue.trim().toLowerCase();

    if (this.dataSource.paginator) {
      this.dataSource.paginator.firstPage();
    }
  }

  // --- LÓGICA DE SELECCIÓN ---

  isAllSelected() {
    const numSelected = this.selection.selected.length;
    // Solo consideramos para la selección total las cuotas que NO están PAGADAS
    const numRows = this.dataSource.data.filter(row => row.estado !== 'PAGADA').length;
    return numSelected === numRows;
  }

  toggleAllRows() {
    if (this.isAllSelected()) {
      this.selection.clear();
    } else {
      // Selecciona solo las cuotas que NO están PAGADAS
      this.dataSource.data.filter(row => row.estado !== 'PAGADA').forEach(row => this.selection.select(row));
    }
  }
  
  /** * FIX NG5002: Esta función calcula el total. 
   * La llamamos desde el HTML para evitar el error de parseo. 
   */
  calcularTotalFlotante(): number {
    return this.selection.selected.reduce((acc, curr) => {
        const saldo = parseFloat(curr.saldo_pendiente);
        return acc + (isNaN(saldo) ? 0 : saldo);
    }, 0);
  }

  // --- LÓGICA DE COBRO Y COMPROBANTE ---

  cobrarSeleccionados() {
    const total = this.calcularTotalFlotante();
    const cuotas = this.selection.selected;

    // Abrir el diálogo de cobro
    const dialogRef = this.dialog.open(CobroDialogComponent, {
      width: '500px',
      data: { total: total, cuotas: cuotas }
    });

    dialogRef.afterClosed().subscribe(result => {
      if (result && result.cobroRegistrado) {
        this.selection.clear(); // Limpiamos la selección
        this.cargarDeudas(); // Recargamos la tabla
        // Lógica de alerta movida al proceso del CobroDialogComponent si es necesario.
      }
    });
  }

  verComprobante(cuota: any) {
    if (cuota.pagos && cuota.pagos.length > 0) {
      const ultimoPago = cuota.pagos[cuota.pagos.length - 1];

      if (ultimoPago.ruta_pdf) {
        // Asegúrate que esta URL coincida con tu backend
        const url = `http://localhost:8000/storage/${ultimoPago.ruta_pdf}`;
        window.open(url, '_blank');
      } else {
        this.mensaje.error('Esta cuota no tiene un comprobante PDF asociado.');
      }
    }
  }
}