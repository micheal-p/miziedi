<?php

/* =========================================
   PUBLIC API
   ========================================= */
$routes['GET']['/api/categories'] = 'CategoryController@getAll';
$routes['GET']['/api/products'] = 'ProductController@getProductsApi';
$routes['POST']['/api/checkout'] = 'OrderController@createOrder';
$routes['POST']['/api/paystack/webhook'] = 'OrderController@paystackWebhook';
$routes['GET']['/api/track'] = 'OrderController@trackOrderApi';

/* =========================================
   ADMIN API (Auth Required)
   ========================================= */
$routes['POST']['/api/admin/login'] = 'AuthController@adminLogin';

// Categories
$routes['POST']['/api/admin/category'] = 'CategoryController@create';

// Products
$routes['POST']['/api/admin/product'] = 'ProductController@create'; // Add
$routes['POST']['/api/admin/product/([a-f0-9]+)'] = 'ProductController@update'; // Edit (POST for files)
$routes['DELETE']['/api/admin/product/([a-f0-9]+)'] = 'ProductController@delete'; // Delete

// Settings
$routes['POST']['/api/admin/settings'] = 'AdminController@saveSettings';

// Orders
$routes['GET']['/api/admin/orders'] = 'OrderController@getAllOrders';
$routes['PUT']['/api/admin/order/([a-f0-9]+)/status'] = 'OrderController@updateStatus';

// Admin: Update Category
$routes['POST']['/api/admin/category/([a-f0-9]+)'] = 'CategoryController@update';