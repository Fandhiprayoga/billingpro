<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// ---------------------------------------------------------------
// Auth Routes (Shield)
// ---------------------------------------------------------------
service('auth')->routes($routes);

// ---------------------------------------------------------------
// Public Routes
// ---------------------------------------------------------------
$routes->get('/', 'AuthController::login');
$routes->get('maintenance', static function () {
    return view('errors/maintenance');
});

// ---------------------------------------------------------------
// Protected Routes (require login)
// ---------------------------------------------------------------
$routes->group('', ['filter' => 'session'], static function ($routes) {

    // Dashboard
    $routes->get('dashboard', 'DashboardController::index');

    // Switch Active Group
    $routes->post('switch-group', 'GroupSwitchController::switch');

    // Profile
    $routes->get('profile', 'ProfileController::index');
    $routes->post('profile/update', 'ProfileController::update');

    // ---------------------------------------------------------------
    // User Billing Routes (semua user yang login bisa akses)
    // ---------------------------------------------------------------

    // Browse Plans
    $routes->get('plans', 'UserOrderController::plans');

    // My Orders
    $routes->group('my-orders', static function ($routes) {
        $routes->get('/', 'UserOrderController::index');
        $routes->get('create', 'UserOrderController::create');
        $routes->post('store', 'UserOrderController::store');
        $routes->get('view/(:segment)', 'UserOrderController::view/$1');
        $routes->get('upload-confirmation/(:segment)', 'UserOrderController::uploadConfirmation/$1');
        $routes->post('submit-confirmation/(:segment)', 'UserOrderController::submitConfirmation/$1');
    });

    // My Licenses
    $routes->group('my-licenses', static function ($routes) {
        $routes->get('/', 'UserLicenseController::index');
        $routes->get('view/(:num)', 'UserLicenseController::view/$1');
    });

    // ---------------------------------------------------------------
    // Admin Routes (require admin.access permission)
    // ---------------------------------------------------------------
    $routes->group('admin', ['filter' => 'permission:admin.access'], static function ($routes) {

        // User Management
        $routes->group('users', static function ($routes) {
            $routes->get('/', 'UserController::index', ['filter' => 'permission:users.list']);
            $routes->get('create', 'UserController::create', ['filter' => 'permission:users.create']);
            $routes->post('store', 'UserController::store', ['filter' => 'permission:users.create']);
            $routes->get('edit/(:num)', 'UserController::edit/$1', ['filter' => 'permission:users.edit']);
            $routes->post('update/(:num)', 'UserController::update/$1', ['filter' => 'permission:users.edit']);
            $routes->post('delete/(:num)', 'UserController::delete/$1', ['filter' => 'permission:users.delete']);
            $routes->post('assign-role/(:num)', 'UserController::assignRole/$1', ['filter' => 'permission:users.manage-roles']);
        });

        // Role Management (superadmin only)
        $routes->group('roles', ['filter' => 'role:superadmin'], static function ($routes) {
            $routes->get('/', 'RoleController::index');
            $routes->get('permissions', 'RoleController::permissions');
        });

        // Settings
        $routes->group('settings', ['filter' => 'permission:admin.settings'], static function ($routes) {
            $routes->get('/', 'SettingController::index');
            $routes->post('update/general', 'SettingController::updateGeneral');
            $routes->post('update/auth', 'SettingController::updateAuth');
            $routes->post('update/mail', 'SettingController::updateMail');
        });

        // ---------------------------------------------------------------
        // Licensing & Billing Module
        // ---------------------------------------------------------------

        // Plan Management
        $routes->group('plans', static function ($routes) {
            $routes->get('/', 'PlanController::index', ['filter' => 'permission:plans.list']);
            $routes->get('create', 'PlanController::create', ['filter' => 'permission:plans.create']);
            $routes->post('store', 'PlanController::store', ['filter' => 'permission:plans.create']);
            $routes->get('edit/(:num)', 'PlanController::edit/$1', ['filter' => 'permission:plans.edit']);
            $routes->post('update/(:num)', 'PlanController::update/$1', ['filter' => 'permission:plans.edit']);
            $routes->post('delete/(:num)', 'PlanController::delete/$1', ['filter' => 'permission:plans.delete']);
        });

        // Order Management
        $routes->group('orders', static function ($routes) {
            $routes->get('/', 'OrderController::index', ['filter' => 'permission:orders.list']);
            $routes->get('create', 'OrderController::create', ['filter' => 'permission:orders.create']);
            $routes->post('store', 'OrderController::store', ['filter' => 'permission:orders.create']);
            $routes->get('view/(:segment)', 'OrderController::view/$1', ['filter' => 'permission:orders.view']);
            $routes->get('upload-confirmation/(:segment)', 'OrderController::uploadConfirmation/$1', ['filter' => 'permission:orders.create']);
            $routes->post('submit-confirmation/(:segment)', 'OrderController::submitConfirmation/$1', ['filter' => 'permission:orders.create']);
            $routes->post('approve/(:segment)', 'OrderController::approve/$1', ['filter' => 'permission:orders.approve']);
            $routes->post('reject/(:segment)', 'OrderController::reject/$1', ['filter' => 'permission:orders.reject']);
        });

        // License Management
        $routes->group('licenses', static function ($routes) {
            $routes->get('/', 'LicenseController::index', ['filter' => 'permission:licenses.list']);
            $routes->get('view/(:num)', 'LicenseController::view/$1', ['filter' => 'permission:licenses.view']);
            $routes->post('revoke/(:num)', 'LicenseController::revoke/$1', ['filter' => 'permission:licenses.revoke']);
        });
    });

    // Serve uploaded files securely from writable/uploads
    $routes->get('uploads/(:any)', 'FileController::serve/$1');
});

// ---------------------------------------------------------------
// Public API Routes (no session required)
// ---------------------------------------------------------------
$routes->group('api', static function ($routes) {
    $routes->group('license', static function ($routes) {
        $routes->post('activate', 'Api\LicenseApiController::activate');
        $routes->post('check', 'Api\LicenseApiController::check');
    });
});
