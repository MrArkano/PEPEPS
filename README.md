# 🌾 PEPEPS - Plataforma Digital de Mercados Campesinos de Santander

Plataforma web desarrollada con **Laravel 13**, **PHP 8.5/8.2** y **MongoDB**, orientada a conectar a los productores rurales de Santander (Bucaramanga, Floridablanca, Girón, San Gil, Piedecuesta, Lebrija, etc.) con consumidores urbanos, reduciendo la intermediación comercial, garantizando precios justos y trazabilidad de origen campesino verificado.

---

## 🛠️ Tecnologías Utilizadas

- **Framework Backend:** Laravel 13 (Arquitectura Limpia y Patrón Repositorio / Inversión de Dependencias).
- **Lenguaje:** PHP 8.2 / 8.5 (con extensión nativa C `mongodb` y `bcmath` para cálculos de precisión monetaria Decimal128).
- **Base de Datos NoSQL de Dominio:** MongoDB 8.0 (colecciones de usuarios, productores, categorías, productos, pedidos, pagos, entregas y auditoría).
- **Driver / ODM NoSQL:** Paquete oficial `mongodb/laravel-mongodb` v5.12.
- **Frontend & UI/UX:** Blade Templates + **Tailwind CSS v4** (diseño optimizado para dispositivos móviles, fuentes nativas del sistema, microinteracciones y estándares WCAG 2.1 AA).
- **Contenerización y Despliegue:** Docker, Docker Compose (Laravel Sail), Dockerfile Multi-Stage de producción y configuración para Netlify Edge / Railway / Render.

---

## 📋 Requisitos Previos

- **Docker Desktop** (en Windows/macOS) o **Docker Engine + Docker Compose** (en Linux / WSL2).
- **Git** instalado.

---

## 🚀 Opción A: Ejecución Local con Docker (Laravel Sail)

### 1. Clonar el Repositorio
```bash
git clone https://github.com/MrArkano/Andres.git
cd Andres
```

### 2. Configurar el Archivo de Entorno
Copia el archivo de ejemplo:
```bash
cp .env.example .env
```

### 3. Iniciar los Contenedores con Laravel Sail
```bash
./vendor/bin/sail up -d
```

### 4. Generar la Clave de Aplicación y Migraciones Auxiliares
```bash
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate --force
```

### 5. Cargar la Base de Datos Inicial de MongoDB (Dataset PEPEPS)
El script oficial con las 7 colecciones y 10 registros por colección se encuentra en `database/mongo/SCRIPT_PROYECTO_PEPEPS.js`. Cárgalo dentro del contenedor de Mongo:
```bash
docker exec -i $(docker compose ps -q mongo) mongosh -u root -p secret_mongo_root --authenticationDatabase admin < database/mongo/SCRIPT_PROYECTO_PEPEPS.js
```

### 6. Ejecutar el Seeder de Contraseñas
Los datos de prueba del script vienen con hashes de marcador (`hash_admin_001...`). Ejecuta el seeder para activar el inicio de sesión con `password123`:
```bash
./vendor/bin/sail artisan db:seed --class=UsuariosPasswordSeeder --force
```

### 7. Compilar los Assets del Frontend
```bash
./vendor/bin/sail npm install
./vendor/bin/sail npm run build
```

