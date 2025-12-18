#!/bin/bash

# Lee el valor de APP_ENV del archivo .env de forma segura, sin ejecutarlo
# Se asegura de que la línea no esté comentada y extrae el valor después del '='
APP_ENV=$(grep -vE '^\s*#' .env | grep -E '^APP_ENV=' | cut -d '=' -f2-)

# Decide qué archivo de compose usar y lo exporta como variable de entorno
if [ "$APP_ENV" = "PRODUCTION" ]; then
    echo "✅ Configurando INTRANET para producción..."
    export COMPOSE_FILE=docker-compose-prod.yml
else
    echo "🛠️ Configurando INTRANET para desarrollo..."
    export COMPOSE_FILE=docker-compose-dev.yml
fi

# Levanta los contenedores usando la configuración que ya fue seleccionada
echo "Levantando contenedores con '$COMPOSE_FILE'..."
./vendor/bin/sail up -d --build