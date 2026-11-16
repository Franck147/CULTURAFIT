# 🏋️‍♂️ CULTURA FIT — Sistema Integral de Gestión para Gimnasios & Centros Fitness

Sistema moderno, robusto y seguro de gestión de gimnasios desarrollado en **Laravel 10**, **PostgreSQL**, **Livewire 3**, **Blade** y **Bootstrap 5.3**, migrado y optimizado desde la versión legacy de PHP para ofrecer máxima escalabilidad, integridad transaccional y seguridad de grado empresarial.

---

## 🚀 1. Tecnologías del Stack

| Capa | Tecnología | Descripción |
| :--- | :--- | :--- |
| **Backend Framework** | **Laravel 10.x** (PHP 8.1+) | Arquitectura MVC, Service Layer, Eloquent ORM, Transactions & Row Locking. |
| **Base de Datos** | **PostgreSQL 13+** | Motor relacional robusto con Check Constraints, Foreign Keys en Cascada e Índices optimizados. |
| **Frontend Dinámico** | **Livewire 3.8 + Blade** | Componentes reactivos en tiempo real sin recarga de página ni complejidad de SPAs pesadas. |
| **Diseño & UI/UX** | **Bootstrap 5.3 + FontAwesome 6** | Interfaz responsive adaptada a computadoras de escritorio, tablets y pantallas táctiles de POS. |
| **Visualización** | **Chart.js 4.x** | Gráficos interactivos de evolución antropométrica (peso/grasa/cintura) e ingresos financieros. |
| **Notificaciones** | **SweetAlert2** | Diálogos de confirmación y alertas visuales elegantes para anulaciones y cierres de turno. |

---

## 🔒 2. Mejoras Críticas de Seguridad y Auditoría Implementadas

En esta migración se resolvieron de raíz las **18 vulnerabilidades críticas** identificadas en el sistema anterior:

1. **Anti-RCE en Carga de Archivos:** Las fotos de socios y productos ahora se validan estrictamente por tipo MIME real (`mimes:jpeg,png,jpg,webp`), peso máximo (`max:2048`), y se renombran criptográficamente (`socio_173169...hash.jpg`), eliminando por completo la posibilidad de inyectar archivos `.php` maliciosos.
2. **Protección Total contra Inyecciones SQL:** 100% de consultas migradas a **Eloquent ORM** y sentencias preparadas PDO.
3. **Control de Concurrencia e Integridad de Stock:** Las ventas del POS se ejecutan dentro de `DB::transaction()` con bloqueo de fila `lockForUpdate()`, garantizando que nunca se produzca stock negativo por compras simultáneas.
4. **Arqueo y Cierre de Caja Recalculado en Servidor:** El saldo esperado en efectivo se calcula de forma inmutable en el backend (`Fondo Inicial + Ventas Efectivo - Gastos`), evitando manipulaciones por inspectores de navegador.
5. **Autenticación con Bcrypt:** Contraseñas hasheadas de forma segura con `Hash::make()`.
6. **Protección CSRF en todos los Formularios:** Inclusión de tokens `@csrf` y meta-tags para peticiones AJAX.
7. **Control de Acceso Basado en Roles (RBAC):** Middleware `CheckRole` estricto en el Kernel de rutas (`admin`, `cajero`).

---

## 📊 3. Módulos del Sistema

### 1. Panel de Control (Dashboard)
- KPIs en tiempo real: Socios activos, Facturación del mes, Gastos operativos y Utilidad neta.
- Gráficos interactivos de tendencia de ventas (últimos 6 meses) y Top 5 de productos más vendidos.
- Alertas de inventario crítico (productos con stock $\le 10$ unidades).
- Historial de las 5 ventas más recientes con acceso rápido a comprobantes.

