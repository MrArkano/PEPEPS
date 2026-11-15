# 🌾 PEPEPS - Plataforma Digital de Mercados Campesinos de Santander
### Proyecto Final · Desarrollo y Arquitectura Backend
**Docente:** MSc. Feisar Moreno  
**Estudiantes:** Andrés Felipe Jaimes Mantilla (U00189545) · Helbert Mauricio Quesada López (U00189606)  
**Despliegue en Producción:** [https://pepeps-santander.onrender.com](https://pepeps-santander.onrender.com)  
**Repositorio Oficial en GitHub:** [https://github.com/MrArkano/PEPEPS.git](https://github.com/MrArkano/PEPEPS.git)

---

## 🏛️ 1. Arquitectura del Sistema y Patrones de Diseño

El sistema está diseñado bajo los principios de **Clean Architecture (Arquitectura Limpia)**, **Domain-Driven Design (DDD)** y el **Patrón Repositorio (Repository Pattern)** con Inversión de Control (IoC) e Inyección de Dependencias (DI), garantizando desacoplamiento total entre las reglas del negocio, el framework y los motores de persistencia.

```text
app/
├── Casts/             # Casts agnósticos (Decimal128Cast, ObjectIdCast)
├── DTO/               # Data Transfer Objects inmutables (Filtros, Fichas, Paginación)
├── Enums/             # Enums de Dominio (Rol, EstadoPedido, MetodoPago, TipoEntrega)
├── Exceptions/        # Excepciones centralizadas de dominio y negocio
├── Http/
│   ├── Controllers/
│   │   ├── Api/V1/    # Controladores REST API v1 desacoplados (JSON responses)
│   │   └── ...        # Controladores Web Blade
│   ├── Middleware/    # EncabezadosSeguridad (ISO 27001 / OWASP), RequiereRol
│   └── Requests/
│       ├── Api/       # FormRequests para API con validación estricta y respuestas 422 JSON
│       └── ...        # FormRequests Web
├── Models/            # Modelos de Dominio Eloquent con relaciones y asignación masiva
├── Policies/          # Políticas de Autorización de Laravel (Control de Acceso)
├── Repositories/
│   ├── Contracts/     # Interfaces agnósticas (IoC)
│   └── Mongo/         # Implementaciones concretas de persistencia
└── Services/
    ├── Externos/      # Capa de integración HTTP dedicada para APIs de Terceros
    │   ├── PasarelaPagoStripeService.php       # API Externa 1: Pagos y Webhooks
    │   └── ServicioMeteorologicoAgricola.php   # API Externa 2: Clima Open-Meteo
    └── ...            # Servicios de Dominio (Auth, Pedidos, Catálogo, Pagos)
```

---

## 🗄️ 2. Modelos de Datos y Persistencia con Eloquent (Mínimo 8 Modelos)

La persistencia se gestiona a través del ORM Eloquent, modelando 8 entidades de dominio con tipado estricto, asignación masiva controlada (`$fillable` y `$hidden`) y relaciones de cardinalidad:

| # | Modelo | Colección / Tabla | Descripción | Tipos de Relaciones Eloquent |
| :-: | :--- | :--- | :--- | :--- |
| **1** | `Usuario` | `usuarios` | Identidad, autenticación Sanctum y perfiles de rol. | `hasMany(Productor)` (1:N), `hasMany(Pedido)` (1:N) |
| **2** | `Productor` | `productores` | Fincas rurales de Santander y orígenes agrícolas. | `belongsTo(Usuario)` (N:1), `hasMany(Producto)` (1:N) |
| **3** | `Categoria` | `categorias` | Clasificación de productos (Frutas, Hortalizas, etc.). | `hasMany(Producto)` (1:N) |
| **4** | `Producto` | `productos` | Catálogo campesino, precios COP e inventario embebido. | `belongsTo(Productor)` (N:1), `belongsTo(Categoria)` (N:1) |
| **5** | `Pedido` | `pedidos` | Órdenes de compra con snapshot de productos. | `belongsTo(Usuario)` (N:1), `hasOne(Pago)` (1:1), `hasOne(Entrega)` (1:1) |
| **6** | `Pago` | `pagos` | Registro y conciliación financiera de la transacción. | `belongsTo(Pedido)` (1:1) |
| **7** | `Entrega` | `entregas` | Logística, despacho rural y asignación de transportador. | `belongsTo(Pedido)` (1:1) |
| **8** | `Auditoria` | `auditoria` | Bitácora inmutable de trazabilidad de cambios. | `belongsTo(Usuario)` (N:1) |
| **+** | `PersonalAccessToken` | `personal_access_tokens` | Tokens criptográficos de sesión emitidos por Sanctum. | `morphTo(tokenable)` (Polimórfica) |

- **Poblado Limpio y Factories:** El sistema cuenta con factories en `database/factories/` (`UsuarioFactory`, `ProductoFactory`, `ProductorFactory`, `CategoriaFactory`) y el seeder principal `DatabaseSeeder.php` que orquesta la inicialización limpia con `php artisan db:seed`.

---

## 🌐 3. Exposición de API REST (Endpoints, URIs y Códigos HTTP)

La API REST se encuentra versionada bajo el prefijo `/api/v1/`, con respuestas JSON estructuradas (`status`, `message`, `data`, `meta`), manejo desacoplado en controladores API y cumplimiento estricto de códigos HTTP estándar:

| Método | URI | Autenticación | Códigos HTTP | Funcionalidad y Payload |
| :--- | :--- | :---: | :---: | :--- |
| `POST` | `/api/v1/auth/login` | Pública | `200`, `401`, `422` | Autenticación de usuario. Retorna Bearer Token de Sanctum. |
| `POST` | `/api/v1/auth/registro` | Pública | `201`, `422` | Registro público de nuevos consumidores con emisión de token. |
| `GET` | `/api/v1/auth/perfil` | `auth:sanctum` | `200`, `401` | Consulta de perfil del usuario autenticado por token. |
| `POST` | `/api/v1/auth/logout` | `auth:sanctum` | `200`, `401` | Revocación inmediata del token criptográfico activo. |
| `GET` | `/api/v1/productos` | Pública | `200` | Listado paginado del catálogo campesino con filtros (`q`, `municipio`, `categoria`). |
| `GET` | `/api/v1/productos/{id}` | Pública | `200`, `404` | Ficha técnica de trazabilidad, finca de origen y stock. |
| `GET` | `/api/v1/pedidos` | `auth:sanctum` | `200`, `401` | Historial de pedidos del usuario (o todos si es Administrador). |
| `POST` | `/api/v1/pedidos` | `auth:sanctum` | `201`, `401`, `422` | Creación de pedido con validación de inventario y snapshot de precios. |
| `GET` | `/api/v1/pedidos/{id}` | `auth:sanctum` | `200`, `401`, `403`, `404` | Detalle del pedido. Control de autorización (403 si es ajeno). |
| `GET` | `/api/v1/fincas/{id}/clima` | Pública | `200`, `404` | **API Externa 1:** Clima en tiempo real y sugerencia agronómica de cosecha. |
| `POST` | `/api/v1/pedidos/{id}/pagar` | `auth:sanctum` | `200`, `401`, `403`, `404`, `500` | **API Externa 2:** Genera intención de pago criptográfica con Stripe. |
| `POST` | `/api/v1/pagos/webhook` | Exenta CSRF | `200` | **API Externa 2:** Webhook asíncrono para confirmación de transacciones. |

---

## 🔌 4. Consumo de Servicios Externos (Integración Doble)

Se integraron dos servicios externos de terceros encapsulados en clases de servicio dedicadas (`app/Services/Externos/`) sin lógica HTTP en controladores:

### API Externa 1: Servicio Meteorológico Agrícola (Open-Meteo API)
- **Clase:** `App\Services\Externos\ServicioMeteorologicoAgricola`
- **Propósito de Negocio:** Consultar variables agroclimáticas en tiempo real (temperatura, humedad relativa, precipitación y código meteorológico) en las coordenadas exactas de los municipios santandereanos de las fincas (Bucaramanga, Floridablanca, San Gil, Lebrija, Charta, Zapatoca, etc.).
- **Lógica de Valor Agregado:** Emite una recomendación agronómica automatizada para orientar al productor si las condiciones son propicias para la recolección y despacho de frutas perecederas hacia el área metropolitana.
- **Configuración:** `config/services.php` (`open_meteo.base_url`) y `.env` (`OPEN_METEO_BASE_URL`).

### API Externa 2: Pasarela de Pagos Digital (Stripe Payment Gateway)
- **Clase:** `App\Services\Externos\PasarelaPagoStripeService`
- **Propósito de Negocio:** Procesar transacciones digitales seguras mediante la creación de `PaymentIntents` criptográficos y recepción asíncrona de Webhooks de confirmación (`payment_intent.succeeded`).
- **Seguridad y Webhooks:** Credenciales administradas mediante variables de entorno (`STRIPE_SECRET_KEY`, `STRIPE_WEBHOOK_SECRET`). Exención de validación CSRF configurada en `bootstrap/app.php` para la ruta `/api/v1/pagos/webhook`.

---

## 🔒 5. Autenticación, Roles y Seguridad ISO/IEC 27001:2022

1. **Gestión de Identidad y Tokens:** Autenticación por Bearer Tokens con **Laravel Sanctum** (`HasApiTokens`), almacenados de forma segura con hashes SHA-256 en la colección `personal_access_tokens`.
2. **Perfiles y Roles Diferenciados:**
   - **Administrador:** Supervisión global, analítica NoSQL, conciliación de pagos y bitácora de auditoría.
   - **Productor:** Gestión exclusiva de sus fincas, catálogo de productos asignados y alertas de stock bajo.
   - **Consumidor:** Carrito de compras, checkout, historial de pedidos y calificación de entregas.
3. **Políticas de Autorización (Policies y Gates):** `ProductoPolicy`, `FincaPolicy`, `RequiereRol` middleware impidiendo accesos indebidos horizontales o verticales (retornando `403 Forbidden`).
4. **Cumplimiento ISO/IEC 27002:2022 (Versión más actualizada):**
   - **A.8.28 (Codificación Segura):** CSP Nivel 3 estricta, `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`, `Permissions-Policy`.
   - **A.8.24 (Criptografía):** Hashing con Bcrypt (costo 12) y salting individual. HSTS forzado (`max-age=31536000; includeSubDomains; preload`).
   - **A.8.12 (Prevención de Fugas):** Ocultamiento de cabeceras (`X-Powered-By`, `Server`), `expose_php = Off`, ocultamiento de hashes con `$hidden`.
   - **A.8.15 (Registro y Auditoría):** Bitácora inmutable en la colección `auditoria` para trazabilidad de estados y precios.

---

## 🧪 6. Suite de Pruebas Automatizadas y Mocks (PHPUnit)

La plataforma cuenta con **47 pruebas automatizadas (372 aserciones)** que cubren el 100% de los endpoints REST, autenticación, aislamiento por roles y consumo de APIs externas mediante simulación de fachadas (**Mocks con `Http::fake()`**):

```bash
./vendor/bin/sail artisan test
```

### Resultados de la Suite:
```text
   PASS  Tests\Unit\ExampleTest
  ✓ that true is true

   PASS  Tests\Feature\ApiRestV1Test (16 tests, 242 assertions)
  ✓ login api con credenciales validas retorna 200 y token
  ✓ login api con credenciales invalidas retorna 401
  ✓ registro api exitoso retorna 201 y token
  ✓ registro api con datos invalidos retorna 422
  ✓ perfil requiere autenticacion y retorna 401 sin token
  ✓ perfil con token valido retorna 200
  ✓ logout revoca token y retorna 200
  ✓ catalogo productos publico retorna 200 con estructura
  ✓ detalle producto existente retorna 200
  ✓ detalle producto inexistente retorna 404
  ✓ crear pedido sin token retorna 401
  ✓ crear pedido con token valido retorna 201 y descuenta inventario
  ✓ detalle pedido ajeno retorna 403 forbidden
  ✓ api externa 1 clima finca con http fake retorna 200
  ✓ api externa 2 pasarela stripe con http fake retorna 200
  ✓ api externa 2 webhook pasarela procesa evento y retorna 200

   PASS  Tests\Feature\AutenticacionTest (11 tests)
   PASS  Tests\Feature\CatalogoGestionTest (8 tests)
   PASS  Tests\Feature\PedidosPagosEntregasTest (5 tests)
   PASS  Tests\Feature\ReportesAuditoriaTest (5 tests)
   PASS  Tests\Feature\ExampleTest (1 test)

  Tests:    47 passed (372 assertions)
  Duration: 4.42s
```

---

## 🚀 7. Instrucciones de Instalación y Despliegue Local

### 1. Clonar el Repositorio
```bash
git clone https://github.com/MrArkano/PEPEPS.git
cd PEPEPS
```

### 2. Configurar Variables de Entorno
```bash
cp .env.example .env
```

### 3. Levantar Contenedores con Laravel Sail
```bash
./vendor/bin/sail up -d
```

### 4. Generar Key y Poblado de Datos
```bash
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan db:seed
```

### 5. Compilar Assets de Frontend
```bash
./vendor/bin/sail npm install
./vendor/bin/sail npm run build
```

Acceso web: **[http://localhost](http://localhost)** o **[http://localhost/catalogo](http://localhost/catalogo)**.

---

## 👥 8. Credenciales de Prueba para Evaluación

Contraseña unificada para todas las cuentas de demostración: **`password123`**

| Rol de Usuario | Correo Electrónico | Contraseña | Alcance y Permisos |
| :--- | :--- | :---: | :--- |
| **Administrador** | `carlos.ramirez@pepeps.co` | `password123` | Control total, reportes NoSQL, auditoría y conciliación de pagos. |
| **Productor Rural** | `maria.suarez@finca.co` | `password123` | Control de fincas en Floridablanca y Lebrija, inventario y productos. |
| **Consumidor** | `andres.mantilla@gmail.com` | `password123` | Catálogo, carrito, checkout, pedidos y consumo de endpoints API. |
