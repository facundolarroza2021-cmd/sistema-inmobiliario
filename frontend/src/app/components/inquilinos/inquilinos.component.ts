import { Component, inject, OnInit, ViewChild } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ApiService } from '../../services/api.service';
import { InquilinoDialogComponent } from './inquilino-dialog/inquilino-dialog.component';
import { MensajeService } from '../../services/mensaje.service';
import { MatTableDataSource, MatTableModule } from '@angular/material/table';
import { MatPaginator, MatPaginatorModule } from '@angular/material/paginator';
import { MatSort, MatSortModule } from '@angular/material/sort';
import { MatDialog, MatDialogModule } from '@angular/material/dialog';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import { MatFormFieldModule } from '@angular/material/form-field';

@Component({
  selector: 'app-inquilinos',
  standalone: true,
  imports: [
    CommonModule, MatTableModule, MatPaginatorModule, MatSortModule, 
    MatDialogModule, MatButtonModule, MatIconModule, MatInputModule, 
    MatFormFieldModule
  ],
  templateUrl: './inquilinos.component.html',
  styleUrl: './inquilinos.component.css'
})
export class InquilinosComponent implements OnInit {
  private api = inject(ApiService);
  private dialog = inject(MatDialog);
  private mensaje = inject(MensajeService);

  columnas: string[] = ['id', 'nombre', 'dni', 'contacto', 'acciones'];
  dataSource = new MatTableDataSource<any>([]);

  @ViewChild(MatPaginator) paginator!: MatPaginator;
  @ViewChild(MatSort) sort!: MatSort;

  ngOnInit() {
    this.cargarDatos();
  }

  cargarDatos() {
    this.api.getInquilinos().subscribe(data => {
      this.dataSource.data = data;
      this.dataSource.paginator = this.paginator;
      this.dataSource.sort = this.sort;
    });
  }

  aplicarFiltro(event: Event) {
    const valor = (event.target as HTMLInputElement).value;
    this.dataSource.filter = valor.trim().toLowerCase();
  }

  abrirDialogo() {
    this.dialog.open(InquilinoDialogComponent, { width: '500px' })
      .afterClosed().subscribe(res => {
        if (res) this.cargarDatos();
      });
  }

  getColorAvatar(id: number): string {
    const colores = [
      '#ec407a', '#ab47bc', '#7e57c2', '#5c6bc0', '#42a5f5', 
      '#26a69a', '#66bb6a', '#ffa726', '#ff7043', '#8d6e63'
    ];
    return colores[id % colores.length];
  }
  editar(inquilino: any) {
    this.dialog.open(InquilinoDialogComponent, {
      width: '500px',
      data: inquilino // <--- Pasamos el objeto a editar
    }).afterClosed().subscribe(res => {
      if (res) this.cargarDatos(); // Recargar tabla si hubo cambios
    });
  }
  eliminar(inquilino: any) {
    if(confirm(`¿Estás seguro de eliminar a ${inquilino.nombre_completo}?`)) {
      this.api.eliminarInquilino(inquilino.id).subscribe(() => {
        this.mensaje.mostrarExito('Inquilino eliminado correctamente');
        this.cargarDatos();
      }, (err) => {
        this.mensaje.mostrarError('No se puede eliminar (quizás tiene contratos activos)');
      });
    }
  }
}