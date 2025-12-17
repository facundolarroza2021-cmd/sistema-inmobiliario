import { Component, inject, OnInit, ViewChild } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ApiService } from '../../services/api.service';
import { PropiedadDialogComponent } from './propiedad-dialog/propiedad-dialog.component'; // Importar Dialog
import { RouterLink } from '@angular/router';
// Material Imports
import { MatTableDataSource, MatTableModule } from '@angular/material/table';
import { MatPaginator, MatPaginatorModule } from '@angular/material/paginator';
import { MatSort, MatSortModule } from '@angular/material/sort';
import { MatDialog, MatDialogModule } from '@angular/material/dialog';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatChipsModule } from '@angular/material/chips';
import { MensajeService } from '../../services/mensaje.service';

@Component({
  selector: 'app-propiedades',
  standalone: true,
  imports: [
    CommonModule, MatTableModule, MatPaginatorModule, MatSortModule, 
    MatDialogModule, MatButtonModule, MatIconModule, MatInputModule, 
    MatFormFieldModule, MatChipsModule ,RouterLink
  ],
  templateUrl: './propiedades.component.html',
  styleUrl: './propiedades.component.css'
})
export class PropiedadesComponent implements OnInit {
  private api = inject(ApiService);
  private dialog = inject(MatDialog);
  private mensaje = inject(MensajeService);

  // Tabla Profesional
  columnas: string[] = ['id', 'direccion', 'tipo', 'dueño', 'acciones'];
  dataSource = new MatTableDataSource<any>([]);

  @ViewChild(MatPaginator) paginator!: MatPaginator;
  @ViewChild(MatSort) sort!: MatSort;

  ngOnInit() {
    this.cargarDatos();
  }

  cargarDatos() {
    this.api.getPropiedades().subscribe(data => {
      this.dataSource.data = data;
      this.dataSource.paginator = this.paginator;
      this.dataSource.sort = this.sort;

      // Filtro personalizado: Busca por dirección O por nombre del dueño
      this.dataSource.filterPredicate = (data: any, filter: string) => {
        const info = `${data.direccion} ${data.tipo} ${data.propietario?.nombre_completo}`.toLowerCase();
        return info.includes(filter);
      };
    });
  }

  aplicarFiltro(event: Event) {
    const valor = (event.target as HTMLInputElement).value;
    this.dataSource.filter = valor.trim().toLowerCase();
  }

  abrirDialogo() {
    this.dialog.open(PropiedadDialogComponent, { width: '500px' })
      .afterClosed().subscribe(res => {
        if (res) this.cargarDatos(); // Recargar tabla si se guardó
      });
  }

  getIcono(tipo: string): string {
    const t = tipo.toLowerCase();
    if (t.includes('depto') || t.includes('departamento')) return 'apartment';
    if (t.includes('local') || t.includes('oficina')) return 'store';
    if (t.includes('terreno')) return 'landscape';
    if (t.includes('cochera')) return 'directions_car';
    return 'home'; 
  }

  getClaseIcono(tipo: string): string {
    const t = tipo.toLowerCase();
    if (t.includes('depto')) return 'icon-depto';
    if (t.includes('local')) return 'icon-local';
    return 'icon-casa'; 
  }
  editar(propiedad: any) {
    this.dialog.open(PropiedadDialogComponent, {
      width: '500px',
      data: propiedad 
    }).afterClosed().subscribe(res => {
      if (res) this.cargarDatos();
    });
  }

  async eliminar(propiedad: any) {
    // 1. Llamamos a la alerta de confirmación
    const resultado = await this.mensaje.confirmarEliminacion(
      '¿Eliminar Propiedad?',
      `Estás por borrar el inmueble en ${propiedad.direccion}.`
    );

    // 2. Si el usuario confirmó (le dio al botón fucsia)
    if (resultado.isConfirmed) {
      this.api.eliminarPropiedad(propiedad.id).subscribe({
        next: () => {
          this.mensaje.mostrarExito('La propiedad ha sido borrada.');
          this.cargarDatos(); // Recarga la tabla
        },
        error: (err) => {
          this.mensaje.mostrarError('No se pudo eliminar. Es posible que tenga contratos activos.');
        }
      });
    }
  }
}