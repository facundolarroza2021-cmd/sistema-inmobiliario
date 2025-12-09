import { Component, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatTableModule } from '@angular/material/table';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatDialog, MatDialogModule } from '@angular/material/dialog'; // <--- Importante
import { MatTableDataSource } from '@angular/material/table';
import { ApiService } from '../../services/api.service';
import { MensajeService } from '../../services/mensaje.service';
import { PropietarioDialogComponent } from './propietario-dialog/propietario-dialog.component'; // Verifica la ruta
import {RouterLink} from '@angular/router';
import { ViewChild, AfterViewInit } from '@angular/core'; // <--- Agrega ViewChild y AfterViewInit
// ...
import { MatPaginator, MatPaginatorModule } from '@angular/material/paginator'; // <--- Agrega estos dos

@Component({
  selector: 'app-propietarios',
  standalone: true,
  imports: [CommonModule, MatTableModule, MatButtonModule, MatIconModule, MatDialogModule, RouterLink, MatPaginatorModule],
  templateUrl: './propietarios.component.html',
  styleUrls: ['./propietarios.component.css']
})
export class PropietariosComponent implements OnInit {
  
  private api = inject(ApiService);
  private dialog = inject(MatDialog); // <--- Inyectar Dialog
  private mensaje = inject(MensajeService); // <--- Inyectar Mensajes

  dataSource = new MatTableDataSource<any>([]);
  columnasMostradas: string[] = ['id', 'propietario', 'identificacion', 'contacto', 'acciones']; // <--- Asegurate que 'acciones' esté aquí

  ngOnInit(): void {
    this.cargarDatos();
  }

  cargarDatos() {
    this.api.getPropietarios().subscribe(res => {
      this.dataSource.data = res;
    });
  }

  // --- FUNCIÓN EDITAR ---
  editar(propietario: any) {
    this.dialog.open(PropietarioDialogComponent, {
      width: '500px',
      data: propietario // <--- Pasamos el propietario al formulario
    }).afterClosed().subscribe(result => {
      if (result) {
        this.cargarDatos(); // Recargamos la tabla si hubo cambios
      }
    });
  }

  // --- FUNCIÓN ELIMINAR ---
  eliminar(propietario: any) {
    if(confirm(`¿Estás seguro de eliminar a ${propietario.nombre_completo}?`)) {
      this.api.eliminarPropietario(propietario.id).subscribe({
        next: () => {
          this.mensaje.exito('Propietario eliminado');
          this.cargarDatos();
        },
        error: (err) => {
          this.mensaje.error('No se puede eliminar (¿Tiene propiedades asociadas?)');
        }
      });
    }
  }
  abrirDialogo() {
    this.dialog.open(PropietarioDialogComponent, {
      width: '500px',
      data: null // <--- IMPORTANTE: data es null para indicar "Modo Creación"
    }).afterClosed().subscribe(result => {
      if (result) {
        this.cargarDatos(); // Recargar la tabla si se creó uno nuevo
      }
    });
  }
  aplicarFiltro(event: Event) {
    const valorFiltro = (event.target as HTMLInputElement).value;
    this.dataSource.filter = valorFiltro.trim().toLowerCase();
  }

  getColorAvatar(nombre: string): string {
    if (!nombre) return '#333333'; // Color gris oscuro por defecto si no hay nombre

    // Lista de colores bonitos (Material Design)
    const colores = [
      '#d32f2f', '#c2185b', '#7b1fa2', '#512da8', '#303f9f',
      '#1976d2', '#0288d1', '#0097a7', '#00796b', '#388e3c',
      '#689f38', '#afb42b', '#fbc02d', '#ffa000', '#f57c00',
      '#e64a19', '#5d4037', '#616161', '#455a64'
    ];

    // Matemáticas simples para que "Juan" siempre tenga el mismo color
    let hash = 0;
    for (let i = 0; i < nombre.length; i++) {
      hash = nombre.charCodeAt(i) + ((hash << 5) - hash);
    }

    // Elegimos un color de la lista basado en el hash
    const index = Math.abs(hash % colores.length);
    return colores[index];
  }
}