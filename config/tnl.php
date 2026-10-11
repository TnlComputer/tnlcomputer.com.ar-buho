<?php

// Datos fijos del sitio. Lo que cambia seguido (productos, planes, proyectos) se edita desde /admin.
return [
    'name' => 'TNL Computer',
    'tagline' => 'Tecnología para tu empresa: hosting, desarrollo web, hardware y service técnico.',
    'email' => env('TNL_EMAIL', 'info@tnlcomputer.com.ar'),
    // Casilla que recibe los formularios y pedidos.
    'notify_to' => env('TNL_NOTIFY_TO', 'jorgemartinez@tnlcomputer.com.ar'),
    'whatsapp' => env('TNL_WHATSAPP', '5491149156462'),
    'whatsapp_label' => '+54 9 11 4915-6462',

    'social' => [
        'Instagram' => 'https://www.instagram.com/tnlcomputer/',
        'Facebook' => 'https://www.facebook.com/TNLCOMPUTER',
        'X' => 'https://twitter.com/tnlcomputer',
        'LinkedIn' => 'https://www.linkedin.com/in/jorgegustavomartinez/',
        'GitHub' => 'https://github.com/tnlcomputer',
    ],

    // Recargo (%) que se suma al precio de la lista del proveedor al importarla.
    'markup' => (float) env('TNL_MARKUP', 15),

    // Familias de rubros para el menú de la tienda (por slug). Los rubros que no estén acá van a "Otros".
    'shop_groups' => [
        'Componentes' => ['cpu', ['procesadores-amd', 'procesadores-intel', 'motherboards-amd', 'motherboards-intel', 'memorias-ram', 'placas-de-video', 'fuentes', 'gabinetes', 'coolers', 'placas-de-expansion']],
        'Almacenamiento' => ['server', ['discos-ssd', 'discos-rigidos', 'carry-y-caddy-disks', 'memorias-sd-y-pendrives']],
        'Periféricos' => ['tag', ['teclados', 'mouses', 'combos-teclado-y-mouse', 'auriculares', 'microfonos', 'webcams', 'mouse-pads', 'parlantes', 'perifericos']],
        'Equipos' => ['smartphone', ['notebooks', 'accesorios-para-notebooks', 'mini-pcs', 'monitores', 'tablets-y-accesorios', 'celulares-y-accesorios', 'proyectores']],
        'Redes y energía' => ['truck', ['redes-y-conectividad', 'adaptadores-y-cables-de-video', 'estabilizadores-y-ups']],
        'Impresión' => ['pen', ['impresoras', 'insumos-de-impresion']],
        'Gaming y hogar' => ['sparkles', ['consolas-y-gaming', 'sillas-gamer', 'media-streaming', 'camaras', 'seguridad', 'soportes', 'hogar-y-electro', 'limpieza-de-pc', 'mineria', 'software']],
    ],

    'brands' => ['amd', 'asus', 'crucial', 'evga', 'hyperx', 'intel', 'kingston', 'logitech', 'redragon'],

    'hosting_features' => [
        ['SSL gratis', 'Certificado SSL incluido para tu dominio.'],
        ['Migración asistida', 'Mudamos tu sitio sin cortes ni pérdida de mails.'],
        ['Soporte 24x7', 'Teléfono, e-mail y tickets.'],
        ['Ilimitados', 'Casillas de mail, bases de datos y subdominios.'],
        ['Panel de control', 'cPanel o DirectAdmin, a tu elección.'],
        ['Multi PHP', 'Elegí la versión de PHP 7.x u 8.x ideal para tu aplicación.'],
        ['DNS propios', 'Usá DNS propios o DNS discretos.'],
        ['Backups automáticos', 'Copia semanal disponible en tu panel.'],
        ['Softaculous', 'Instalá WordPress y cientos de apps en segundos.'],
    ],

    'design_services' => [
        ['Diseño web', 'Relevamos qué necesita tu sitio: secciones, contenidos y objetivos, y lo convertimos en un diseño claro.'],
        ['Branding', 'Identidad visual, tono y lenguaje acordes a tu audiencia, para una marca reconocible y sostenible en el tiempo.'],
        ['Marketing digital', 'Estrategia y presencia en buscadores y redes, con planificación e investigación de lo que querés comunicar.'],
        ['Analítica', 'Medimos todo lo implementado, sacamos conclusiones y rediseñamos para mejorar de forma continua.'],
        ['Desarrollo', 'Sitios y sistemas rápidos y seguros, con Laravel, PHP, JavaScript y bases de datos.'],
        ['UI / UX', 'Interfaces pensadas para tus usuarios: intuitivas, accesibles y atractivas.'],
        ['Responsive', 'Mobile-first: tu sitio se ve y funciona bien en cualquier pantalla.'],
        ['Fotografía', 'Imágenes de producto y de marca cuidadas, que venden.'],
        ['Soporte', 'Acompañamiento antes, durante y después de publicar tu sitio.'],
    ],

    'service_reasons' => [
        'No enciende', 'Hace ruido', 'No carga Windows', 'Pantalla azul', 'Se apaga sola',
        'Temperatura', 'Se mojó', 'Se golpeó', 'Lenta / virus', 'Otro',
    ],

    // Textos tomados del portfolio publicado en el hosting (2026), más nuevo que el del repo.
    'profile' => [
        'name' => 'Jorge Gustavo Martinez',
        'role' => 'Full-Stack Developer',
        'lead' => 'Diseño e implemento soluciones web con foco en claridad técnica, datos y resultados de negocio.',
        'email' => 'tnlcomputer@gmail.com',
        'photo' => 'img/portfolio/yo-2024.png',
        'github' => 'https://github.com/TnlComputer',
        'linkedin' => 'https://www.linkedin.com/in/jorgegustavomartinez/',
        'about_title' => 'Trayectoria técnica con visión de negocio',
        'about' => [
            'Full-Stack Developer con más de 30 años en tecnología: desde sistemas administrativos y hardware hasta aplicaciones web modernas, APIs, bases de datos e inteligencia artificial aplicada.',
            'Trabajo con Laravel, PHP, Java/Spring, Python, JavaScript, MySQL y herramientas de BI. Me interesa construir productos útiles, mantenibles y orientados a resultados.',
        ],
        // período, puesto, descripción
        'experience' => [
            ['2019 — Actualidad', 'Full-Stack Developer', 'Desarrollo de aplicaciones web, integración con bases de datos y e-commerce. Stack: Laravel, PHP, JavaScript, MySQL.'],
            ['2001 — 2017', 'Desarrollo web y soporte técnico', 'Front-end con HTML, CSS y PHP; armado y mantenimiento de redes, equipos y soporte a usuarios.'],
            ['1987 — 1990', 'Analista Programador COBOL', 'Análisis y programación de sistemas administrativos en entornos empresariales.'],
        ],
        'hobby' => [
            'title' => 'Autos',
            'text' => 'Me encantan los autos: mejorarlos o restaurarlos. Si puedo hacerlo yo, mejor; si no, busco algún amigo y lo ayudo.',
            'image' => 'img/portfolio/bmw-2016-320x320.webp',
        ],
    ],
];
