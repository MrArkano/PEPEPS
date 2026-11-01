// ============================================================================
// PEPEPS - Plataforma Digital de Mercados Campesinos de Santander
// Entrega #3 - Base de Datos II (NoSQL) - MongoDB
// Consultas relevantes (5) sobre el dataset creado en SCRIPT_PROYECTO_PEPEPS.js
// Ejecutar con: mongosh CONSULTAS_PEPEPS.js
// Requisito previo: haber ejecutado SCRIPT_PROYECTO_PEPEPS.js al menos una vez.
// ============================================================================

db = db.getSiblingDB("pepeps");

function imprimirResultados(cursorOrArray) {
  const resultados = cursorOrArray.toArray ? cursorOrArray.toArray() : cursorOrArray;
  if (resultados.length === 0) {
    print("(Sin resultados)");
  } else {
    resultados.forEach(function (doc) {
      printjson(doc);
    });
  }
  print("Total de filas devueltas: " + resultados.length);
}

// ============================================================================
// CONSULTA 1
// OBJETIVO: Identificar los productos mas vendidos agrupados por municipio
//           del productor, para detectar que se vende mas en cada zona de
//           Santander.
// COLECCIONES INVOLUCRADAS: pedidos + productos + productores (3 colecciones)
// ============================================================================
print("\n============================================================");
print(" CONSULTA 1: Productos mas vendidos por municipio ");
print("============================================================");

const resultadoConsulta1 = db.pedidos.aggregate([
  // Descomponer cada pedido en sus items individuales
  { $unwind: "$items" },

  // Relacionar cada item con su producto
  {
    $lookup: {
      from: "productos",
      localField: "items.producto_id",
      foreignField: "_id",
      as: "producto_info"
    }
  },
  { $unwind: "$producto_info" },

  // Relacionar el producto con su productor para obtener el municipio
  {
    $lookup: {
      from: "productores",
      localField: "producto_info.productor_id",
      foreignField: "_id",
      as: "productor_info"
    }
  },
  { $unwind: "$productor_info" },

  // Agrupar por municipio y nombre del producto, sumando cantidades vendidas
  {
    $group: {
      _id: {
        municipio: "$productor_info.municipio",
        producto: "$items.nombre_producto"
      },
      cantidad_total_vendida: { $sum: "$items.cantidad" }
    }
  },

  // Dar formato legible a la salida
  {
    $project: {
      _id: 0,
      municipio: "$_id.municipio",
      producto: "$_id.producto",
      cantidad_total_vendida: 1
    }
  },

  { $sort: { municipio: 1, cantidad_total_vendida: -1 } }
]);

imprimirResultados(resultadoConsulta1);

// ============================================================================
// CONSULTA 2
// OBJETIVO: Detectar que productores tienen pedidos en estado "pendiente"
//           para priorizar su gestion y despacho.
// COLECCIONES INVOLUCRADAS: pedidos + productos + productores (3 colecciones)
// ============================================================================
print("\n============================================================");
print(" CONSULTA 2: Productores con pedidos pendientes ");
print("============================================================");

const resultadoConsulta2 = db.pedidos.aggregate([
  // Solo interesan los pedidos pendientes
  { $match: { estado: "pendiente" } },

  // Descomponer items para llegar al producto vendido
  { $unwind: "$items" },

  {
    $lookup: {
      from: "productos",
      localField: "items.producto_id",
      foreignField: "_id",
      as: "producto_info"
    }
  },
  { $unwind: "$producto_info" },

  {
    $lookup: {
      from: "productores",
      localField: "producto_info.productor_id",
      foreignField: "_id",
      as: "productor_info"
    }
  },
  { $unwind: "$productor_info" },

  // Contar pedidos pendientes distintos por productor
  // (se usa el _id del pedido para no duplicar conteo entre items del mismo pedido)
  {
    $group: {
      _id: {
        productor: "$productor_info.nombre_finca_asociacion",
        municipio: "$productor_info.municipio",
        pedido: "$_id"
      }
    }
  },
  {
    $group: {
      _id: {
        productor: "$_id.productor",
        municipio: "$_id.municipio"
      },
      cantidad_pedidos_pendientes: { $sum: 1 }
    }
  },

  {
    $project: {
      _id: 0,
      finca_asociacion: "$_id.productor",
      municipio: "$_id.municipio",
      cantidad_pedidos_pendientes: 1
    }
  },

  { $sort: { cantidad_pedidos_pendientes: -1 } }
]);

