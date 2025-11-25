<?php

/* =========================================
   FRONTEND STOREFRONT
   ========================================= */
$routes['GET']['/'] = 'ProductController@index'; 
$routes['GET']['/cart'] = 'OrderController@cart';
$routes['GET']['/checkout'] = 'OrderController@checkout';
$routes['GET']['/track'] = 'OrderController@trackPage';

// Cart Logic
$routes['POST']['/cart/add'] = 'OrderController@addToCart';
$routes['POST']['/cart/remove'] = 'OrderController@removeFromCart';

// Dynamic Product Detail (Regex for ID)
$routes['GET']['/product/([a-f0-9]+)'] = 'ProductController@detail';

/* =========================================
   STATIC PAGES & SHORTCUTS
   ========================================= */
$routes['GET']['/returns'] = 'PageController@returns';
$routes['GET']['/story'] = 'PageController@story';
$routes['GET']['/sustainability'] = 'PageController@sustainability';
$routes['GET']['/new-arrivals'] = 'PageController@newArrivals';
$routes['GET']['/best-sellers'] = 'PageController@bestSellers';

/* =========================================
   ADMIN PAGES (UI)
   ========================================= */
$routes['GET']['/admin/login'] = 'AdminController@loginPage';
$routes['GET']['/admin/dashboard'] = 'AdminController@dashboard';
$routes['GET']['/admin/products'] = 'AdminController@productsPage';
$routes['GET']['/admin/categories'] = 'AdminController@categoriesPage';
$routes['GET']['/admin/orders'] = 'AdminController@ordersPage';
$routes['GET']['/admin/settings'] = 'AdminController@settingsPage';