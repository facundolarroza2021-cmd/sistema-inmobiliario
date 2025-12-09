import { Component, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ActivatedRoute } from '@angular/router'; // Para leer el ID de la URL
import { ApiService } from '../../../services/api.service';
import { MatDialog } from '@angular/material/dialog';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatCardModule } from '@angular/material/card';
import { MatTabsModule } from '@angular/material/tabs';
import { PropietarioDialogComponent } from '../propietario-dialog/propietario-dialog.component';

@Component({
  selector: 'app-propietario-detalle',
  standalone: true,
  imports: [CommonModule, MatButtonModule, MatIconModule, MatCardModule, MatTabsModule],
  templateUrl: './propietario-detalle.component.html',
  styleUrl: './propietario-detalle.component.css'
})
export class PropietarioDetalleComponent implements OnInit {
  private route = inject(ActivatedRoute);
  private api = inject(ApiService);
  private dialog = inject(MatDialog);

  propietario: any = null;
  kpis: any = { total_propiedades: 0, ocupacion: 0 };

  ngOnInit() {
    // Obtenemos el ID de la URL (ej: /propietarios/5)
    const id = this.route.snapshot.paramMap.get('id');
    
    if(id) {
      this.api.getPropietarioDetalle(+id).subscribe(res => {
        this.propietario = res.datos;
        this.kpis = res.kpis;
      });
    }
  }
  cargarDatos(id: number) {
    this.api.getPropietarios().subscribe(res => {
      this.propietario = res;
    });
  }

  getColorAvatar(id: number): string {
    const colores = ['#e57373', '#ba68c8', '#64b5f6', '#4db6ac', '#ffb74d'];
    return colores[id % colores.length];
  }
  editarPerfil() {
    // Abrimos el dialog pasándole EL PROPIETARIO ACTUAL
    const dialogRef = this.dialog.open(PropietarioDialogComponent, {
      width: '500px',
      data: this.propietario // <--- Aquí pasamos la data
    });

    dialogRef.afterClosed().subscribe(result => {
      if (result) {
        // Si guardó cambios, recargamos la página para verlos
        this.cargarDatos(this.propietario.id);
      }
    });
  }
}