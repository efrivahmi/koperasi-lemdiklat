<?php

return [

    /*
    |--------------------------------------------------------------------------
    | School Information
    |--------------------------------------------------------------------------
    */

    'name' => env('SCHOOL_NAME', 'Koperasi Lemdiklat Taruna Nusantara Indonesia'),

    'full_name' => 'Sistem Koperasi Lemdiklat Taruna Nusantara Indonesia',

    'subtitle' => 'SMA Taruna Nusantara Indonesia | SMK Taruna Nusantara Jaya',

    'short_name' => 'Koperasi Lemdiklat TNI',

    /*
    |--------------------------------------------------------------------------
    | Logo Configuration
    |--------------------------------------------------------------------------
    |
    | Logo paths stored in storage/app/public/logos/
    | Run: php artisan storage:link to create symbolic link
    |
    */

    'logo' => [
        'main' => env('LOGO_MAIN', '/storage/logos/logo.png'),
        'icon' => env('LOGO_ICON', '/storage/logos/icon.png'),
        'dark' => env('LOGO_DARK', '/storage/logos/logo-dark.png'),
    ],

    /*
    |--------------------------------------------------------------------------
    | School Contact
    |--------------------------------------------------------------------------
    */

    'contact' => [
        'address' => env('SCHOOL_ADDRESS', 'Jl. Pendidikan No. 123, Jakarta'),
        'phone' => env('SCHOOL_PHONE', '(021) 1234-5678'),
        'email' => env('SCHOOL_EMAIL', 'info@lemdiklat-tni.sch.id'),
        'website' => env('SCHOOL_WEBSITE', 'https://lemdiklat-tni.sch.id'),
    ],

];
