## 🏗️ Arquitectura de Base de Datos

```mermaid
erDiagram
    PROPIETARIO ||--|{ PROPIEDAD : "tiene muchas"
    PROPIETARIO ||--|{ LIQUIDACION : "recibe"
    
    PROPIEDAD ||--|{ CONTRATO : "se alquila en"
    PROPIEDAD ||--|{ IMAGEN : "tiene fotos"
    
    INQUILINO ||--|{ CONTRATO : "firma"
    
    CONTRATO ||--|{ CUOTA : "genera"
    CONTRATO ||--|{ GARANTE : "tiene"
    
    CUOTA ||--|{ PAGO : "se paga con"

    PROPIETARIO {
        int id
        string nombre
        string cbu
    }
    PROPIEDAD {
        int id
        string direccion
        string tipo
    }
    CONTRATO {
        date fecha_inicio
        date fecha_fin
        bool activo
    }
    CUOTA {
        string periodo
        decimal monto
        string estado
    }