¡Listo! Abre en tu navegador web: **[http://localhost](http://localhost)** o **[http://localhost/catalogo](http://localhost/catalogo)**.

---

## ☁️ Opción B: Conexión con MongoDB en la Nube (MongoDB Atlas)

Para conectar la aplicación a un cluster de MongoDB en la nube:

### 1. Crear Cluster Gratuito en MongoDB Atlas
1. Ingresa a [MongoDB Atlas](https://www.mongodb.com/cloud/atlas) y crea una cuenta gratuita.
2. Crea una base de datos gratuita (**M0 Free Tier**).
3. En **Security > Database Access**, crea un usuario (ejemplo: `pepeps_user`) con rol `readWriteAnyDatabase` o acceso a la base de datos `pepeps`.
4. En **Security > Network Access**, agrega la IP `0.0.0.0/0` (permitir acceso desde cualquier lugar, necesario para plataformas de nube como Railway o Render).

### 2. Configurar la Cadena de Conexión en `.env`
En tu archivo `.env`, reemplaza la configuración de MongoDB con la URI de Atlas:
```env
MONGODB_DATABASE=pepeps
MONGODB_URI="mongodb+srv://pepeps_user:TU_PASSWORD_AQUI@cluster0.xxxx.mongodb.net/pepeps?retryWrites=true&w=majority&appName=pepeps"
```

### 3. Cargar el Dataset Oficial en MongoDB Atlas
Ejecuta el script oficial directamente contra tu cluster en la nube usando `mongosh`:
```bash
mongosh "mongodb+srv://cluster0.xxxx.mongodb.net/pepeps" --username pepeps_user < database/mongo/SCRIPT_PROYECTO_PEPEPS.js
```

Luego, activa las contraseñas de los usuarios de prueba:
```bash
php artisan db:seed --class=UsuariosPasswordSeeder --force
```

---

## 🌐 Opción C: Despliegue en Producción Cloud (Railway / Render + Netlify)

> [!NOTE]
> **Arquitectura Cloud para Laravel + MongoDB:**
> Laravel es una aplicación dinámica PHP que requiere la extensión binaria nativa C de MongoDB (`mongodb.so`). Netlify es una plataforma CDN/Edge orientada a Jamstack estático. La solución estándar en la industria consiste en desplegar el backend contenerizado en una plataforma con soporte Docker (como **Railway** o **Render**) y, si se requiere un dominio Netlify, utilizar **Netlify como Reverse Proxy / CDN** mediante el archivo `netlify.toml` incluido en el repositorio.

### Paso 1: Desplegar el Contenedor en Railway o Render
1. Crea una cuenta gratuita en [Railway.app](https://railway.app) o [Render.com](https://render.com).
2. Selecciona **New Project > Deploy from GitHub repo** y elige `MrArkano/Andres`.
3. La plataforma detectará automáticamente el archivo `Dockerfile` multi-stage incluido en la raíz.
4. En la pestaña de variables de entorno (**Variables**), configura:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   APP_KEY=base64:TU_APP_KEY_AQUI
   APP_URL=https://tu-proyecto.up.railway.app
   MONGODB_URI=mongodb+srv://pepeps_user:PASSWORD@cluster0.xxxx.mongodb.net/pepeps?retryWrites=true&w=majority
   MONGODB_DATABASE=pepeps
   SESSION_DRIVER=file
   CACHE_STORE=file
   SESSION_SECURE_COOKIE=true
   ```
5. ¡Tu backend Laravel + MongoDB estará en línea con HTTPS automático!

### Paso 2: Conectar con Netlify (Reverse Proxy)
Si deseas que tu proyecto se sirva a través de una URL de Netlify (`*.netlify.app`):
1. Abre el archivo `netlify.toml` en este repositorio.
2. Reemplaza la URL `https://TU-BACKEND-EN-RAILWAY.up.railway.app/:splat` por la URL generada por Railway o Render.
3. En [Netlify](https://www.netlify.com), haz clic en **Add new site > Import an existing project**, conecta el repositorio `MrArkano/Andres` y haz clic en **Deploy**.
4. Netlify recibirá las solicitudes en sus servidores perimetrales y las enviará de forma transparente y con SSL a tu aplicación Laravel.

---

## 👥 Usuarios de Prueba para Evaluación

Todos los usuarios tienen la contraseña configurada como: **`password123`**

| Rol | Correo Electrónico | Contraseña | Alcance y Funcionalidades |
| :--- | :--- | :--- | :--- |
| **Administrador** | `carlos.ramirez@pepeps.co` | `password123` | Supervisión global, catálogo total, conciliación de pagos, asignación logística y bitácora de auditoría. |
| **Administrador** | `diana.pabon@pepeps.co` | `password123` | Analítica, reportes y métricas de plataforma. |
| **Productor Rural** | `jose.gomez@finca.co` | `password123` | Control de sus 3 fincas (*Finca Los Naranjos, El Mirador, Las Delicias*), inventarios y pedidos asociados. |
| **Productor Rural** | `maria.suarez@finca.co` | `password123` | Control de fincas *La Esperanza, Santa Rita y La Primavera*. |
| **Consumidor** | `andres.mantilla@gmail.com` | `password123` | Carrito de compras, checkout, historial de pedidos, pagos y calificación de productos. |
| **Consumidor** | `laura.vargas@gmail.com` | `password123` | Compras y seguimiento de entregas a domicilio. |

---

## 🧪 Pruebas Automatizadas (Testing Suite)

La plataforma cuenta con una suite completa de **31 pruebas automatizadas** que cubren autenticación, aislamiento de ofertas por rol, flujo de compra, control de inventario, consultas analíticas y bitácora de auditoría.

Para ejecutar los tests:
```bash
./vendor/bin/sail test
```
Salida esperada:
```text
PASS Tests\Feature\AutenticacionTest (11 tests)
PASS Tests\Feature\CatalogoGestionTest (8 tests)
PASS Tests\Feature\PedidosPagosEntregasTest (5 tests)
PASS Tests\Feature\ReportesAuditoriaTest (5 tests)
PASS Tests\Feature\ExampleTest (1 test)
PASS Tests\Unit\ExampleTest (1 test)

Tests: 31 passed (130 assertions)
```

---

## 📊 Módulo de Reportes y Analítica NoSQL

Ubicado en la ruta `/reportes`, ejecuta en tiempo real los pipelines de agregación de `CONSULTAS_PEPEPS.js`:

1. **Consulta 1:** *Productos más vendidos por municipio del productor* (excluyendo pedidos cancelados con `$match: { estado: { $ne: "cancelado" } }`).
2. **Consulta 2:** *Productores con pedidos pendientes* para priorizar cosecha y despacho.
3. **Consulta 3:** *Historial cronológico de compras por consumidor*.
4. **Consulta 4:** *Comportamiento financiero de pagos por método y estado* (agrupación monetaria con `$toDecimal`).
5. **Consulta 5:** *Productos agotados o con existencias bajas* ($\le 10$ unidades) por productor.

---

## 🛡️ Bitácora de Auditoría Básica

Ubicada en `/gestion/auditoria` (acceso exclusivo Administrador), registra en la colección `auditoria` de MongoDB:
- Fecha y hora exacta de cada operación.
- Usuario responsable (`usuario_id`, `nombre_usuario`).
- Entidad afectada (`pedidos`, `productos`).
- Acción realizada (`actualizacion_estado`, `cambio_precio`, `cambio_inventario`).
- Campo modificado, valor anterior y valor nuevo.

---

## 🏗️ Arquitectura del Software

```text
app/
├── Casts/             # Casts BSON personalizados (Decimal128Cast, ObjectIdCast)
├── Enums/             # Enums de Dominio (Rol, EstadoPedido, MetodoPago, etc.)
├── Exceptions/        # Excepciones centralizadas de dominio y negocio
├── Http/
│   ├── Controllers/   # Controladores delegados sin lógica de negocio
│   ├── Middleware/    # RequiereRol, EncabezadosSeguridad (CSP estricta, OWASP)
│   └── Requests/      # Form Requests (validación de entradas en el borde)
├── Models/            # Modelos MongoDB Eloquent (Usuario, Producto, Pedido, etc.)
├── Policies/          # Políticas de autorización y aislamiento de ofertas
├── Repositories/      # Contratos agnósticos e implementaciones MongoDB
└── Services/          # Servicios de Dominio (Auth, Catálogo, Pedidos, Pagos, etc.)
```

---

## 📄 Autores

Proyecto desarrollado para la cátedra de **Base de Datos II (NoSQL)**:
- **Helbert Mauricio Quesada López** (U00189606) - Administrador del Proyecto
- **Andrés Felipe Jaimes Mantilla** (U00189545) - Ingeniero de Desarrollo
