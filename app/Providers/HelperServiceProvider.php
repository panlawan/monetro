<?php
// สร้างไฟล์ app/Providers/HelperServiceProvider.php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class HelperServiceProvider extends ServiceProvider
{
    public function register()
    {
        require_once app_path('Helpers/AvatarHelper.php');
    }

    public function boot()
    {
        //
    }
}

// แล้วเพิ่มใน config/app.php ใน providers array
// App\Providers\HelperServiceProvider::class,

// หรือใช้วิธีง่าย ๆ โดยสร้างไฟล์ composer.json autoload
// เพิ่มใน composer.json:
/*
"autoload": {
    "psr-4": {
        "App\\": "app/",
        "Database\\Factories\\": "database/factories/",
        "Database\\Seeders\\": "database/seeders/"
    },
    "files": [
        "app/Helpers/AvatarHelper.php"
    ]
}
*/

// แล้วรัน: composer dump-autoload