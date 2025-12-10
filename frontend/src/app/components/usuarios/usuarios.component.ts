import { Component, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule, FormBuilder, FormGroup, Validators } from '@angular/forms';
import { ApiService } from '../../services/api.service';
import { MatCardModule } from '@angular/material/card';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatButtonModule } from '@angular/material/button';
import { MatSelectModule } from '@angular/material/select';
import { MatSnackBar, MatSnackBarModule } from '@angular/material/snack-bar';

@Component({
  selector: 'app-usuarios',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    MatCardModule,
    MatFormFieldModule,
    MatInputModule,
    MatButtonModule,
    MatSelectModule,
    MatSnackBarModule
  ],
  templateUrl: './usuarios.component.html',
  styleUrls: ['./usuarios.component.css']
})
export class UsuariosComponent {
  private fb = inject(FormBuilder);
  private apiService = inject(ApiService);
  private snackBar = inject(MatSnackBar);

  usuarioForm: FormGroup = this.fb.group({
    name: ['', Validators.required],
    email: ['', [Validators.required, Validators.email]],
    password: ['', [Validators.required, Validators.minLength(6)]],
    password_confirmation: ['', Validators.required], // Laravel pide confirmación
    role: ['administrativo', Validators.required] // Valor por defecto
  });

  roles = [
    { value: 'admin', viewValue: 'Administrador (Control Total)' },
    { value: 'administrativo', viewValue: 'Administrativo (Gestión)' },
    { value: 'cobrador', viewValue: 'Cobrador (Solo Cobros)' }
  ];

  onSubmit() {
    if (this.usuarioForm.valid) {
      // Validar que las contraseñas coincidan manualmente antes de enviar
      if (this.usuarioForm.value.password !== this.usuarioForm.value.password_confirmation) {
        this.mostrarSnack('Las contraseñas no coinciden');
        return;
      }

      this.apiService.registrarUsuario(this.usuarioForm.value).subscribe({
        next: (res) => {
          this.mostrarSnack('Usuario creado exitosamente');
          this.usuarioForm.reset({ role: 'administrativo' }); // Limpiar y dejar rol default
        },
        error: (err) => {
          console.error(err);
          this.mostrarSnack('Error al crear usuario. Verifica el email.');
        }
      });
    }
  }

  mostrarSnack(mensaje: string) {
    this.snackBar.open(mensaje, 'Cerrar', { duration: 3000 });
  }
}