<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->post('/comments/store', 'Home::store');
$routes->post('/comments/delete/(:num)', 'Home::delete/$1');
