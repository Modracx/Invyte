<?php

/**
 * Invyte - Professional Edition
 * Invyte is an open-source inventory management system designed for Magento 2
 * Version: 1.0.0
 * Kenneth D'silva (Modracx), Copyright (c) March 2026
 * Licensed under the MIT License – https://opensource.org/licenses/MIT
 */


// Auth (public)
$router->post('/auth/login', 'AuthController@login');

// All routes below require JWT auth
$router->group(['middleware' => 'auth'], function () use ($router) {

    $router->post('/auth/logout', 'AuthController@logout');
    $router->get('/auth/me', 'AuthController@me');

    // Dashboard
    $router->get('/dashboard', 'ReportController@dashboard');

    // Products
    $router->get('/products', 'ProductController@index');
    $router->get('/products/{id}', 'ProductController@show');
    $router->put('/products/{id}/stock', 'ProductController@updateStock');

    // Sources (admin: CRUD; manager: read own)
    $router->get('/sources', 'SourceController@index');
    $router->group(['middleware' => 'role:admin'], function () use ($router) {
        $router->post('/sources', 'SourceController@store');
        $router->put('/sources/{code}', 'SourceController@update');
        $router->delete('/sources/{code}', 'SourceController@destroy');
    });

    // Location hierarchy (per source) — admin full CRUD, manager read+write own sources
    $router->get('/sources/{code}/locations',         'LocationController@tree');
    $router->get('/sources/{code}/locations/flat',    'LocationController@flat');
    $router->post('/sources/{code}/locations',        'LocationController@store');
    $router->put('/sources/{code}/locations/{id}',    'LocationController@update');
    $router->delete('/sources/{code}/locations/{id}', 'LocationController@destroy');

    // Product location assignments
    $router->get('/products/{id}/locations',  'LocationController@productLocations');
    $router->put('/products/{id}/location',   'LocationController@assignLocation');

    // Movements
    $router->get('/movements', 'MovementController@index');
    $router->post('/movements', 'MovementController@store');

    // Purchase Orders
    $router->get('/purchase-orders', 'PurchaseOrderController@index');
    $router->post('/purchase-orders', 'PurchaseOrderController@store');
    $router->get('/purchase-orders/{id}', 'PurchaseOrderController@show');
    $router->put('/purchase-orders/{id}', 'PurchaseOrderController@update');
    $router->put('/purchase-orders/{id}/receive', 'PurchaseOrderController@receive');
    $router->delete('/purchase-orders/{id}', 'PurchaseOrderController@destroy');

    // Suppliers (admin only)
    $router->group(['middleware' => 'role:admin'], function () use ($router) {
        $router->get('/suppliers', 'SupplierController@index');
        $router->post('/suppliers', 'SupplierController@store');
        $router->get('/suppliers/{id}', 'SupplierController@show');
        $router->put('/suppliers/{id}', 'SupplierController@update');
        $router->delete('/suppliers/{id}', 'SupplierController@destroy');
    });

    // Reports
    $router->get('/reports/low-stock', 'ReportController@lowStock');
    $router->get('/reports/stock-value', 'ReportController@stockValue');
    $router->get('/reports/movements', 'ReportController@movementSummary');

    // Alerts
    $router->get('/alerts', 'ReportController@alerts');
    $router->put('/alerts/{id}/resolve', 'ReportController@resolveAlert');

    // Magento Orders
    $router->get('/orders', 'FulfillmentController@orders');
    $router->get('/orders/{id}', 'FulfillmentController@orderDetail');

    // Fulfillments
    $router->get('/fulfillments', 'FulfillmentController@index');
    $router->post('/fulfillments', 'FulfillmentController@store');
    $router->get('/fulfillments/{id}', 'FulfillmentController@show');
    $router->put('/fulfillments/{id}', 'FulfillmentController@update');
    $router->post('/fulfillments/{id}/confirm', 'FulfillmentController@confirm');
    $router->delete('/fulfillments/{id}', 'FulfillmentController@destroy');
});
