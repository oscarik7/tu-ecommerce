<?php

return [
    /*
     * Si está en false, no se loguea nada.
     */
    'enabled' => env('ACTIVITY_LOGGER_ENABLED', true),

    /*
     * Si un evento no tiene cambios dirty, no se guarda.
     */
    'submit_empty_logs' => false,

    /*
     * Nombre del log por defecto.
     */
    'default_log_name' => 'default',

    /*
     * Modelo del "causer" (el que ejecuta la acción).
     */
    'default_auth_driver' => null,

    /*
     * Nombre de la tabla donde se guardan los logs.
     */
    'table_name' => env('ACTIVITY_LOG_TABLE', 'activity_log'),

    /*
     * Conexión de base de datos. null = conexión por defecto.
     */
    'database_connection' => env('ACTIVITY_LOG_DB_CONNECTION', null),

    /*
     * Modelo usado para los registros de actividad.
     */
    'activity_model' => \Spatie\Activitylog\Models\Activity::class,

    /*
     * Cantidad máxima de logs a conservar por sujeto. null = sin límite.
     */
    'max_event_count' => env('ACTIVITY_LOG_MAX_EVENT_COUNT', null),

    /*
     * Días que se conservan los logs. null = para siempre.
     */
    'delete_records_older_than_days' => env('ACTIVITY_LOG_OLDER_THAN_DAYS', null),
];