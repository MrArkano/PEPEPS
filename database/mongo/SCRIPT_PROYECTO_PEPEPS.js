// ============================================================================
// PEPEPS - Plataforma Digital de Mercados Campesinos de Santander
// Entrega #3 - Base de Datos II (NoSQL) - MongoDB
// Script de creacion de base de datos, colecciones, indices y datos de prueba
// Ejecutar con: mongosh SCRIPT_PROYECTO_PEPEPS.js
// ============================================================================

// ----------------------------------------------------------------------------
// 1. SELECCION / CREACION DE LA BASE DE DATOS
// ----------------------------------------------------------------------------
print("============================================================");
print(" PEPEPS - Creando / seleccionando base de datos 'pepeps' ");
print("============================================================");

db = db.getSiblingDB("pepeps");

print("-> Base de datos activa: " + db.getName());

// ----------------------------------------------------------------------------
// 2. LIMPIEZA SOLO DE LAS COLECCIONES DEL PROYECTO (si ya existian)
//    No se afecta ninguna otra base de datos ni coleccion ajena al proyecto.
// ----------------------------------------------------------------------------
print("\n------------------------------------------------------------");
print(" Limpiando colecciones previas del proyecto (si existian) ");
print("------------------------------------------------------------");

const coleccionesProyecto = [
  "usuarios",
  "productores",
  "categorias",
  "productos",
  "pedidos",
  "pagos",
  "entregas"
];

coleccionesProyecto.forEach(function (nombreColeccion) {
  if (db.getCollectionNames().indexOf(nombreColeccion) !== -1) {
    db.getCollection(nombreColeccion).drop();
    print("-> Coleccion '" + nombreColeccion + "' eliminada (se recreara).");
  }
});

// ----------------------------------------------------------------------------
// 3. CREACION EXPLICITA DE LAS 7 COLECCIONES PRINCIPALES
// ----------------------------------------------------------------------------
print("\n------------------------------------------------------------");
print(" Creando las 7 colecciones principales del proyecto PEPEPS ");
print("------------------------------------------------------------");

coleccionesProyecto.forEach(function (nombreColeccion) {
  db.createCollection(nombreColeccion);
  print("-> Coleccion creada: " + nombreColeccion);
});

// ----------------------------------------------------------------------------
// 4. DEFINICION PREVIA DE ObjectId PARA MANTENER COHERENCIA REFERENCIAL
// ----------------------------------------------------------------------------

// ---- IDs de usuarios (10 usuarios: administradores, productores, consumidores)
const idUsuarioAdmin1      = new ObjectId();
const idUsuarioAdmin2      = new ObjectId();
const idUsuarioProductor1  = new ObjectId();
const idUsuarioProductor2  = new ObjectId();
const idUsuarioProductor3  = new ObjectId();
const idUsuarioProductor4  = new ObjectId();
const idUsuarioConsumidor1 = new ObjectId();
const idUsuarioConsumidor2 = new ObjectId();
const idUsuarioConsumidor3 = new ObjectId();
const idUsuarioConsumidor4 = new ObjectId();

// ---- IDs de productores (vinculados a usuarios con rol Productor)
const idProductor1 = new ObjectId(); // Usuario Productor1 - Bucaramanga
const idProductor2 = new ObjectId(); // Usuario Productor2 - Floridablanca
const idProductor3 = new ObjectId(); // Usuario Productor3 - Giron
const idProductor4 = new ObjectId(); // Usuario Productor4 - San Gil

// ---- IDs de categorias
const idCategoriaFrutas     = new ObjectId();
const idCategoriaVerduras   = new ObjectId();
const idCategoriaTuberculos = new ObjectId();
const idCategoriaLacteos    = new ObjectId();
const idCategoriaGranosCafe = new ObjectId();
const idCategoriaPanela     = new ObjectId();

// ---- IDs de productos (10 productos)
const idProductoMango     = new ObjectId();
const idProductoMora      = new ObjectId();
const idProductoAguacate  = new ObjectId();
const idProductoTomate    = new ObjectId();
const idProductoCebolla   = new ObjectId();
const idProductoPapa      = new ObjectId();
const idProductoQueso     = new ObjectId();
const idProductoCafe      = new ObjectId();
const idProductoPanela    = new ObjectId();
const idProductoLechuga   = new ObjectId();

// ---- IDs de pedidos (10 pedidos)
const idPedido1  = new ObjectId();
const idPedido2  = new ObjectId();
const idPedido3  = new ObjectId();
const idPedido4  = new ObjectId();
const idPedido5  = new ObjectId();
const idPedido6  = new ObjectId();
const idPedido7  = new ObjectId();
const idPedido8  = new ObjectId();
const idPedido9  = new ObjectId();
const idPedido10 = new ObjectId();

// ----------------------------------------------------------------------------
// 5. INSERCION DE DATOS - COLECCION usuarios (10 documentos)
// ----------------------------------------------------------------------------
print("\n------------------------------------------------------------");
print(" Insertando documentos en 'usuarios' ");
print("------------------------------------------------------------");

