<?php

// Solo las reglas que usa el sitio; el resto cae al inglés.
return [
    'accepted' => 'Tenés que aceptar para continuar.',
    'between' => [
        'numeric' => 'Tiene que estar entre :min y :max.',
    ],
    'boolean' => 'Valor inválido.',
    'date' => 'La fecha no es válida.',
    'regex' => 'El formato no es válido.',
    'required_if' => 'Este campo es obligatorio.',
    'uploaded' => 'No se pudo subir el archivo.',
    'email' => 'Ingresá un email válido.',
    'exists' => 'La opción elegida no es válida.',
    'file' => 'Tiene que ser un archivo.',
    'image' => 'Tiene que ser una imagen.',
    'in' => 'Elegí una opción de la lista.',
    'integer' => 'Tiene que ser un número entero.',
    'max' => [
        'numeric' => 'No puede ser mayor a :max.',
        'file' => 'El archivo no puede pesar más de :max KB.',
        'string' => 'No puede tener más de :max caracteres.',
    ],
    'mimes' => 'El archivo tiene que ser: :values.',
    'min' => [
        'numeric' => 'Tiene que ser al menos :min.',
        'string' => 'Tiene que tener al menos :min caracteres.',
    ],
    'numeric' => 'Tiene que ser un número.',
    'required' => 'Este campo es obligatorio.',
    'string' => 'Tiene que ser texto.',
    'unique' => 'Ya existe un registro con ese valor.',
    'url' => 'Ingresá una URL válida (con https://).',
];
