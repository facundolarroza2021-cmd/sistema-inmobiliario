import { Injectable, inject, signal } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, tap } from 'rxjs';
import { Router } from '@angular/router';

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private http = inject(HttpClient);
  private router = inject(Router);
  // Asegúrate que este puerto coincida con tu backend (8000 según tu docker-compose)
  private apiUrl = 'http://localhost:8000/api'; 

  // Usamos señales (Signals) para manejar el estado del usuario de forma reactiva
  currentUser = signal<any>(null);

  constructor() {
    // Al recargar la página, verificamos si ya había un usuario guardado
    const savedUser = localStorage.getItem('user');
    if (savedUser) {
      this.currentUser.set(JSON.parse(savedUser));
    }
  }

  // LOGIN: Envía credenciales y guarda el token
  login(credentials: any): Observable<any> {
    return this.http.post(`${this.apiUrl}/login`, credentials).pipe(
      tap((response: any) => {
        // 1. Guardar en LocalStorage (Persistencia)
        localStorage.setItem('token', response.access_token);
        localStorage.setItem('user', JSON.stringify(response.user));
        
        // 2. Actualizar la señal (Estado en memoria)
        this.currentUser.set(response.user);
      })
    );
  }

  // LOGOUT: Limpia todo y redirige
  logout() {
    // Opcional: Avisar al backend para borrar el token de la base de datos
    const token = localStorage.getItem('token');
    if (token) {
        this.http.post(`${this.apiUrl}/logout`, {}).subscribe();
    }
    
    // Limpieza local
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    this.currentUser.set(null);
    this.router.navigate(['/login']);
  }

  // Utilidad para obtener el token actual
  getToken() {
    return localStorage.getItem('token');
  }

  // Verifica si hay sesión activa
  isAuthenticated(): boolean {
    return !!localStorage.getItem('token');
  }

  // Verifica si el usuario tiene cierto rol (útil para ocultar botones)
  hasRole(allowedRoles: string[]): boolean {
    const user = this.currentUser();
    return user && allowedRoles.includes(user.role);
  }
}