db.usuarios.insertMany([
  {
    _id: idUsuarioAdmin1,
    nombre: "Carlos",
    apellido: "Ramirez",
    correo: "carlos.ramirez@pepeps.co",
    telefono: "3101234567",
    contrasena_hash: "hash_admin_001",
    rol: "Administrador",
    estado: "activo",
    direcciones: [
      {
        direccion: "Calle 36 #14-25",
        municipio: "Bucaramanga",
        barrio: "Centro",
        referencia: "Edificio Camara de Comercio"
      }
    ]
  },
  {
    _id: idUsuarioAdmin2,
    nombre: "Diana",
    apellido: "Pabon",
    correo: "diana.pabon@pepeps.co",
    telefono: "3109876543",
    contrasena_hash: "hash_admin_002",
    rol: "Administrador",
    estado: "activo",
    direcciones: [
      {
        direccion: "Carrera 27 #45-10",
        municipio: "Bucaramanga",
        barrio: "Cabecera del Llano",
        referencia: "Cerca al parque"
      }
    ]
  },
  {
    _id: idUsuarioProductor1,
    nombre: "Jose",
    apellido: "Gomez",
    correo: "jose.gomez@finca.co",
    telefono: "3112223344",
    contrasena_hash: "hash_prod_001",
    rol: "Productor",
    estado: "activo",
    direcciones: [
      {
        direccion: "Vereda El Carmen, Km 5",
        municipio: "Bucaramanga",
        barrio: "Zona Rural",
        referencia: "Finca Los Naranjos"
      }
    ]
  },
  {
    _id: idUsuarioProductor2,
    nombre: "Maria",
    apellido: "Suarez",
    correo: "maria.suarez@finca.co",
    telefono: "3123334455",
    contrasena_hash: "hash_prod_002",
    rol: "Productor",
    estado: "activo",
    direcciones: [
      {
        direccion: "Vereda La Esperanza",
        municipio: "Floridablanca",
        barrio: "Zona Rural",
        referencia: "Finca La Esperanza"
      }
    ]
  },
  {
    _id: idUsuarioProductor3,
    nombre: "Pedro",
    apellido: "Lizcano",
    correo: "pedro.lizcano@finca.co",
    telefono: "3134445566",
    contrasena_hash: "hash_prod_003",
    rol: "Productor",
    estado: "activo",
    direcciones: [
      {
        direccion: "Vereda Palogordo",
        municipio: "Giron",
        barrio: "Zona Rural",
        referencia: "Finca El Palmar"
      }
    ]
  },
  {
    _id: idUsuarioProductor4,
    nombre: "Luz",
    apellido: "Camargo",
    correo: "luz.camargo@finca.co",
    telefono: "3145556677",
    contrasena_hash: "hash_prod_004",
    rol: "Productor",
    estado: "activo",
    direcciones: [
      {
        direccion: "Vereda Varguitas",
        municipio: "San Gil",
        barrio: "Zona Rural",
        referencia: "Asociacion Agro San Gil"
      }
    ]
  },
  {
    _id: idUsuarioConsumidor1,
    nombre: "Andres",
    apellido: "Mantilla",
    correo: "andres.mantilla@gmail.com",
    telefono: "3156667788",
    contrasena_hash: "hash_cons_001",
    rol: "Consumidor",
    estado: "activo",
    direcciones: [
      {
        direccion: "Calle 45 #20-30",
        municipio: "Bucaramanga",
        barrio: "Alvarez",
        referencia: "Casa esquinera"
      }
    ]
  },
  {
    _id: idUsuarioConsumidor2,
    nombre: "Laura",
    apellido: "Vargas",
    correo: "laura.vargas@gmail.com",
    telefono: "3167778899",
    contrasena_hash: "hash_cons_002",
    rol: "Consumidor",
    estado: "activo",
    direcciones: [
      {
        direccion: "Carrera 10 #15-40",
        municipio: "Floridablanca",
        barrio: "Caney",
        referencia: "Conjunto Villa Verde"
      }
    ]
  },
  {
    _id: idUsuarioConsumidor3,
    nombre: "Juan",
    apellido: "Rueda",
    correo: "juan.rueda@gmail.com",
    telefono: "3178889900",
    contrasena_hash: "hash_cons_003",
    rol: "Consumidor",
    estado: "activo",
    direcciones: [
      {
        direccion: "Calle 100 #5-12",
        municipio: "Piedecuesta",
        barrio: "Centro",
        referencia: "Frente a la plaza"
      }
    ]
  },
  {
    _id: idUsuarioConsumidor4,
    nombre: "Sofia",
    apellido: "Nino",
    correo: "sofia.nino@gmail.com",
    telefono: "3189990011",
    contrasena_hash: "hash_cons_004",
    rol: "Consumidor",
    estado: "activo",
    direcciones: [
      {
        direccion: "Carrera 8 #22-18",
        municipio: "Lebrija",
        barrio: "El Centro",
        referencia: "Cerca al terminal"
      }
    ]
  }
]);

print("-> 10 documentos insertados en 'usuarios'.");

// ----------------------------------------------------------------------------
// 6. INSERCION DE DATOS - COLECCION productores (10 documentos)
//    usuario_id apunta a usuarios con rol Productor existentes.
// ----------------------------------------------------------------------------
print("\n------------------------------------------------------------");
print(" Insertando documentos en 'productores' ");
print("------------------------------------------------------------");

