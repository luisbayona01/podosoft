<?php

return [

    'pagination_size' => env('WHATSAPP_CONTACTS_PAGE_SIZE', 100),

    'max_pages' => env('WHATSAPP_CONTACTS_MAX_PAGES', 250),

    'cache_ttl_minutes' => env('WHATSAPP_CONTACTS_CACHE_MINUTES', 10),

    'request_timeout_seconds' => env('WHATSAPP_CONTACTS_REQUEST_TIMEOUT', 30),

    'permissions' => [
        'view' => 'whatsapp.contacts.view',
        'export' => 'whatsapp.contacts.export',
    ],

];