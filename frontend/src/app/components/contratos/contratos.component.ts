import { Component, inject, OnInit, ViewChild } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ApiService } from '../../services/api.service';
import { ContratoDialogComponent } from './contrato-dialog/contrato-dialog.component';

// Material
import { MatTableDataSource, MatTableModule } from '@angular/material/table';
import { MatPaginator, MatPaginatorModule } from '@angular/material/paginator';
import { MatSort, MatSortModule } from '@angular/material/sort';
import { MatDialog, MatDialogModule } from '@angular/material/dialog';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatChipsModule } from '@angular/material/chips';
import { ContratoDetalleComponent } from './contrato-detalle/contrato-detalle.component';
import { Title } from '@angular/platform-browser';

@Component({
  selector: 'app-contratos',
  standalone: true,
  imports: [
    CommonModule, MatTableModule, MatPaginatorModule, MatSortModule, 
    MatDialogModule, MatButtonModule, MatIconModule, MatInputModule, 
    MatFormFieldModule, MatChipsModule
  ],
  templateUrl: './contratos.component.html',
  styleUrl: './contratos.component.css'
})
export class ContratosComponent implements OnInit {
  private api = inject(ApiService);
  private dialog = inject(MatDialog);
  private title = inject(Title);

  columnas: string[] = ['id', 'propiedad', 'inquilino', 'vigencia', 'estado', 'acciones'];
  dataSource = new MatTableDataSource<any>([]);

  @ViewChild(MatPaginator) paginator!: MatPaginator;
  @ViewChild(MatSort) sort!: MatSort;

  ngOnInit() {
    this.cargarDatos();
    this.title.setTitle('Gestion | Contratos');
  }

  cargarDatos() {
    this.api.getContratos().subscribe(data => {
      this.dataSource.data = data;
      this.dataSource.paginator = this.paginator;
      this.dataSource.sort = this.sort;
      
      // Filtro personalizado
      this.dataSource.filterPredicate = (data: any, filter: string) => {
        const info = `${data.inquilino.nombre_completo} ${data.propiedad.direccion}`.toLowerCase();
        return info.includes(filter);
      };
    });
  }

  aplicarFiltro(event: Event) {
    const valor = (event.target as HTMLInputElement).value;
    this.dataSource.filter = valor.trim().toLowerCase();
  }

  nuevoContrato() {
    this.dialog.open(ContratoDialogComponent, { width: '700px' })
      .afterClosed().subscribe(res => {
        if (res) this.cargarDatos(); // Si guardó, recargamos la tabla
      });
  }
  verDetalle(contrato: any) {
    this.dialog.open(ContratoDetalleComponent, {
      width: '800px', // Más ancho para que entre todo
      data: contrato
    });
  }
}