db.productores.insertMany([
  {
    _id: idProductor1,
    usuario_id: idUsuarioProductor1,
    nombre_finca_asociacion: "Finca Los Naranjos",
    municipio: "Bucaramanga",
    direccion_rural: "Vereda El Carmen, Km 5",
    descripcion: "Produccion de frutas tropicales y citricos de la region.",
    estado_verificacion: "verificado",
    fecha_registro: new Date("2024-02-10")
  },
  {
    _id: idProductor2,
    usuario_id: idUsuarioProductor2,
    nombre_finca_asociacion: "Finca La Esperanza",
    municipio: "Floridablanca",
    direccion_rural: "Vereda La Esperanza",
    descripcion: "Cultivo de hortalizas y verduras organicas.",
    estado_verificacion: "verificado",
    fecha_registro: new Date("2024-03-05")
  },
  {
    _id: idProductor3,
    usuario_id: idUsuarioProductor3,
    nombre_finca_asociacion: "Finca El Palmar",
    municipio: "Giron",
    direccion_rural: "Vereda Palogordo",
    descripcion: "Produccion de tuberculos y productos lacteos artesanales.",
    estado_verificacion: "verificado",
    fecha_registro: new Date("2024-01-20")
  },
  {
    _id: idProductor4,
    usuario_id: idUsuarioProductor4,
    nombre_finca_asociacion: "Asociacion Agro San Gil",
    municipio: "San Gil",
    direccion_rural: "Vereda Varguitas",
    descripcion: "Asociacion de productores de cafe y panela.",
    estado_verificacion: "verificado",
    fecha_registro: new Date("2024-04-15")
  },
  {
    _id: new ObjectId(),
    usuario_id: idUsuarioProductor1,
    nombre_finca_asociacion: "Finca El Mirador",
    municipio: "Piedecuesta",
    direccion_rural: "Vereda Sevilla",
    descripcion: "Cultivo de mora y fresa en clima frio.",
    estado_verificacion: "pendiente",
    fecha_registro: new Date("2024-05-02")
  },
  {
    _id: new ObjectId(),
    usuario_id: idUsuarioProductor2,
    nombre_finca_asociacion: "Finca Santa Rita",
    municipio: "Lebrija",
    direccion_rural: "Vereda La Victoria",
    descripcion: "Produccion de aguacate y citricos de exportacion.",
    estado_verificacion: "verificado",
    fecha_registro: new Date("2024-02-28")
  },
  {
    _id: new ObjectId(),
    usuario_id: idUsuarioProductor3,
    nombre_finca_asociacion: "Finca Buenavista",
    municipio: "Socorro",
    direccion_rural: "Vereda Pescadero",
    descripcion: "Produccion de panela organica y cana de azucar.",
    estado_verificacion: "verificado",
    fecha_registro: new Date("2024-03-18")
  },
  {
    _id: new ObjectId(),
    usuario_id: idUsuarioProductor4,
    nombre_finca_asociacion: "Finca Horizonte Verde",
    municipio: "Barichara",
    direccion_rural: "Vereda Guane",
    descripcion: "Cultivo de tomate y cebolla en invernadero.",
    estado_verificacion: "pendiente",
    fecha_registro: new Date("2024-06-10")
  },
  {
    _id: new ObjectId(),
    usuario_id: idUsuarioProductor1,
    nombre_finca_asociacion: "Finca Las Delicias",
    municipio: "Zapatoca",
    direccion_rural: "Vereda El Carmen Alto",
    descripcion: "Produccion de cafe especial de altura.",
    estado_verificacion: "verificado",
    fecha_registro: new Date("2024-01-08")
  },
  {
    _id: new ObjectId(),
    usuario_id: idUsuarioProductor2,
    nombre_finca_asociacion: "Finca La Primavera",
    municipio: "Charta",
    direccion_rural: "Vereda Centro",
    descripcion: "Produccion lechera y derivados lacteos.",
    estado_verificacion: "verificado",
    fecha_registro: new Date("2024-04-22")
  }
]);

print("-> 10 documentos insertados en 'productores'.");

// ----------------------------------------------------------------------------
// 7. INSERCION DE DATOS - COLECCION categorias (10 documentos)
// ----------------------------------------------------------------------------
print("\n------------------------------------------------------------");
print(" Insertando documentos en 'categorias' ");
print("------------------------------------------------------------");

const idCategoriaCitricos  = new ObjectId();
const idCategoriaHortalizas = new ObjectId();
const idCategoriaOrganicos = new ObjectId();
const idCategoriaCereales  = new ObjectId();

db.categorias.insertMany([
  { _id: idCategoriaFrutas,     nombre: "Frutas",              descripcion: "Frutas frescas de la region santandereana.",        estado: "activa" },
  { _id: idCategoriaVerduras,   nombre: "Verduras",             descripcion: "Verduras y hortalizas frescas.",                    estado: "activa" },
  { _id: idCategoriaTuberculos, nombre: "Tuberculos",           descripcion: "Papa, yuca y otros tuberculos.",                    estado: "activa" },
  { _id: idCategoriaLacteos,    nombre: "Lacteos",              descripcion: "Quesos, leche y derivados lacteos artesanales.",    estado: "activa" },
  { _id: idCategoriaGranosCafe, nombre: "Granos y Cafe",        descripcion: "Cafe, cacao y granos de la region.",                estado: "activa" },
  { _id: idCategoriaPanela,     nombre: "Panela y Derivados",   descripcion: "Panela organica y derivados de la cana.",           estado: "activa" },
  { _id: idCategoriaCitricos,   nombre: "Citricos",             descripcion: "Naranja, mandarina, limon y otros citricos.",       estado: "activa" },
  { _id: idCategoriaHortalizas, nombre: "Hortalizas de Hoja",   descripcion: "Lechuga, espinaca y hortalizas de hoja verde.",     estado: "activa" },
  { _id: idCategoriaOrganicos,  nombre: "Productos Organicos",  descripcion: "Productos certificados organicos.",                estado: "activa" },
  { _id: idCategoriaCereales,   nombre: "Cereales y Granos",    descripcion: "Maiz, frijol y otros granos secos.",                estado: "activa" }
]);

