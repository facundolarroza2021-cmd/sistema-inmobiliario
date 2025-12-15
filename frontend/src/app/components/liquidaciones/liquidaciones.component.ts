import { Component, inject, OnInit, ViewChild } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ApiService } from '../../services/api.service';
import { MensajeService } from '../../services/mensaje.service';
import { LiquidacionDialogComponent } from './liquidacion-dialog/liquidacion-dialog.component';

// Material Imports
import { MatTableDataSource, MatTableModule } from '@angular/material/table';
import { MatPaginator, MatPaginatorModule } from '@angular/material/paginator';
import { MatSort, MatSortModule } from '@angular/material/sort';
import { MatDialog, MatDialogModule } from '@angular/material/dialog';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import { MatFormFieldModule } from '@angular/material/form-field';

@Component({
  selector: 'app-liquidaciones',
  standalone: true,
  imports: [
    CommonModule, MatTableModule, MatPaginatorModule, MatSortModule, 
    MatDialogModule, MatButtonModule, MatIconModule, MatInputModule, 
    MatFormFieldModule
  ],
  templateUrl: './liquidaciones.component.html',
  styleUrl: './liquidaciones.component.css'
})
export class LiquidacionesComponent implements OnInit {
  private api = inject(ApiService);
  private mensaje = inject(MensajeService);
  private dialog = inject(MatDialog);

  columnas: string[] = ['fecha', 'propietario', 'periodo', 'monto', 'acciones'];
  dataSource = new MatTableDataSource<any>([]);

  @ViewChild(MatPaginator) paginator!: MatPaginator;
  @ViewChild(MatSort) sort!: MatSort;

  ngOnInit() {
    this.cargarDatos();
  }

  cargarDatos() {
    this.api.getLiquidaciones().subscribe(data => {
      this.dataSource.data = data;
      this.dataSource.paginator = this.paginator;
      this.dataSource.sort = this.sort;
      
      // Filtro por nombre de propietario
      this.dataSource.filterPredicate = (data: any, filter: string) => {
        return data.propietario.nombre_completo.toLowerCase().includes(filter);
      };
    });
  }

  aplicarFiltro(event: Event) {
    const valor = (event.target as HTMLInputElement).value;
    this.dataSource.filter = valor.trim().toLowerCase();
  }

  abrirDialogo() {
    const ref = this.dialog.open(LiquidacionDialogComponent, { width: '450px' });
    
    ref.afterClosed().subscribe(result => {
      if (result) {
        // Hacemos la llamada a la API aquí
        this.api.crearLiquidacion(result).subscribe((res: any) => {
          this.mensaje.exito(`Liquidación Creada: $${res.resumen.a_pagar}`);
          if (res.url_pdf) window.open(res.url_pdf, '_blank');
          this.cargarDatos();
        }, (err) => this.mensaje.error(err.error.error || err.message));
      }
    });
  }

  verPdf(ruta: string) {
    if(ruta) window.open(`http://localhost:8000/storage/${ruta}`, '_blank');
  }

  // Color para avatar
  getColorAvatar(id: number): string {
    const colores = ['#37474f', '#455a64', '#546e7a', '#78909c', '#607d8b']; // Tonos grises azulados financieros
    return colores[id % colores.length];
  }
}