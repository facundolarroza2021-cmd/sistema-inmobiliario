import { Injectable, inject } from '@angular/core';
import { MatSnackBar } from '@angular/material/snack-bar';

@Injectable({
  providedIn: 'root'
})
export class MensajeService {
  private snackBar = inject(MatSnackBar);

  exito(texto: string) {
    this.snackBar.open(texto, 'Cerrar', {
      duration: 3000, 
      horizontalPosition: 'right', 
      verticalPosition: 'bottom', 
      panelClass: ['snack-exito'] 
    });
  }

  error(texto: string) {
    this.snackBar.open(texto, 'OK', {
      duration: 5000, 
      horizontalPosition: 'center',
      verticalPosition: 'bottom',
      panelClass: ['snack-error'] 
    });
  }
}