### 2. Control de Caja & Arqueo
- **Apertura de Turno:** Registro del fondo inicial en efectivo antes de permitir ventas.
- **Cierre y Arqueo:** Desglose automático de ventas en efectivo vs. medios digitales (tarjeta, transferencia) vs. gastos operativos.
- Cálculo de discrepancias en tiempo real: Identificación instantánea de cuadre exacto, sobrante o faltante.
- Historial detallado de todos los turnos anteriores con cajero responsable.

### 3. Punto de Venta (POS)
- Búsqueda en tiempo real por nombre de producto o escaneo de código de barras.
- Filtro dinámico por categorías (Suplementos, Ropa, Bebidas, etc.).
- Carrito interactivo con control estricto de existencias, cálculo de descuentos y selección de cliente/socio.
- Calculadora de cambio/vuelto en ventanilla para pagos en efectivo.
- Emisión inmediata de comprobantes térmicos de 80mm listos para imprimir (`window.print()`).
- Historial de ventas con filtros de fecha, búsqueda y modal de detalle vía AJAX.

### 4. Socios & Clientes
- Directorio de socios con foto de perfil, DNI, teléfono, email y estado de membresía.
- Control de estados: *Activo*, *Inactivo*, *Pendiente*.
- Subida segura de fotografías con vista previa en vivo.

### 5. Progreso Corporal & Rutinas
- **Evolución Física:** Registro de peso corporal (kg), porcentaje de grasa (%), perímetro de cintura (cm) y brazo (cm).
- Gráficos interactivos multieje con Chart.js para visualizar el progreso a lo largo de las semanas.
- **Plan Semanal:** Asignación de rutinas de entrenamiento personalizadas para los 6 días de la semana con recomendaciones nutricionales.

### 6. Inventario & Categorías
- Catálogo de productos con código SKU, precios de compra/costo y precios de venta al público.
- Cálculo de existencias y advertencias de bajo stock.
- Modal de **Ajuste Rápido de Stock** (entradas por compra o salidas por mermas/ajustes).
- Gestión de categorías de productos con conteo automático de ítems asociados.

### 7. Gastos y Egresos Operativos
- Registro de salidas de dinero asociadas al turno de caja abierto o generales.
- Flujo de anulación de gastos con registro obligatorio del motivo de cancelación.

### 8. Reportes Financieros & Auditoría Ejecutiva
- **Filtros Avanzados:** Filtrado multidimensional por rango de fechas (con accesos directos: *Hoy, Ayer, Esta Semana, Este Mes, Mes Anterior, Año Actual*), por **Cajero Responsable** específico y por **Método de Pago** (*Efectivo, Tarjeta, Transferencia*).
- **Detalle de Ventas por Cajero:** Listado exhaustivo de transacciones que muestra el nombre del cajero, avatar, rol, socio/cliente, cantidad de artículos, descuentos y total cobrado, con modal para ver el detalle de productos y enlace directo a ticket térmico.
- **Auditoría de Rendimiento por Cajero:** Tabla comparativa que consolida el total recaudado por cada usuario, cantidad de órdenes atendidas, ticket promedio y porcentaje de participación sobre los ingresos globales.
- **Exportación a Excel (CSV con UTF-8 BOM):** Descarga instantánea compatible 100% con Microsoft Excel que incluye fórmulas, datos de cajero y desglose de artículos.
- **Modo de Impresión Ejecutivo:** Diseño limpio para imprimir balances contables formales con membrete del gimnasio.

### 9. Usuarios & Seguridad
- Creación y edición de cuentas de usuario con roles asignados.
- Protección de auto-desactivación para evitar que el superadministrador se bloquee a sí mismo.

### 10. Configuración & Mantenimiento
- Parámetros generales: Nombre comercial del gimnasio, RUC, logo, dirección, teléfono y símbolo de moneda.
- Módulo de **Copias de Seguridad (Backups) de PostgreSQL**: Generación con 1 clic de volcados `.sql` nativos mediante `pg_dump`, descarga directa de respaldos al equipo local, listado histórico con peso y fecha, y guías de restauración asistida.

---

## 🛠️ 4. Requisitos del Sistema

