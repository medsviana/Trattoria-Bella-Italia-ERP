# 🍝 Trattoria Bella Italia - Sistema de Gestión Empresarial (ERP)

![PHP](https://img.shields.io/badge/PHP-8.0%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-Relational-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Estado](https://img.shields.io/badge/Estado-En_Desarrollo-green)

## 📖 Descripción del Proyecto

**Trattoria Bella Italia** es una solución de software empresarial (ERP) diseñada para la digitalización completa de un restaurante temático italiano. El sistema integra módulos de **gestión de relaciones con clientes (CRM)**, **inventario de proveedores** y **facturación fiscal automatizada**.

El núcleo del proyecto se distingue por su **arquitectura de datos persistente**, diseñada para garantizar la integridad transaccional en entornos de alta concurrencia.

---

## 🔄 Lógica de Negocio y Flujo de Trabajo

El sistema implementa un ciclo de vida de venta basado en estados:

### 1. Toma de Comanda (POS)
* Interfaz ágil (`formulario_comandas.php`) conectada al catálogo de platos.
* Permite la selección dinámica de productos y cantidades.

### 2. Persistencia y Resumen (Estado: Pendiente)
* **Innovación:** Al confirmar la comanda, el sistema **guarda inmediatamente** el registro en la tabla `pedido` con el flag `procesado = FALSE`.
* Esto asegura que la información no se pierda ante cierres inesperados del navegador.

### 3. Confirmación y Check-out
* Panel centralizado (`gestion_pedidos.php`) para validación.
* **Procesamiento por Lotes:** Permite seleccionar múltiples pedidos pendientes y confirmarlos simultáneamente para facturación.

### 4. Facturación y Exportación PDF
* Generación automática de facturas profesionales (`guardar_factura.php` + TCPDF) vinculadas a pedidos confirmados.

### 5. Gestión de Compras
* Flujo de reabastecimiento (`formulario_compras.php`) para gestionar stock con proveedores.

---

## 🗃️ Arquitectura de Base de Datos

| Tabla | Descripción |
| :--- | :--- |
| **`pedido`** | Núcleo transaccional con gestión de estados. |
| **`factura`** | Registro fiscal inalterable. |
| **`plato`** | Gestión de menú y precios. |
| **`persona`** | Super-entidad para `usuario`, `empleado` y `cliente`. |

---

## 📂 Estructura del Proyecto

```text
/
├── 📄 conexion.php               # Configuración de conexión a la base de datos
├── 📄 estilo.css                 # Estilos generales de la interfaz
├── 📄 estiloLista.css            # Estilos específicos para tablas y badges de estado
│
├── 🛒 Módulo de Ventas
│   ├── formulario_comandas.php   # Selección de platos y toma de pedidos
│   └── resumen_pedido.php        # Procesamiento y visualización de la comanda enviada
│
├── 📊 Módulo de Contabilidad
│   ├── gestion_pedidos.php       # Panel de control de pedidos (Pendientes/Confirmados)
│   ├── formulario_factura.php    # Selección de pedidos para facturación
│   └── guardar_factura.php       # Lógica de generación de facturas en formato PDF
│
├── 📦 Módulo de Compras
│   ├── formulario_compras.php    # Gestión de pedidos de productos a proveedores
│   └── resumen_compras.php       # Resumen y cálculo de costes de la compra
│
└── 📁 db/
    └── base_datos_restaurante.txt # Script SQL con la estructura de tablas
