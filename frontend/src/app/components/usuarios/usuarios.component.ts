// frontend/src/app/components/usuarios/usuarios.component.ts

import { Component, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
// Importar tipos necesarios para la validación cruzada
import { 
  ReactiveFormsModule, 
  FormBuilder, 
  FormGroup, 
  Validators, 
  ValidationErrors, 
  AbstractControl, 
  ValidatorFn // <-- Importar ValidatorFn
} from '@angular/forms';
import { ApiService } from '../../services/api.service';
import { MensajeService } from '../../services/mensaje.service';
import { MatCardModule } from '@angular/material/card';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatButtonModule } from '@angular/material/button';
import { MatSelectModule } from '@angular/material/select';
import { MatSnackBarModule } from '@angular/material/snack-bar';
import { MatIconModule } from '@angular/material/icon'; // <-- Agregar MatIconModule (asumiendo que lo necesitas)

// ********************************************************************************
// *** 1. FUNCIÓN VALIDADORA CRUZADA PARA COINCIDENCIA DE CONTRASEÑAS ***
// ********************************************************************************
export const passwordMatchValidator: ValidatorFn = (control: AbstractControl): ValidationErrors | null => {
  const password = control.get('password');
  const passwordConfirmation = control.get('password_confirmation');

  if (!password || !passwordConfirmation) {
    return null; 
  }

  // Verifica si los valores existen y si son diferentes
  if (password.value && passwordConfirmation.value && password.value !== passwordConfirmation.value) {
    // Retorna el error al nivel del FormGroup
    return { 'notSame': true }; 
  }

  // Si coinciden o los campos están vacíos, no hay error de validación cruzada
  return null;
};
// ********************************************************************************


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
    MatSnackBarModule,
    MatIconModule // <-- AGREGADO
  ],
  templateUrl: './usuarios.component.html',
  styleUrls: ['./usuarios.component.css']
})
export class UsuariosComponent {
  private fb = inject(FormBuilder);
  private apiService = inject(ApiService);
  private mensaje = inject(MensajeService);

  // Variable para el toggle de visibilidad de contraseña
  showPassword: boolean = false; 
  isSaving: boolean = false;

  // ********************************************************************************
  // *** 2. INICIALIZACIÓN DEL FORMULARIO CON VALIDACIÓN CRUZADA ***
  // ********************************************************************************
  usuarioForm: FormGroup = this.fb.group({
    name: ['', Validators.required],
    email: ['', [Validators.required, Validators.email]],
    password: ['', [Validators.required, Validators.minLength(6)]], // Mínimo 6 caracteres
    password_confirmation: ['', Validators.required],
    role: ['administrativo', Validators.required]
  }, {
    // Aplica la validación cruzada aquí
    validators: passwordMatchValidator 
  });
  // ********************************************************************************

  roles = [
    { value: 'admin', viewValue: 'Administrador (Control Total)' },
    { value: 'administrativo', viewValue: 'Administrativo (Gestión)' },
    { value: 'cobrador', viewValue: 'Cobrador (Solo Cobros)' }
  ];

  onSubmit() {
    this.usuarioForm.markAllAsTouched(); // Asegura que se muestren los errores
    
    // El 'if (this.usuarioForm.valid)' ahora incluye la validación cruzada.
    if (this.usuarioForm.valid) {
      this.isSaving = true;
      
      // ELIMINAR la verificación manual, ya la hace el validador:
      // if (this.usuarioForm.value.password !== this.usuarioForm.value.password_confirmation) { ... }
      
      const datosRegistro = this.usuarioForm.value;
      delete datosRegistro.password_confirmation; // Eliminar del payload

      this.apiService.registrarUsuario(datosRegistro).subscribe({
        next: (res) => {
          this.isSaving = false;
          this.mensaje.mostrarExito('Usuario registrado');
          this.usuarioForm.reset({ role: 'administrativo' });
        },
        error: (err) => {
          this.isSaving = false;
          console.error(err);
          this.mensaje.mostrarError(err.error?.message || 'Error al crear usuario. Verifica el email.');
        }
      });
    } else {
        // Mostrar error general si la validación falla (ej: contraseñas no coinciden)
        if (this.usuarioForm.hasError('notSame')) {
            this.mensaje.mostrarError('Las contraseñas no coinciden.');
        } else {
             this.mensaje.mostrarAdvertencia('Por favor, complete todos los campos requeridos.');
        }
    }
  }
}