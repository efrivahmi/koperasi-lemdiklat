<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Inventory Configuration
    |--------------------------------------------------------------------------
    |
    | Configure inventory-related settings such as low stock thresholds
    | and reorder points.
    |
    */

    'inventory' => [
        'low_stock_threshold' => env('LOW_STOCK_THRESHOLD', 10),
        'out_of_stock_threshold' => env('OUT_OF_STOCK_THRESHOLD', 0),
    ],

    /*
    |--------------------------------------------------------------------------
    | Student Balance Configuration
    |--------------------------------------------------------------------------
    |
    | Configure student balance limits, topup amounts, and transaction
    | restrictions.
    |
    */

    'balance' => [
        'max_balance' => env('MAX_STUDENT_BALANCE', 50000000),
        'min_topup' => env('MIN_TOPUP_AMOUNT', 1000),
        'max_topup' => env('MAX_TOPUP_AMOUNT', 10000000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Session Configuration for Cashier Role
    |--------------------------------------------------------------------------
    |
    | Configure session lifetime for cashier users who may work long shifts.
    | Default is 8 hours (480 minutes).
    |
    */

    'session' => [
        'cashier_lifetime' => env('CASHIER_SESSION_LIFETIME', 480), // 8 hours in minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | Transaction Configuration
    |--------------------------------------------------------------------------
    |
    | Configure transaction-related settings.
    |
    */

    'transaction' => [
        'recent_transactions_limit' => env('RECENT_TRANSACTIONS_LIMIT', 10),
        'top_products_limit' => env('TOP_PRODUCTS_LIMIT', 5),
    ],

];
