<?php

return [
    // Base directory where standalone Laravel packages (modules) live
    'path' => base_path('modules'),

    // List of enabled modules by name (Vendor/Package or slug)
    'enabled' => [
        // 'vendor/package',
    ],

    // If true, auto-discover modules under the path even if not in enabled list
    'auto_discover' => true,
];
