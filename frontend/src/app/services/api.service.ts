import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class ApiService {
  private http = inject(HttpClient);
  private apiUrl = 'http://localhost:8000/api'; 

  constructor() { }

  //PROPIETARIOS
  getPropietarios(): Observable<any> {
    return this.http.get(`${this.apiUrl}/propietarios`);
  }

  crearPropietario(data: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/propietarios`, data);
  }

  //PROPIEDADES
  getPropiedades(): Observable<any> {
    return this.http.get(`${this.apiUrl}/propiedades`);
  }

  crearPropiedad(data: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/propiedades`, data);
  }

  //INQUILINOS
  getInquilinos(): Observable<any> {
    return this.http.get(`${this.apiUrl}/inquilinos`);
  }

  crearInquilino(data: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/inquilinos`, data);
  }

  editarInquilino(id: number, datos: any): Observable<any> {
    return this.http.put(`${this.apiUrl}/inquilinos/${id}`, datos);
  }

  eliminarInquilino(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/inquilinos/${id}`);
  }

  //CONTRATOS
  crearContrato(data: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/contratos`, data);
  }

  //PAGOS
  registrarPago(data: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/pagos`, data);
  }
  //CUOTAS
  getCuotasPendientes(): Observable<any> {
    return this.http.get(`${this.apiUrl}/cuotas`);
  }
  //DASHBOARD
  getDashboardStats(): Observable<any> {
    return this.http.get(`${this.apiUrl}/dashboard`);
  }
  //LIQUIDACIONES
  generarLiquidacion(data: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/liquidaciones`, data);
  }
  //CONTRATOS
  getContratos(): Observable<any> {
    return this.http.get(`${this.apiUrl}/contratos`);
  }
  //FINALIZAR CONTRATO
  finalizarContrato(id: number): Observable<any> {
    return this.http.patch(`${this.apiUrl}/contratos/${id}/finalizar`, {});
  }
  //LIQUIDACIONES
  getLiquidaciones(): Observable<any> {
    return this.http.get(`${this.apiUrl}/liquidaciones`);
  }
  // PAGOS MULTIPLES 
  registrarPagoMultiple(datos: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/pagos/multiple`, datos);
  }
  //PROPIETARIOS
  getPropietarioDetalle(id: number): Observable<any> {
    return this.http.get(`${this.apiUrl}/propietarios/${id}`);
  }
  //IMAGENES
  getPropiedadDetalle(id: number): Observable<any> {
    return this.http.get(`${this.apiUrl}/propiedades/${id}`);
  }

  subirFotoPropiedad(id: number, archivo: File): Observable<any> {
    const formData = new FormData();
    formData.append('imagen', archivo);
    return this.http.post(`${this.apiUrl}/propiedades/${id}/fotos`, formData);
  }
  editarPropiedad(id: number, datos: any): Observable<any> {
    return this.http.put(`${this.apiUrl}/propiedades/${id}`, datos);
  }

  eliminarPropiedad(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/propiedades/${id}`);
  }
  //GASTOS
  getGastosPropiedad(id: number): Observable<any> {
    return this.http.get(`${this.apiUrl}/propiedades/${id}/gastos`);
  }
  
  crearGasto(datos: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/gastos`, datos);
  }

  eliminarGasto(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/gastos/${id}`);
  }
  // EDITAR PROPIETARIO
  editarPropietario(id: number, datos: any): Observable<any> {
    return this.http.put(`${this.apiUrl}/propietarios/${id}`, datos);
  }
  eliminarPropietario(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/propietarios/${id}`);
  }
  // REGISTRO DE USUARIO
  registrarUsuario(datos: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/register`, datos);
  }
  // --- CAJA CHICA / TESORERÍA ---
  getMovimientosCaja(mes: number, anio: number): Observable<any> {
    return this.http.get(`${this.apiUrl}/caja?mes=${mes}&anio=${anio}`);
  }

  getBalanceCaja(mes: number, anio: number): Observable<any> {
    return this.http.get(`${this.apiUrl}/caja/balance?mes=${mes}&anio=${anio}`);
  }

  registrarMovimientoCaja(datos: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/caja`, datos);
  }

  eliminarMovimientoCaja(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/caja/${id}`);
  }
  // --- MANTENIMIENTO / TICKETS ---
  getTickets(): Observable<any> {
    return this.http.get(`${this.apiUrl}/tickets`);
  }

  crearTicket(data: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/tickets`, data);
  }

  actualizarTicket(id: number, data: any): Observable<any> {
    return this.http.put(`${this.apiUrl}/tickets/${id}`, data);
  }

  eliminarTicket(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/tickets/${id}`);
  }
}