// frontend/src/app/services/mensaje.service.ts

import { Injectable, inject } from '@angular/core';
import Swal, { SweetAlertResult } from 'sweetalert2'; // Importar SweetAlert2

@Injectable({
  providedIn: 'root'
})
export class MensajeService {
  // Asumimos que aquí tenías la inyección de MatSnackBar, ahora la eliminamos

  constructor() { }

  /**
   * Muestra un mensaje de éxito.
   * @param mensaje Contenido del mensaje.
   */
  mostrarExito(mensaje: string): void {
    Swal.fire({
      icon: 'success',
      title: '¡Éxito!',
      text: mensaje,
      showConfirmButton: false,
      timer: 3000 // Desaparece después de 3 segundos
    });
  }

  /**
   * Muestra un mensaje de error.
   * @param mensaje Contenido del mensaje.
   */
  mostrarError(mensaje: string): void {
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: mensaje,
      showConfirmButton: true, // Mantener el botón para que el usuario pueda leer
    });
  }

  /**
   * Muestra un mensaje de advertencia o información.
   * @param mensaje Contenido del mensaje.
   */
  mostrarAdvertencia(mensaje: string): void {
    Swal.fire({
      icon: 'warning',
      title: 'Advertencia',
      text: mensaje,
      showConfirmButton: true,
    });
  }
  
  /**
   * Muestra un diálogo de confirmación antes de una acción destructiva.
   * @param titulo Título del diálogo.
   * @param texto Mensaje de la acción a confirmar.
   * @returns Promise<SweetAlertResult> que resuelve si el usuario hizo clic en 'Confirmar'.
   */
  confirmarAccion(titulo: string, texto: string): Promise<SweetAlertResult> {
    return Swal.fire({
      title: titulo,
      text: texto,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6', // Azul
      cancelButtonColor: '#d33', // Rojo
      confirmButtonText: 'Sí, estoy seguro',
      cancelButtonText: 'Cancelar'
    });
  }

  async confirmarEliminacion(titulo: string, texto: string) {
    return Swal.fire({
      title: titulo,
      text: texto,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#e91e63', // Color Fucsia institucional
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar',
      reverseButtons: true,
      heightAuto: false // Evita saltos visuales en Angular
    });
  }
}