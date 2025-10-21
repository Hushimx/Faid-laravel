<?php

if (!function_exists('HandleActiveSidebar')) {
    function handleActiveSidebar(array $routes): string|null
    {
        foreach ($routes as $route) {
            if (\Illuminate\Support\Facades\Route::is(trim($route))) {
                return 'active';
            }
        }
    }
}