print("-> 10 documentos insertados en 'categorias'.");

// ----------------------------------------------------------------------------
// 8. INSERCION DE DATOS - COLECCION productos (10 documentos)
//    productor_id apunta a productores existentes.
//    categoria_id apunta a categorias existentes.
// ----------------------------------------------------------------------------
print("\n------------------------------------------------------------");
print(" Insertando documentos en 'productos' ");
print("------------------------------------------------------------");

db.productos.insertMany([
  {
    _id: idProductoMango,
    productor_id: idProductor1,
    categoria_id: idCategoriaFrutas,
    nombre: "Mango Tommy",
    descripcion: "Mango Tommy fresco cosechado en finca local.",
    precio: Decimal128("3500"),
    unidad_medida: "kg",
    estado: "activo",
    imagenes: ["mango_01.jpg", "mango_02.jpg"],
    inventario: {
      presentacion: "Bulto x 20 kg",
      cantidad_disponible: 85,
      estado: "disponible",
      fecha_actualizacion: new Date("2026-10-20")
    },
    fecha_creacion: new Date("2024-02-15")
  },
  {
    _id: idProductoMora,
    productor_id: idProductor1,
    categoria_id: idCategoriaFrutas,
    nombre: "Mora de Castilla",
    descripcion: "Mora fresca seleccionada, ideal para jugos y postres.",
    precio: Decimal128("4200"),
    unidad_medida: "kg",
    estado: "activo",
    imagenes: ["mora_01.jpg"],
    inventario: {
      presentacion: "Canastilla x 10 kg",
      cantidad_disponible: 8,
      estado: "disponible",
      fecha_actualizacion: new Date("2026-10-22")
    },
    fecha_creacion: new Date("2024-02-20")
  },
  {
    _id: idProductoAguacate,
    productor_id: idProductor2,
    categoria_id: idCategoriaFrutas,
    nombre: "Aguacate Hass",
    descripcion: "Aguacate Hass de exportacion, punto optimo de maduracion.",
    precio: Decimal128("5800"),
    unidad_medida: "kg",
    estado: "activo",
    imagenes: ["aguacate_01.jpg"],
    inventario: {
      presentacion: "Caja x 15 kg",
      cantidad_disponible: 60,
      estado: "disponible",
      fecha_actualizacion: new Date("2026-10-18")
    },
    fecha_creacion: new Date("2024-03-10")
  },
  {
    _id: idProductoTomate,
    productor_id: idProductor2,
    categoria_id: idCategoriaVerduras,
    nombre: "Tomate Chonto",
    descripcion: "Tomate chonto fresco cultivado en invernadero.",
    precio: Decimal128("2800"),
    unidad_medida: "kg",
    estado: "activo",
    imagenes: ["tomate_01.jpg"],
    inventario: {
      presentacion: "Canastilla x 20 kg",
      cantidad_disponible: 5,
      estado: "disponible",
      fecha_actualizacion: new Date("2026-10-21")
    },
    fecha_creacion: new Date("2024-03-12")
  },
  {
    _id: idProductoCebolla,
    productor_id: idProductor2,
    categoria_id: idCategoriaVerduras,
    nombre: "Cebolla Cabezona",
    descripcion: "Cebolla cabezona blanca, cultivo tradicional santandereano.",
    precio: Decimal128("2200"),
    unidad_medida: "kg",
    estado: "activo",
    imagenes: ["cebolla_01.jpg"],
    inventario: {
      presentacion: "Bulto x 25 kg",
      cantidad_disponible: 0,
      estado: "agotado",
      fecha_actualizacion: new Date("2026-10-23")
    },
    fecha_creacion: new Date("2024-03-15")
  },
  {
    _id: idProductoPapa,
    productor_id: idProductor3,
    categoria_id: idCategoriaTuberculos,
    nombre: "Papa Pastusa",
    descripcion: "Papa pastusa de primera calidad.",
    precio: Decimal128("1900"),
    unidad_medida: "kg",
    estado: "activo",
    imagenes: ["papa_01.jpg"],
    inventario: {
      presentacion: "Bulto x 50 kg",
      cantidad_disponible: 120,
      estado: "disponible",
      fecha_actualizacion: new Date("2026-10-19")
    },
    fecha_creacion: new Date("2024-01-25")
  },
  {
    _id: idProductoQueso,
    productor_id: idProductor3,
    categoria_id: idCategoriaLacteos,
    nombre: "Queso Campesino",
    descripcion: "Queso campesino artesanal fresco.",
    precio: Decimal128("12000"),
    unidad_medida: "unidad",
    estado: "activo",
    imagenes: ["queso_01.jpg"],
    inventario: {
      presentacion: "Bloque x 500 g",
      cantidad_disponible: 3,
      estado: "disponible",
      fecha_actualizacion: new Date("2026-10-24")
    },
    fecha_creacion: new Date("2024-02-01")
  },
  {
    _id: idProductoCafe,
    productor_id: idProductor4,
    categoria_id: idCategoriaGranosCafe,
    nombre: "Cafe Especial de San Gil",
    descripcion: "Cafe de altura, tostado medio, cultivado en San Gil.",
    precio: Decimal128("22000"),
    unidad_medida: "libra",
    estado: "activo",
    imagenes: ["cafe_01.jpg", "cafe_02.jpg"],
    inventario: {
      presentacion: "Bolsa x 500 g",
      cantidad_disponible: 45,
      estado: "disponible",
      fecha_actualizacion: new Date("2026-10-17")
    },
    fecha_creacion: new Date("2024-04-20")
  },
  {
    _id: idProductoPanela,
    productor_id: idProductor4,
    categoria_id: idCategoriaPanela,
    nombre: "Panela Organica en Pastilla",
    descripcion: "Panela organica procesada de forma artesanal.",
    precio: Decimal128("6500"),
    unidad_medida: "kg",
    estado: "activo",
    imagenes: ["panela_01.jpg"],
    inventario: {
      presentacion: "Paquete x 1 kg",
      cantidad_disponible: 70,
      estado: "disponible",
      fecha_actualizacion: new Date("2026-10-16")
    },
    fecha_creacion: new Date("2024-04-25")
  },
  {
    _id: idProductoLechuga,
    productor_id: idProductor3,
    categoria_id: idCategoriaVerduras,
    nombre: "Lechuga Crespa",
    descripcion: "Lechuga crespa fresca cultivada sin agroquimicos.",
    precio: Decimal128("1500"),
    unidad_medida: "unidad",
    estado: "activo",
    imagenes: ["lechuga_01.jpg"],
    inventario: {
      presentacion: "Unidad",
      cantidad_disponible: 10,
      estado: "disponible",
      fecha_actualizacion: new Date("2026-10-25")
    },
    fecha_creacion: new Date("2024-05-01")
  }
]);

