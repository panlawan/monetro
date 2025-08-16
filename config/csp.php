<?php
return [
    'directive' => [
        'default-src' => ["'self'"],
        'script-src' => [
            "'self'", 
            "'unsafe-inline'", 
            "'unsafe-eval'",  // สำหรับ Chart.js
            'https://cdn.jsdelivr.net'
        ],
        'style-src' => ["'self'", "'unsafe-inline'"],
        'img-src' => ["'self'", 'data:', 'blob:'],
        'font-src' => ["'self'", 'https://fonts.gstatic.com'],
        'connect-src' => ["'self'"],
        'worker-src' => ["'self'"],
        'frame-src' => ["'none'"],
        'object-src' => ["'none'"],
        'base-uri' => ["'self'"],
        'form-action' => ["'self'"],
    ],
];