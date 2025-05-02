<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('/Kasir', 'Kasir\Main::index');
$routes->get('/Panel', 'Panel\Main::index');


$routes->group('api', function($routes) {
    $routes->post('login', 'Kasir\KasirApi::login');
    $routes->get('products', 'Kasir\KasirApi::products');
    $routes->post('checkout', 'Kasir\KasirApi::checkout');
});
