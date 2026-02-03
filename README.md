# 🍝 Trattoria Bella Italia - Sistema de Gestión Integral (ERP)

![PHP](https://img.shields.io/badge/PHP-8.0%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-Relational-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![PDF Generation](https://img.shields.io/badge/PDF-Report-EC1C24?style=for-the-badge&logo=adobeacrobatreader&logoColor=white)
![Architecture](https://img.shields.io/badge/Architecture-MVC_Pattern-orange?style=for-the-badge)

## 📖 Descripción del Proyecto

**Trattoria Bella Italia** es una solución de software empresarial (ERP) diseñada para la digitalización completa de un restaurante temático italiano. El sistema no se limita a la toma de pedidos, sino que integra módulos de **gestión de relaciones con clientes (CRM)**, **inventario de proveedores** y **facturación fiscal automatizada**.

El núcleo del proyecto se distingue por su **arquitectura de datos persistente**, diseñada para garantizar la integridad transaccional en entornos de alta concurrencia (servicio de mesas en tiempo real).

---

## 🔄 Lógica de Negocio y Flujo de Trabajo

El sistema implementa un ciclo de vida de venta basado en estados, asegurando que la información operativa y financiera nunca se pierda.

### 1. Toma de Comanda (POS)
* Interfaz ágil (`formulariocomandas.php`) conectada al catálogo de platos (`plato`, `producto`).
* Permite la selección dinámica de productos y cantidades.

### 2. Persistencia y Resumen (Estado: Pendiente)
* **Innovación:** Al confirmar la comanda, el sistema **guarda inmediatamente** el registro en la tabla `pedido` con el flag `procesado = FALSE`.
* Esto asegura que, ante un cierre inesperado del navegador o fallo eléctrico, la mesa y su pedido siguen registrados en el sistema, accesibles desde el panel de resumen (`resumenPedido.php`).

### 3. Confirmación y Check-out (Lógica Booleana)
* El sistema presenta un resumen detallado para validación.
* **Procesamiento por Lotes:** Mediante una interfaz de *checkboxes*, el gerente puede seleccionar múltiples pedidos pendientes y confirmarlos simultáneamente.
* Al confirmar el pago, el sistema actualiza el estado del pedido (Trigger lógico) y habilita la emisión de documentos.

### 4. Facturación y Exportación PDF
* Solo los pedidos validados generan registros en la tabla `factura`.
* Generación automática de documentos PDF profesionales listos para impresión o envío digital al cliente.

---

## 🗃️ Arquitectura de Base de Datos

El proyecto se sustenta sobre un modelo relacional normalizado (MySQL) que incluye gestión de herencia de identidades y trazabilidad de productos.

### Entidades Principales:
| Tabla | Descripción |
| :--- | :--- |
| **`pedido`** | Núcleo transaccional. Incluye columna `procesado` (BOOLEAN) para gestión de estados. |
| **`factura`** | Registro fiscal inalterable, vinculado a pedidos confirmados. |
| **`plato` / `producto`** | Gestión de menú y desglose de ingredientes (`tipo: ingrediente/otros`). |
| **`persona`** | Super-entidad que gestiona identidades únicas para `usuario`, `empleado` y `cliente`. |
| **`campania`** | Módulo de Marketing para gestión de promociones y fidelización. |

---

## 📂 Estructura del Repositorio

```text
/
├── formulariocomandas.php    # Frontend: Selección de platos (Carta digital)
├── resumenPedido.php         # Logic: Recepción de comanda y persistencia SQL
├── Formulario_Facturacion.php# Logic: Cierre de venta y generación de factura
├── formularioCompras.php     # Backoffice: Gestión de stock y proveedores
├── estilo.css                # UI: Estilos corporativos y diseño responsive
└── db/
    └── base_datos_restaurante.txt  # Script SQL de creación de tablas