- **PHP:** 8.1 o superior (con extensiones `pdo_pgsql`, `pgsql`, `mbstring`, `openssl`, `curl` activas).
- **PostgreSQL:** Versión 13, 14, 15 o 16.
- **Composer:** 2.x.
- **Servidor Web:** Apache / Nginx (compatible con Laragon, XAMPP o Docker).

---

## 💻 5. Instrucciones de Instalación en Laragon (Paso a Paso)

### Paso 1: Crear la Base de Datos en PostgreSQL
1. Abre tu gestor de base de datos preferido (pgAdmin, DBeaver o la consola `psql`).
2. Crea una nueva base de datos llamada `cultura_fit`:
```sql
CREATE DATABASE cultura_fit WITH ENCODING 'UTF8';
```

### Paso 2: Configurar las Credenciales en `.env`
Abre el archivo `.env` dentro de la carpeta `CULTURA_FIT` y verifica que los datos de conexión coincidan con tu instalación de PostgreSQL:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=cultura_fit
DB_USERNAME=postgres
DB_PASSWORD=tu_password_aqui
```

### Paso 3: Ejecutar las Migraciones y Poblado de Datos (Seeders)
Abre la consola en la carpeta del proyecto y ejecuta:

```bash
php artisan migrate --seed
```

> **Alternativa sin Artisan:** También puedes importar directamente el script SQL puro ubicado en:
> `database/schema_postgresql.sql`
> El cual creará todas las tablas, restricciones, índices y datos iniciales en PostgreSQL.

### Paso 4: Iniciar la Aplicación
Puedes acceder configurando un Virtual Host en Laragon (`cultura_fit.test`) o iniciando el servidor integrado:

```bash
php artisan serve
```
La aplicación estará disponible en: **`http://127.0.0.1:8000`**

---

## 🔑 6. Credenciales de Acceso Iniciales (Demostración)

| Rol | Correo Electrónico | Contraseña | Permisos |
| :--- | :--- | :--- | :--- |
| **Administrador** | `admin@gym.com` | `admin123` | Acceso total a todos los módulos y configuraciones. |
| **Cajero / Asignado** | `cajero@gym.com` | `cajero123` | Control de Turnos, POS, Historial, Socios, Progreso y Gastos. |

---

## 📁 7. Estructura de Directorios del Proyecto

```text
CULTURA_FIT/
├── app/
│   ├── Http/
│   │   ├── Controllers/       # 13 Controladores de negocio optimizados
│   │   └── Middleware/        # Middleware CheckRole para RBAC
│   └── Models/                # 11 Modelos Eloquent con relaciones y casts
├── config/                    # Configuraciones de Laravel (database, auth, etc.)
├── database/
│   ├── migrations/            # 11 Migraciones para PostgreSQL
│   ├── seeders/               # Pobladores con datos de ejemplo
│   └── schema_postgresql.sql  # Script DDL PostgreSQL 100% compatible
├── public/
│   ├── css/gym-theme.css      # Estilos personalizados y paleta gym
│   └── img/                   # Logos, avatares de socios y productos
├── resources/
│   └── views/
│       ├── auth/              # Pantalla de inicio de sesión
│       ├── caja/              # Vistas de apertura y cierre con arqueo
│       ├── dashboard/         # Panel interactivo con gráficos Chart.js
│       ├── gastos/            # Registro y anulación de egresos
│       ├── inventario/        # Productos, stock y categorías
│       ├── layouts/           # Plantilla base responsive con sidebar y topbar
│       ├── pos/               # Terminal POS, historial y tickets 80mm
│       ├── progreso/          # Gráfico de medidas y editor de rutinas
│       ├── reportes/          # Informes y balances financieros
│       ├── socios/            # CRUD de clientes y membresías
│       └── usuarios/          # Control de usuarios y credenciales
└── routes/
    └── web.php                # 55 Rutas organizadas y protegidas por RBAC
```

---
**Desarrollado con altos estándares de calidad, seguridad y rendimiento.**
