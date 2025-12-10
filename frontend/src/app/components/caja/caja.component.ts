import { Component, inject, OnInit } from '@angular/core';
import { CommonModule, CurrencyPipe, DatePipe } from '@angular/common';
import { ReactiveFormsModule, FormBuilder, FormsModule, FormGroup, Validators } from '@angular/forms';
import { ApiService } from '../../services/api.service';
import { AuthService } from '../../services/auth.service';
import { MatCardModule } from '@angular/material/card';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatButtonModule } from '@angular/material/button';
import { MatSelectModule } from '@angular/material/select';
import { MatIconModule } from '@angular/material/icon';
import { MatTableModule } from '@angular/material/table';
import { MatSnackBar, MatSnackBarModule } from '@angular/material/snack-bar';

@Component({
  selector: 'app-caja',
  standalone: true,
  imports: [
    CommonModule, ReactiveFormsModule, FormsModule,
    MatCardModule, MatFormFieldModule, MatInputModule, MatButtonModule,
    MatSelectModule, MatIconModule, MatTableModule, MatSnackBarModule,
    CurrencyPipe, DatePipe
  ],
  templateUrl: './caja.component.html',
  styleUrls: ['./caja.component.css']
})
export class CajaComponent implements OnInit {
  private fb = inject(FormBuilder);
  private apiService = inject(ApiService);
  public authService = inject(AuthService); // Público para usar en HTML
  private snackBar = inject(MatSnackBar);

  // Datos
  movimientos: any[] = [];
  balance = { ingresos: 0, egresos: 0, balance_neto: 0 };
  
  // Filtros de fecha (por defecto hoy)
  mesActual = new Date().getMonth() + 1;
  anioActual = new Date().getFullYear();
  
  meses = [
    {val: 1, label: 'Enero'}, {val: 2, label: 'Febrero'}, {val: 3, label: 'Marzo'},
    {val: 4, label: 'Abril'}, {val: 5, label: 'Mayo'}, {val: 6, label: 'Junio'},
    {val: 7, label: 'Julio'}, {val: 8, label: 'Agosto'}, {val: 9, label: 'Septiembre'},
    {val: 10, label: 'Octubre'}, {val: 11, label: 'Noviembre'}, {val: 12, label: 'Diciembre'}
  ];

  // Formulario de Nuevo Movimiento
  cajaForm: FormGroup = this.fb.group({
    tipo: ['EGRESO', Validators.required],
    categoria: ['', Validators.required],
    descripcion: [''],
    monto: ['', [Validators.required, Validators.min(0.01)]],
    fecha: [new Date().toISOString().substring(0, 10), Validators.required]
  });

  categoriasIngreso = ['Aporte Capital', 'Comisión Venta', 'Otros Ingresos'];
  categoriasEgreso = ['Librería/Insumos', 'Servicios (Luz/Internet)', 'Mantenimiento Oficina', 'Sueldos', 'Retiro Socios', 'Otros Gastos'];

  displayedColumns: string[] = ['fecha', 'tipo', 'categoria', 'descripcion', 'usuario', 'monto', 'acciones'];

  ngOnInit() {
    this.cargarDatos();
  }

  cargarDatos() {
    // 1. Cargar Balance
    this.apiService.getBalanceCaja(this.mesActual, this.anioActual).subscribe(res => {
      this.balance = res;
    });

    // 2. Cargar Lista
    this.apiService.getMovimientosCaja(this.mesActual, this.anioActual).subscribe(res => {
      this.movimientos = res;
    });
  }

  onSubmit() {
    if (this.cajaForm.valid) {
      this.apiService.registrarMovimientoCaja(this.cajaForm.value).subscribe({
        next: () => {
          this.snackBar.open('Movimiento registrado', 'Cerrar', { duration: 3000 });
          this.cajaForm.reset({
            tipo: 'EGRESO',
            fecha: new Date().toISOString().substring(0, 10)
          });
          this.cargarDatos(); // Recargar tabla y balance
        },
        error: () => this.snackBar.open('Error al registrar', 'Cerrar', { duration: 3000 })
      });
    }
  }

  eliminar(id: number) {
    if (confirm('¿Seguro que deseas eliminar este registro?')) {
      this.apiService.eliminarMovimientoCaja(id).subscribe(() => {
        this.cargarDatos();
        this.snackBar.open('Eliminado correctamente', 'Cerrar', { duration: 2000 });
      });
    }
  }

  cambiarPeriodo() {
    this.cargarDatos();
  }
}