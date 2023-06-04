<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;
use SimpleMVC\Controller\Error404;
use SimpleMVC\Controller\Error405;

return [
    'routing' => [
        'routes' => require 'route.php'
    ],
    'error' => [
        '404' => Error404::class,
        '405' => Error405::class
    ]
];
