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
import Swal from 'sweetalert2';

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
      cuotas_ids: cuotas.map(p => p.id),
      medio_pago: datosExtra.forma_pago,
      observacion: datosExtra.observacion,
      monto: datosExtra.monto_final // <--- ENVIAMOS EL MONTO EDITADO
    };

    this.api.registrarPagoMultiple(payload).subscribe({
      next: (res: any) => {
        this.cargando = false;
        this.selection.clear();
        this.cargarDeudas(); // Recargamos la tabla para que salga el botón nuevo

        // ALERTA CON BOTÓN DE DESCARGA
        if (res.url_pdf) {
          Swal.fire({
            title: '¡Cobro Exitoso! 💰',
            text: 'El pago se registró correctamente.',
            icon: 'success',
            showCancelButton: true,
            confirmButtonText: 'Ver Recibo PDF',
            cancelButtonText: 'Cerrar'
          }).then((result) => {
            if (result.isConfirmed) {
              window.open(res.url_pdf, '_blank');
            }
          });
        } else {
          this.mensaje.exito('Pago registrado exitosamente');
        }
      },
      error: (err) => {
        // ... (tu manejo de errores) ...
      }
    });
  }

  verComprobante(cuota: any) {
    // Verificamos si la cuota tiene pagos asociados
    if (cuota.pagos && cuota.pagos.length > 0) {
      // Tomamos el último pago realizado
      const ultimoPago = cuota.pagos[cuota.pagos.length - 1];

      if (ultimoPago.ruta_pdf) {
        // Construimos la URL completa
        // Asegúrate que esta URL coincida con tu backend (puerto 8000)
        const url = `http://localhost:8000/storage/${ultimoPago.ruta_pdf}`;
        window.open(url, '_blank');
      } else {
        this.mensaje.error('Esta cuota no tiene un PDF generado.');
      }
    } else {
      // Caso raro: Está pagada pero no tiene registro en la tabla pagos
      this.mensaje.error('No se encontró el registro del pago.');
    }
  }
}