print("-> 10 documentos insertados en 'productos'.");

// ----------------------------------------------------------------------------
// 9. INSERCION DE DATOS - COLECCION pedidos (10 documentos)
//    consumidor_id apunta a usuarios con rol Consumidor existentes.
//    items.producto_id apunta a productos existentes.
//    subtotal = suma de items.subtotal ; total = subtotal + costo_envio
// ----------------------------------------------------------------------------
print("\n------------------------------------------------------------");
print(" Insertando documentos en 'pedidos' ");
print("------------------------------------------------------------");

db.pedidos.insertMany([
  {
    _id: idPedido1,
    consumidor_id: idUsuarioConsumidor1,
    fecha: new Date("2026-09-01"),
    estado: "entregado",
    tipo_entrega: "domicilio",
    items: [
      {
        producto_id: idProductoMango,
        nombre_producto: "Mango Tommy",
        cantidad: 5,
        precio_unitario: Decimal128("3500"),
        subtotal: Decimal128("17500"),
        calificacion_producto: 5,
        comentario_producto: "Muy fresco y dulce."
      },
      {
        producto_id: idProductoCafe,
        nombre_producto: "Cafe Especial de San Gil",
        cantidad: 1,
        precio_unitario: Decimal128("22000"),
        subtotal: Decimal128("22000"),
        calificacion_producto: 5,
        comentario_producto: "Excelente aroma."
      }
    ],
    direccion_entrega: {
      direccion: "Calle 45 #20-30",
      municipio: "Bucaramanga",
      barrio: "Alvarez",
      referencia: "Casa esquinera"
    },
    subtotal: Decimal128("39500"),
    costo_envio: Decimal128("4000"),
    total: Decimal128("43500")
  },
  {
    _id: idPedido2,
    consumidor_id: idUsuarioConsumidor2,
    fecha: new Date("2026-09-03"),
    estado: "pendiente",
    tipo_entrega: "domicilio",
    items: [
      {
        producto_id: idProductoTomate,
        nombre_producto: "Tomate Chonto",
        cantidad: 3,
        precio_unitario: Decimal128("2800"),
        subtotal: Decimal128("8400")
      },
      {
        producto_id: idProductoCebolla,
        nombre_producto: "Cebolla Cabezona",
        cantidad: 2,
        precio_unitario: Decimal128("2200"),
        subtotal: Decimal128("4400")
      }
    ],
    direccion_entrega: {
      direccion: "Carrera 10 #15-40",
      municipio: "Floridablanca",
      barrio: "Caney",
      referencia: "Conjunto Villa Verde"
    },
    subtotal: Decimal128("12800"),
    costo_envio: Decimal128("3500"),
    total: Decimal128("16300")
  },
  {
    _id: idPedido3,
    consumidor_id: idUsuarioConsumidor3,
    fecha: new Date("2026-09-05"),
    estado: "pendiente",
    tipo_entrega: "recogida_punto",
    items: [
      {
        producto_id: idProductoPapa,
        nombre_producto: "Papa Pastusa",
        cantidad: 10,
        precio_unitario: Decimal128("1900"),
        subtotal: Decimal128("19000")
      }
    ],
    direccion_entrega: {
      direccion: "Calle 100 #5-12",
      municipio: "Piedecuesta",
      barrio: "Centro",
      referencia: "Frente a la plaza"
    },
    subtotal: Decimal128("19000"),
    costo_envio: Decimal128("0"),
    total: Decimal128("19000")
  },
  {
    _id: idPedido4,
    consumidor_id: idUsuarioConsumidor4,
    fecha: new Date("2026-09-07"),
    estado: "entregado",
    tipo_entrega: "domicilio",
    items: [
      {
        producto_id: idProductoQueso,
        nombre_producto: "Queso Campesino",
        cantidad: 2,
        precio_unitario: Decimal128("12000"),
        subtotal: Decimal128("24000"),
        calificacion_producto: 4,
        comentario_producto: "Buen sabor."
      }
    ],
    direccion_entrega: {
      direccion: "Carrera 8 #22-18",
      municipio: "Lebrija",
      barrio: "El Centro",
      referencia: "Cerca al terminal"
    },
    subtotal: Decimal128("24000"),
    costo_envio: Decimal128("5000"),
    total: Decimal128("29000")
  },
  {
    _id: idPedido5,
    consumidor_id: idUsuarioConsumidor1,
    fecha: new Date("2026-09-10"),
    estado: "en_camino",
    tipo_entrega: "domicilio",
    items: [
      {
        producto_id: idProductoPanela,
        nombre_producto: "Panela Organica en Pastilla",
        cantidad: 4,
        precio_unitario: Decimal128("6500"),
        subtotal: Decimal128("26000")
      },
      {
        producto_id: idProductoMora,
        nombre_producto: "Mora de Castilla",
        cantidad: 2,
        precio_unitario: Decimal128("4200"),
        subtotal: Decimal128("8400")
      }
    ],
    direccion_entrega: {
      direccion: "Calle 45 #20-30",
      municipio: "Bucaramanga",
      barrio: "Alvarez",
      referencia: "Casa esquinera"
    },
    subtotal: Decimal128("34400"),
    costo_envio: Decimal128("4000"),
    total: Decimal128("38400")
  },
  {
    _id: idPedido6,
    consumidor_id: idUsuarioConsumidor2,
    fecha: new Date("2026-09-12"),
    estado: "entregado",
    tipo_entrega: "domicilio",
    items: [
      {
        producto_id: idProductoAguacate,
        nombre_producto: "Aguacate Hass",
        cantidad: 6,
        precio_unitario: Decimal128("5800"),
        subtotal: Decimal128("34800"),
        calificacion_producto: 5,
        comentario_producto: "Excelente punto de maduracion."
      }
    ],
    direccion_entrega: {
      direccion: "Carrera 10 #15-40",
      municipio: "Floridablanca",
      barrio: "Caney",
      referencia: "Conjunto Villa Verde"
    },
    subtotal: Decimal128("34800"),
    costo_envio: Decimal128("4500"),
    total: Decimal128("39300")
  },
  {
    _id: idPedido7,
    consumidor_id: idUsuarioConsumidor3,
    fecha: new Date("2026-09-15"),
    estado: "pendiente",
    tipo_entrega: "domicilio",
    items: [
      {
        producto_id: idProductoLechuga,
        nombre_producto: "Lechuga Crespa",
        cantidad: 4,
        precio_unitario: Decimal128("1500"),
        subtotal: Decimal128("6000")
      },
      {
        producto_id: idProductoTomate,
        nombre_producto: "Tomate Chonto",
        cantidad: 2,
        precio_unitario: Decimal128("2800"),
        subtotal: Decimal128("5600")
      }
    ],
    direccion_entrega: {
      direccion: "Calle 100 #5-12",
      municipio: "Piedecuesta",
      barrio: "Centro",
      referencia: "Frente a la plaza"
    },
    subtotal: Decimal128("11600"),
    costo_envio: Decimal128("3000"),
    total: Decimal128("14600")
  },
  {
    _id: idPedido8,
    consumidor_id: idUsuarioConsumidor4,
    fecha: new Date("2026-09-18"),
    estado: "cancelado",
    tipo_entrega: "domicilio",
    items: [
      {
        producto_id: idProductoCafe,
        nombre_producto: "Cafe Especial de San Gil",
        cantidad: 2,
        precio_unitario: Decimal128("22000"),
        subtotal: Decimal128("44000")
      }
    ],
    direccion_entrega: {
      direccion: "Carrera 8 #22-18",
      municipio: "Lebrija",
      barrio: "El Centro",
      referencia: "Cerca al terminal"
    },
    subtotal: Decimal128("44000"),
    costo_envio: Decimal128("4000"),
    total: Decimal128("48000")
  },
  {
    _id: idPedido9,
    consumidor_id: idUsuarioConsumidor1,
    fecha: new Date("2026-09-20"),
    estado: "entregado",
    tipo_entrega: "recogida_punto",
    items: [
      {
        producto_id: idProductoMango,
        nombre_producto: "Mango Tommy",
        cantidad: 8,
        precio_unitario: Decimal128("3500"),
        subtotal: Decimal128("28000"),
        calificacion_producto: 4,
        comentario_producto: "Buena relacion precio calidad."
      },
      {
        producto_id: idProductoPapa,
        nombre_producto: "Papa Pastusa",
        cantidad: 5,
        precio_unitario: Decimal128("1900"),
        subtotal: Decimal128("9500")
      }
    ],
    direccion_entrega: {
      direccion: "Calle 45 #20-30",
      municipio: "Bucaramanga",
      barrio: "Alvarez",
      referencia: "Casa esquinera"
    },
    subtotal: Decimal128("37500"),
    costo_envio: Decimal128("0"),
    total: Decimal128("37500")
  },
  {
    _id: idPedido10,
    consumidor_id: idUsuarioConsumidor2,
    fecha: new Date("2026-09-22"),
    estado: "pendiente",
    tipo_entrega: "domicilio",
    items: [
      {
        producto_id: idProductoQueso,
        nombre_producto: "Queso Campesino",
        cantidad: 1,
        precio_unitario: Decimal128("12000"),
        subtotal: Decimal128("12000")
      },
      {
        producto_id: idProductoPanela,
        nombre_producto: "Panela Organica en Pastilla",
        cantidad: 3,
        precio_unitario: Decimal128("6500"),
        subtotal: Decimal128("19500")
      }
    ],
    direccion_entrega: {
      direccion: "Carrera 10 #15-40",
      municipio: "Floridablanca",
      barrio: "Caney",
      referencia: "Conjunto Villa Verde"
    },
    subtotal: Decimal128("31500"),
    costo_envio: Decimal128("4500"),
    total: Decimal128("36000")
  }
]);