imprimirResultados(resultadoConsulta2);

// ============================================================================
// CONSULTA 3
// OBJETIVO: Consultar el historial de pedidos realizados por cada consumidor,
//           util para soporte al cliente y analisis de comportamiento de compra.
// COLECCIONES INVOLUCRADAS: pedidos + usuarios (2 colecciones)
// ============================================================================
print("\n============================================================");
print(" CONSULTA 3: Historial de pedidos por consumidor ");
print("============================================================");

const resultadoConsulta3 = db.pedidos.aggregate([
  {
    $lookup: {
      from: "usuarios",
      localField: "consumidor_id",
      foreignField: "_id",
      as: "consumidor_info"
    }
  },
  { $unwind: "$consumidor_info" },

  {
    $project: {
      _id: 0,
      consumidor: {
        $concat: ["$consumidor_info.nombre", " ", "$consumidor_info.apellido"]
      },
      fecha: 1,
      estado: 1,
      tipo_entrega: 1,
      total: 1
    }
  },

  { $sort: { consumidor: 1, fecha: 1 } }
]);

imprimirResultados(resultadoConsulta3);

// ============================================================================
// CONSULTA 4
// OBJETIVO: Analizar el comportamiento de pagos por metodo de pago y estado,
//           para identificar cuantos pagos y cuanto valor se mueve en cada
//           combinacion (aprobado, pendiente, rechazado, reembolsado).
// COLECCIONES INVOLUCRADAS: pagos + pedidos (2 colecciones)
// ============================================================================
print("\n============================================================");
print(" CONSULTA 4: Pagos por medio y estado (agrupado) ");
print("============================================================");

const resultadoConsulta4 = db.pagos.aggregate([
  // Se vincula con pedidos para validar la relacion y poder enriquecer
  // el analisis (por ejemplo, confirmar existencia del pedido asociado)
  {
    $lookup: {
      from: "pedidos",
      localField: "pedido_id",
      foreignField: "_id",
      as: "pedido_info"
    }
  },
  { $unwind: "$pedido_info" },

  {
    $group: {
      _id: {
        metodo_pago: "$metodo_pago",
        estado_pago: "$estado"
      },
      cantidad_pagos: { $sum: 1 },
      valor_total: { $sum: { $toDecimal: "$valor" } }
    }
  },

  {
    $project: {
      _id: 0,
      metodo_pago: "$_id.metodo_pago",
      estado_pago: "$_id.estado_pago",
      cantidad_pagos: 1,
      valor_total: 1
    }
  },

  { $sort: { metodo_pago: 1, estado_pago: 1 } }
]);

imprimirResultados(resultadoConsulta4);

// ============================================================================
// CONSULTA 5
// OBJETIVO: Detectar productos agotados o con existencias bajas (<= 10
//           unidades) por productor, para priorizar reabastecimiento.
// COLECCIONES INVOLUCRADAS: productos + productores (2 colecciones)
// ============================================================================
print("\n============================================================");
print(" CONSULTA 5: Productos agotados o con existencias bajas por productor ");
print("============================================================");

const resultadoConsulta5 = db.productos.aggregate([
  // Filtrar productos agotados o con 10 unidades o menos disponibles
  {
    $match: {
      $or: [
        { "inventario.estado": "agotado" },
        { "inventario.cantidad_disponible": { $lte: 10 } }
      ]
    }
  },

  {
    $lookup: {
      from: "productores",
      localField: "productor_id",
      foreignField: "_id",
      as: "productor_info"
    }
  },
  { $unwind: "$productor_info" },

  {
    $project: {
      _id: 0,
      productor: "$productor_info.nombre_finca_asociacion",
      municipio: "$productor_info.municipio",
      producto: "$nombre",
      cantidad_disponible: "$inventario.cantidad_disponible",
      estado_inventario: "$inventario.estado"
    }
  },

  { $sort: { cantidad_disponible: 1 } }
]);

imprimirResultados(resultadoConsulta5);

print("\n============================================================");
print(" Fin de la ejecucion de las 5 consultas de PEPEPS. ");
print("============================================================");
