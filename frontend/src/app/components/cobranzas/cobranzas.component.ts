import { Component, inject, OnInit, ViewChild } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ApiService } from '../../services/api.service';
import { MensajeService } from '../../services/mensaje.service';
import { SelectionModel } from '@angular/cdk/collections'; 
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
import { MatDialog } from '@angular/material/dialog';
import { CobroDialogComponent } from './cobro-dialog/cobro-dialog.component';

@Component({
  selector: 'app-cobranzas',
  standalone: true,
  imports: [
    CommonModule, MatCardModule, MatButtonModule, MatTableModule, 
    MatIconModule, MatInputModule, MatFormFieldModule, MatPaginatorModule, 
    MatSortModule, MatChipsModule, MatProgressSpinnerModule, MatCheckboxModule
  ],
  templateUrl: './cobranzas.component.html',
  styleUrl: './cobranzas.component.css'
})
export class CobranzasComponent implements OnInit {
  private api = inject(ApiService);
  private mensaje = inject(MensajeService);
  private dialog = inject(MatDialog);

  columnas: string[] = ['select', 'periodo', 'inquilino', 'propiedad', 'monto', 'estado', 'accion'];
  dataSource = new MatTableDataSource<any>([]);
  selection = new SelectionModel<any>(true, []); // true = múltiple
  cargando = true;

  @ViewChild(MatPaginator) paginator!: MatPaginator;
  @ViewChild(MatSort) sort!: MatSort;

  ngOnInit() {
    this.cargarDeudas();
  }

  cargarDeudas() {
    this.cargando = true;
    this.api.getCuotasPendientes().subscribe(data => {
      this.dataSource.data = data;
      this.dataSource.paginator = this.paginator;
      this.dataSource.sort = this.sort;
      this.cargando = false;
      
      // Filtro
      this.dataSource.filterPredicate = (data: any, filter: string) => {
        const info = `${data.periodo} ${data.contrato.inquilino.nombre_completo}`.toLowerCase();
        return info.includes(filter);
      };
    });
  }

  aplicarFiltro(event: Event) {
    const valor = (event.target as HTMLInputElement).value;
    this.dataSource.filter = valor.trim().toLowerCase();
  }

  // --- LÓGICA DE CHECKBOX ---
  
  // Si están todos seleccionados
  isAllSelected() {
    const numSelected = this.selection.selected.length;
    const numRows = this.dataSource.data.length;
    return numSelected === numRows;
  }

  // Master Toggle (Seleccionar todo)
  toggleAllRows() {
    if (this.isAllSelected()) {
      this.selection.clear();
      return;
    }
    this.selection.select(...this.dataSource.data);
  }

  cobrarSeleccionados() {
    const seleccionados = this.selection.selected;
    const pendientes = seleccionados.filter(s => s.estado === 'PENDIENTE' || s.estado === 'PARCIAL');

    if (pendientes.length === 0) {
      this.mensaje.error('Selecciona al menos una cuota PENDIENTE');
      return;
    }

    const total = pendientes.reduce((acc, curr) => acc + parseFloat(curr.saldo_pendiente), 0);

    // ABRIMOS EL MODAL DE CHECKOUT
    const dialogRef = this.dialog.open(CobroDialogComponent, {
      width: '400px',
      data: { total: total, cantidad: pendientes.length }
    });

    dialogRef.afterClosed().subscribe(resultado => {
      if (resultado) {
        // Si el usuario confirmó, procedemos al cobro con los datos del modal
        this.procesarCobro(pendientes, resultado);
      }
    });
  }

  procesarCobro(cuotas: any[], datosExtra: any) {
    this.cargando = true;
    
    const payload = {
      cuota_ids: cuotas.map(p => p.id),
      forma_pago: datosExtra.forma_pago,
      observacion: datosExtra.observacion,
      monto_recibido: datosExtra.monto_final // <--- ENVIAMOS EL MONTO EDITADO
    };

    this.api.registrarPagoMultiple(payload).subscribe((res: any) => {
      // ... lo mismo de antes ...
      this.mensaje.exito('Pago registrado exitosamente');
      this.selection.clear();
      this.cargarDeudas();
      if(res.url_pdf) window.open(res.url_pdf, '_blank');
    }, (err) => {
      this.cargando = false;
      this.mensaje.error(err.error.error || err.message); // Mejor manejo de error
    });
  }

  // Mantenemos la función de ver comprobante individual
  verComprobante(cuota: any) {
    if (cuota.pagos && cuota.pagos.length > 0) {
      const ultimoPago = cuota.pagos[cuota.pagos.length - 1];
      const url = `http://localhost:8000/storage/${ultimoPago.ruta_pdf}`;
      window.open(url, '_blank');
    }
  }
}