print("-> 10 documentos insertados en 'pedidos'.");

// ----------------------------------------------------------------------------
// 10. INSERCION DE DATOS - COLECCION pagos (10 documentos)
//     pedido_id apunta a pedidos existentes.
// ----------------------------------------------------------------------------
print("\n------------------------------------------------------------");
print(" Insertando documentos en 'pagos' ");
print("------------------------------------------------------------");

db.pagos.insertMany([
  { _id: new ObjectId(), pedido_id: idPedido1,  metodo_pago: "tarjeta_credito",   valor: Decimal128("43500"), estado: "aprobado",   fecha_pago: new Date("2026-09-01"), referencia_transaccion: "TXN-0001-PEPEPS" },
  { _id: new ObjectId(), pedido_id: idPedido2,  metodo_pago: "transferencia",     valor: Decimal128("16300"), estado: "pendiente",  fecha_pago: new Date("2026-09-03"), referencia_transaccion: "TXN-0002-PEPEPS" },
  { _id: new ObjectId(), pedido_id: idPedido3,  metodo_pago: "efectivo",          valor: Decimal128("19000"), estado: "pendiente",  fecha_pago: new Date("2026-09-05"), referencia_transaccion: "TXN-0003-PEPEPS" },
  { _id: new ObjectId(), pedido_id: idPedido4,  metodo_pago: "tarjeta_debito",    valor: Decimal128("29000"), estado: "aprobado",   fecha_pago: new Date("2026-09-07"), referencia_transaccion: "TXN-0004-PEPEPS" },
  { _id: new ObjectId(), pedido_id: idPedido5,  metodo_pago: "PSE",               valor: Decimal128("38400"), estado: "aprobado",   fecha_pago: new Date("2026-09-10"), referencia_transaccion: "TXN-0005-PEPEPS" },
  { _id: new ObjectId(), pedido_id: idPedido6,  metodo_pago: "tarjeta_credito",   valor: Decimal128("39300"), estado: "aprobado",   fecha_pago: new Date("2026-09-12"), referencia_transaccion: "TXN-0006-PEPEPS" },
  { _id: new ObjectId(), pedido_id: idPedido7,  metodo_pago: "efectivo",          valor: Decimal128("14600"), estado: "pendiente",  fecha_pago: new Date("2026-09-15"), referencia_transaccion: "TXN-0007-PEPEPS" },
  { _id: new ObjectId(), pedido_id: idPedido8,  metodo_pago: "transferencia",     valor: Decimal128("48000"), estado: "reembolsado",fecha_pago: new Date("2026-09-18"), referencia_transaccion: "TXN-0008-PEPEPS" },
  { _id: new ObjectId(), pedido_id: idPedido9,  metodo_pago: "PSE",               valor: Decimal128("37500"), estado: "aprobado",   fecha_pago: new Date("2026-09-20"), referencia_transaccion: "TXN-0009-PEPEPS" },
  { _id: new ObjectId(), pedido_id: idPedido10, metodo_pago: "tarjeta_debito",    valor: Decimal128("36000"), estado: "rechazado",  fecha_pago: new Date("2026-09-22"), referencia_transaccion: "TXN-0010-PEPEPS" }
]);

