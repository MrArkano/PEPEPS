#!/bin/bash
# Se ejecuta una sola vez al inicializar el volumen. Usuario de aplicación con
# mínimo privilegio: solo readWrite sobre la base de PEPEPS.
mongosh --quiet -u "$MONGO_INITDB_ROOT_USERNAME" -p "$MONGO_INITDB_ROOT_PASSWORD" --authenticationDatabase admin <<JS
db = db.getSiblingDB("$MONGODB_DATABASE");
db.createUser({ user: "$MONGO_APP_USER", pwd: "$MONGO_APP_PASSWORD", roles: [{ role: "readWrite", db: "$MONGODB_DATABASE" }] });
JS
