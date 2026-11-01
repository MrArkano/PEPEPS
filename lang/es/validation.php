<?php

return [
    'accepted' => 'Debes aceptar :attribute.',
    'array' => ':attribute debe ser una lista.',
    'between' => [
        'numeric' => ':attribute debe estar entre :min y :max.',
        'string' => ':attribute debe tener entre :min y :max caracteres.',
    ],
    'boolean' => ':attribute debe ser verdadero o falso.',
    'confirmed' => 'La confirmación de :attribute no coincide.',
    'date' => ':attribute no es una fecha válida.',
    'date_format' => ':attribute no coincide con el formato :format.',
    'after_or_equal' => ':attribute debe ser una fecha igual o posterior a :date.',
    'decimal' => ':attribute debe tener :decimal decimales.',
    'email' => ':attribute debe ser un correo electrónico válido.',
    'exists' => 'El valor seleccionado en :attribute no es válido.',
    'file' => ':attribute debe ser un archivo.',
    'gt' => [
        'numeric' => ':attribute debe ser mayor que :value.',
    ],
    'gte' => [
        'numeric' => ':attribute debe ser mayor o igual que :value.',
    ],
    'image' => ':attribute debe ser una imagen.',
    'in' => 'El valor seleccionado en :attribute no es válido.',
    'integer' => ':attribute debe ser un número entero.',
    'max' => [
        'numeric' => ':attribute no debe ser mayor que :max.',
        'file' => ':attribute no debe pesar más de :max kilobytes.',
        'string' => ':attribute no debe tener más de :max caracteres.',
        'array' => ':attribute no debe tener más de :max elementos.',
    ],
    'mimes' => ':attribute debe ser un archivo de tipo: :values.',
    'min' => [
        'numeric' => ':attribute debe ser al menos :min.',
        'file' => ':attribute debe pesar al menos :min kilobytes.',
        'string' => ':attribute debe tener al menos :min caracteres.',
        'array' => ':attribute debe tener al menos :min elementos.',
    ],
    'numeric' => ':attribute debe ser un número.',
    'regex' => 'El formato de :attribute no es válido.',
    'required' => ':attribute es obligatorio.',
    'required_if' => ':attribute es obligatorio cuando :other es :value.',
    'string' => ':attribute debe ser texto.',
    'unique' => ':attribute ya está en uso.',

    'password' => [
        'letters' => ':attribute debe contener al menos una letra.',
        'mixed' => ':attribute debe contener mayúsculas y minúsculas.',
        'numbers' => ':attribute debe contener al menos un número.',
        'symbols' => ':attribute debe contener al menos un símbolo.',
        'uncompromised' => ':attribute apareció en una filtración de datos. Elige otra.',
    ],

    'custom' => [],

    'attributes' => [
        'nombre' => 'El nombre',
        'apellido' => 'El apellido',
        'correo' => 'El correo',
        'telefono' => 'El teléfono',
        'contrasena' => 'La contraseña',
        'precio' => 'El precio',
        'cantidad' => 'La cantidad',
        'descripcion' => 'La descripción',
        'municipio' => 'El municipio',
    ],
];

