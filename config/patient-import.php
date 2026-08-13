<?php

return [

    'permission' => 'importar pacientes',

    'allowed_extensions' => ['csv', 'txt'],

    'allowed_mimes' => [
        'text/plain',
        'text/csv',
        'application/csv',
        'application/vnd.ms-excel',
        'application/octet-stream',
    ],

    'max_file_size_kb' => env('PATIENT_IMPORT_MAX_FILE_SIZE_KB', 2048),

    'max_rows' => env('PATIENT_IMPORT_MAX_ROWS', 2000),

    'batch_size' => env('PATIENT_IMPORT_BATCH_SIZE', 100),

    'preview_rows' => 15,

    'duplicate_behavior' => env('PATIENT_IMPORT_DUPLICATE_BEHAVIOR', 'skip'),

    'columns' => [
        'nombre',
        'apellido',
        'telefono',
        'tipo_documento',
        'documento',
        'email',
        'fecha_nacimiento',
        'sexo',
        'direccion',
    ],

    'required_columns' => [
        'nombre',
        'apellido',
        'telefono',
    ],

];