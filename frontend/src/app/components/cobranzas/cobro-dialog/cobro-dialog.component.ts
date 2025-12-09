import { Component, Inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { MatDialogRef, MAT_DIALOG_DATA, MatDialogModule } from '@angular/material/dialog';
import { MatButtonModule } from '@angular/material/button';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { MatIconModule } from '@angular/material/icon';

@Component({
  selector: 'app-cobro-dialog',
  standalone: true,
  imports: [
    CommonModule, FormsModule, MatDialogModule, MatButtonModule, 
    MatFormFieldModule, MatInputModule, MatSelectModule, MatIconModule
  ],
  templateUrl: './cobro-dialog.component.html'
})
export class CobroDialogComponent {
  
  seleccion = {
    forma_pago: 'EFECTIVO',
    observacion: ''
  };

  totalOriginal: number = 0;

  constructor(
    public dialogRef: MatDialogRef<CobroDialogComponent>,
    @Inject(MAT_DIALOG_DATA) public data: { total: number, cantidad: number }
  ) {
    this.totalOriginal = data.total;
  }

  confirmar() {
    this.dialogRef.close({
        ...this.seleccion,
        monto_final: this.data.total 
    });
  }
}