import { Component, inject, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink } from '@angular/router'; // Para los botones de acceso rápido
import { ApiService } from '../../services/api.service';
import { Title } from '@angular/platform-browser';

// Material Imports
import { MatCardModule } from '@angular/material/card';
import { MatIconModule } from '@angular/material/icon';
import { MatButtonModule } from '@angular/material/button';
import { MatListModule } from '@angular/material/list';
import { MatDividerModule } from '@angular/material/divider';

@Component({
  selector: 'app-dashboard',
  standalone: true,
  imports: [
    CommonModule, RouterLink, MatCardModule, MatIconModule, 
    MatButtonModule, MatListModule, MatDividerModule
  ],
  templateUrl: './dashboard.component.html',
  styleUrl: './dashboard.component.css'
})
export class DashboardComponent implements OnInit {
  private api = inject(ApiService);
  private title = inject(Title);
  
  data: any = {
    total_propiedades: 0,
    contratos_activos: 0,
    deuda_pendiente: 0,
    recaudado_mes: 0,
    proximos_vencimientos: [],
    ultimos_pagos: []
  };

  ngOnInit() {
    this.api.getDashboardStats().subscribe(res => {
      this.data = res;
    });
    this.title.setTitle('Gestion | Dashboard');
  }
}