print("-> 10 documentos insertados en 'pagos'.");

// ----------------------------------------------------------------------------
// 11. INSERCION DE DATOS - COLECCION entregas (10 documentos)
//     pedido_id apunta a pedidos existentes.
//     responsable_id apunta a productores (Productor) o IDs de transportador.
// ----------------------------------------------------------------------------
print("\n------------------------------------------------------------");
print(" Insertando documentos en 'entregas' ");
print("------------------------------------------------------------");

const idTransportador1 = new ObjectId();
const idTransportador2 = new ObjectId();

db.entregas.insertMany([
  {
    _id: new ObjectId(),
    pedido_id: idPedido1,
    responsable_id: idTransportador1,
    tipo_responsable: "Transportador",
    estado_entrega: "entregado",
    fecha_programada: new Date("2026-09-02"),
    fecha_entrega: new Date("2026-09-02"),
    calificacion_entrega: 5,
    comentario_entrega: "Entrega puntual y producto en buen estado.",
    observaciones: "Sin novedades."
  },
  {
    _id: new ObjectId(),
    pedido_id: idPedido2,
    responsable_id: idTransportador2,
    tipo_responsable: "Transportador",
    estado_entrega: "programada",
    fecha_programada: new Date("2026-09-05"),
    fecha_entrega: null,
    observaciones: "Pendiente confirmacion de pago."
  },
  {
    _id: new ObjectId(),
    pedido_id: idPedido3,
    responsable_id: idProductor3,
    tipo_responsable: "Productor",
    estado_entrega: "programada",
    fecha_programada: new Date("2026-09-06"),
    fecha_entrega: null,
    observaciones: "Recogida en punto de la finca."
  },
  {
    _id: new ObjectId(),
    pedido_id: idPedido4,
    responsable_id: idTransportador1,
    tipo_responsable: "Transportador",
    estado_entrega: "entregado",
    fecha_programada: new Date("2026-09-08"),
    fecha_entrega: new Date("2026-09-08"),
    calificacion_entrega: 4,
    comentario_entrega: "Buen servicio, leve retraso.",
    observaciones: "Retraso de 20 minutos."
  },
  {
    _id: new ObjectId(),
    pedido_id: idPedido5,
    responsable_id: idTransportador2,
    tipo_responsable: "Transportador",
    estado_entrega: "en_camino",
    fecha_programada: new Date("2026-09-11"),
    fecha_entrega: null,
    observaciones: "En ruta hacia Bucaramanga."
  },
  {
    _id: new ObjectId(),
    pedido_id: idPedido6,
    responsable_id: idTransportador1,
    tipo_responsable: "Transportador",
    estado_entrega: "entregado",
    fecha_programada: new Date("2026-09-13"),
    fecha_entrega: new Date("2026-09-13"),
    calificacion_entrega: 5,
    comentario_entrega: "Excelente, producto fresco.",
    observaciones: "Sin novedades."
  },
  {
    _id: new ObjectId(),
    pedido_id: idPedido7,
    responsable_id: idProductor2,
    tipo_responsable: "Productor",
    estado_entrega: "programada",
    fecha_programada: new Date("2026-09-16"),
    fecha_entrega: null,
    observaciones: "Entrega directa del productor."
  },
  {
    _id: new ObjectId(),
    pedido_id: idPedido8,
    responsable_id: idTransportador2,
    tipo_responsable: "Transportador",
    estado_entrega: "cancelada",
    fecha_programada: new Date("2026-09-19"),
    fecha_entrega: null,
    observaciones: "Pedido cancelado por el cliente."
  },
  {
    _id: new ObjectId(),
    pedido_id: idPedido9,
    responsable_id: idProductor1,
    tipo_responsable: "Productor",
    estado_entrega: "entregado",
    fecha_programada: new Date("2026-09-21"),
    fecha_entrega: new Date("2026-09-21"),
    calificacion_entrega: 4,
    comentario_entrega: "Recogida sin contratiempos.",
    observaciones: "Sin novedades."
  },
  {
    _id: new ObjectId(),
    pedido_id: idPedido10,
    responsable_id: idTransportador1,
    tipo_responsable: "Transportador",
    estado_entrega: "programada",
    fecha_programada: new Date("2026-09-23"),
    fecha_entrega: null,
    observaciones: "Pago rechazado, pendiente de confirmacion."
  }
]);

