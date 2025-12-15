// app/services/eventos.service.ts
import { Injectable } from '@angular/core';
import { Subject, Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class EventosService {
  private cuotasActualizadasSubject = new Subject<void>();

  // Evento que se puede escuchar
  cuotasActualizadas$: Observable<void> = this.cuotasActualizadasSubject.asObservable();

  // Método para emitir el evento
  emitirCuotasActualizadas() {
    this.cuotasActualizadasSubject.next();
  }
}