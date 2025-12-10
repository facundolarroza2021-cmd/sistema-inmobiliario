import { Routes } from '@angular/router';
import { LoginComponent } from './components/login/login.component'; // <--- Importar Login
import { authGuard } from './guards/auth.guard'; // <--- Importar Guard
import { DashboardComponent } from './components/dashboard/dashboard.component';
import { PropietariosComponent } from './components/propietarios/propietarios.component';
import { PropiedadesComponent } from './components/propiedades/propiedades.component';
import { InquilinosComponent } from './components/inquilinos/inquilinos.component';
import { ContratosComponent } from './components/contratos/contratos.component';
import { CobranzasComponent } from './components/cobranzas/cobranzas.component';
import { LiquidacionesComponent } from './components/liquidaciones/liquidaciones.component';
import {PropietarioDetalleComponent} from './components/propietarios/propietario-detalle/propietario-detalle.component';
import { PropiedadDetalleComponent } from './components/propiedades/propiedad-detalle/propiedad-detalle.component';
import { UsuariosComponent } from './components/usuarios/usuarios.component';
import { CajaComponent } from './components/caja/caja.component';

export const routes: Routes = [
    { path: 'login', component: LoginComponent }, // Ruta pública
    
    // Rutas protegidas
    { 
      path: '', 
      canActivate: [authGuard], // <--- Protegemos todo este bloque
      children: [
        { path: '', redirectTo: 'dashboard', pathMatch: 'full' },
        { path: 'dashboard', component: DashboardComponent },
        { path: 'propietarios', component: PropietariosComponent },
        { path: 'propiedades', component: PropiedadesComponent },
        { path: 'inquilinos', component: InquilinosComponent },
        { path: 'contratos', component: ContratosComponent },
        { path: 'cobranzas', component: CobranzasComponent },
        { path: 'liquidaciones', component: LiquidacionesComponent },
        { path: 'propietarios/:id', component: PropietarioDetalleComponent },
        { path: 'propiedades/:id', component: PropiedadDetalleComponent },
        { path: 'usuarios', component: UsuariosComponent },
        { path: 'caja', component: CajaComponent },
      ]
    },
];