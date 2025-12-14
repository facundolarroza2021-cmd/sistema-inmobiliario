import { Component, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ActivatedRoute } from '@angular/router';
import { ApiService } from '../../../services/api.service';
import { MatDialog } from '@angular/material/dialog';
// ... tus otros imports (MatButton, MatIcon, etc.)
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
    const id = this.route.snapshot.paramMap.get('id');
    
    if(id) {
      this.api.getPropietarioDetalle(+id).subscribe(res => {
        
        // 1. Detectamos dónde vienen los datos (a veces Laravel usa .data)
        const d = res.data || res; 

        console.log('Datos Crudos:', d); // Para verificar

        // 2. TRADUCCIÓN MANUAL (Mapeo)
        // Convertimos lo que manda la API a lo que espera tu HTML
        this.propietario = {
          id: d.id,
          nombre_completo: d.nombre,          // API: nombre -> HTML: nombre_completo
          dni: d.identificacion,              // API: identificacion -> HTML: dni
          email: d.contacto?.email,           // Sacamos del objeto contacto
          telefono: d.contacto?.telefono,     // Sacamos del objeto contacto
          cbu: d.contacto?.cbu,               // Sacamos del objeto contacto
          
          propiedades: d.propiedades || [],   
          liquidaciones: d.liquidaciones || [] 
        };

        // 3. Asignamos los KPIs
        this.kpis = d.kpis || { total_propiedades: 0, ocupacion: 0 };
      });
    }
  }

  // --- CORRECCIÓN CRÍTICA AQUÍ ---
  cargarDatos(id: number) {
    // Antes llamabas a getPropietarios() (TODOS), ahora llamamos al detalle (UNO)
    this.api.getPropietarioDetalle(id).subscribe(res => {
      const respuestaReal = res.data || res;
      this.propietario = respuestaReal.datos;
      this.kpis = respuestaReal.kpis;
    });
  }

  getColorAvatar(id: number): string {
    if (!id) return '#ccc'; // Protección por si id es undefined
    const colores = ['#e57373', '#ba68c8', '#64b5f6', '#4db6ac', '#ffb74d'];
    return colores[id % colores.length];
  }

  editarPerfil() {
    const dialogRef = this.dialog.open(PropietarioDialogComponent, {
      width: '500px',
      data: this.propietario
    });

    dialogRef.afterClosed().subscribe(result => {
      if (result) {
        this.cargarDatos(this.propietario.id);
      }
    });
  }
}