print("-> 10 documentos insertados en 'entregas'.");

// ----------------------------------------------------------------------------
// 12. CREACION DE INDICES UTILES PARA EL PROYECTO
// ----------------------------------------------------------------------------
print("\n------------------------------------------------------------");
print(" Creando indices utiles ");
print("------------------------------------------------------------");

db.usuarios.createIndex({ correo: 1 }, { unique: true });
print("-> Indice creado: usuarios.correo (unico)");

db.productos.createIndex({ categoria_id: 1 });
print("-> Indice creado: productos.categoria_id");

db.productos.createIndex({ productor_id: 1 });
print("-> Indice creado: productos.productor_id");

db.productores.createIndex({ municipio: 1 });
print("-> Indice creado: productores.municipio");

db.pedidos.createIndex({ consumidor_id: 1 });
print("-> Indice creado: pedidos.consumidor_id");

db.pedidos.createIndex({ estado: 1 });
print("-> Indice creado: pedidos.estado");

db.pagos.createIndex({ estado: 1 });
print("-> Indice creado: pagos.estado");

db.entregas.createIndex({ estado_entrega: 1 });
print("-> Indice creado: entregas.estado_entrega");

// ----------------------------------------------------------------------------
// 13. VERIFICACION FINAL - CONTEO DE DOCUMENTOS POR COLECCION
// ----------------------------------------------------------------------------
print("\n============================================================");
print(" VERIFICACION FINAL - Conteo de documentos por coleccion ");
print("============================================================");

coleccionesProyecto.forEach(function (nombreColeccion) {
  const total = db.getCollection(nombreColeccion).countDocuments();
  print("-> " + nombreColeccion + ": " + total + " documentos");
});

print("\n============================================================");
print(" Script PEPEPS ejecutado exitosamente. Base de datos lista. ");
print("============================================================");
