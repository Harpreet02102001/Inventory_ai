<?php

use App\Providers\AppServiceProvider;


return [
    App\Providers\AppServiceProvider::class,
    App\Providers\AuthServiceProvider::class,  // ← Gates registered here
];
