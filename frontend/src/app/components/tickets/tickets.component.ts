import { Component, inject, OnInit } from '@angular/core';
import { CommonModule, DatePipe } from '@angular/common';
import { ReactiveFormsModule, FormBuilder, FormGroup, Validators } from '@angular/forms';
import { ApiService } from '../../services/api.service';
import { AuthService } from '../../services/auth.service';
// Material Imports
import { MatTableDataSource, MatTableModule } from '@angular/material/table'; // <--- Importante
import { MatCardModule } from '@angular/material/card';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatChipsModule } from '@angular/material/chips';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { MatSnackBar, MatSnackBarModule } from '@angular/material/snack-bar';
import { MatMenuModule } from '@angular/material/menu'; // Para menú de acciones
import { MatDividerModule } from '@angular/material/divider';

@Component({
  selector: 'app-tickets',
  standalone: true,
  imports: [
    CommonModule, ReactiveFormsModule, DatePipe,
    MatTableModule, MatCardModule, MatButtonModule, MatIconModule, 
    MatChipsModule, MatFormFieldModule, MatInputModule, MatSelectModule, 
    MatSnackBarModule, MatMenuModule, MatDividerModule
  ],
  templateUrl: './tickets.component.html',
  styleUrls: ['./tickets.component.css']
})
export class TicketsComponent implements OnInit {
  private apiService = inject(ApiService);
  public authService = inject(AuthService); 
  private fb = inject(FormBuilder);
  private snackBar = inject(MatSnackBar);

  // Datos para la tabla
  dataSource = new MatTableDataSource<any>([]);
  displayedColumns: string[] = ['estado', 'prioridad', 'fecha', 'propiedad', 'titulo', 'acciones'];
  
  // Listas auxiliares
  propiedades: any[] = [];
  filtroEstado: string = 'TODOS'; // Filtro activo

  // Formulario
  mostrarFormulario = false;
  ticketForm: FormGroup = this.fb.group({
    propiedad_id: ['', Validators.required],
    titulo: ['', Validators.required],
    descripcion: [''],
    prioridad: ['MEDIA', Validators.required]
  });

  ngOnInit() {
    this.cargarDatos();
  }

  cargarDatos() {
    this.apiService.getPropiedades().subscribe(res => this.propiedades = res);
    
    this.apiService.getTickets().subscribe(data => {
      this.dataSource.data = data; // Guardamos todos los datos
      this.aplicarFiltro(); // Aplicamos filtro si había uno seleccionado
    });
  }

  // Lógica de Filtros (Chips)
  filtrar(estado: string) {
    this.filtroEstado = estado;
    this.aplicarFiltro();
  }

  aplicarFiltro() {
    if (this.filtroEstado === 'TODOS') {
      this.dataSource.filter = '';
    } else {
      this.dataSource.filter = this.filtroEstado.trim().toLowerCase();
    }
    
    // Sobrescribimos el predicado para que filtre solo por la columna estado
    this.dataSource.filterPredicate = (data: any, filter: string) => {
      return data.estado.toLowerCase() === filter;
    };
  }

  onSubmit() {
    if (this.ticketForm.valid) {
      this.apiService.crearTicket(this.ticketForm.value).subscribe({
        next: () => {
          this.snackBar.open('Reclamo registrado', 'Cerrar', { duration: 3000 });
          this.ticketForm.reset({ prioridad: 'MEDIA' });
          this.mostrarFormulario = false;
          this.cargarDatos();
        },
        error: () => this.snackBar.open('Error al crear ticket', 'Cerrar', { duration: 3000 })
      });
    }
  }

  cambiarEstado(ticket: any, nuevoEstado: string) {
    this.apiService.actualizarTicket(ticket.id, { estado: nuevoEstado }).subscribe(() => {
      this.cargarDatos();
      this.snackBar.open(`Estado actualizado a ${nuevoEstado}`, 'OK', { duration: 2000 });
    });
  }

  eliminar(id: number) {
    if (confirm('¿Eliminar registro?')) {
      this.apiService.eliminarTicket(id).subscribe(() => this.cargarDatos());
    }
  }
}