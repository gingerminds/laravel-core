<?php

use Gingerminds\LaravelCore\ApiProvider\Permission\PermissionProvider;
use Gingerminds\LaravelCore\ApiProvider\Role\RoleProvider;
use Gingerminds\LaravelCore\ApiProvider\User\ContributorProvider;
use Gingerminds\LaravelCore\ApiProvider\User\UserProvider;
use Gingerminds\LaravelCore\Http\Controllers\Permission\PermissionController;
use Gingerminds\LaravelCore\Http\Controllers\Role\RoleController;
use Gingerminds\LaravelCore\Http\Controllers\User\ContributorController;
use Gingerminds\LaravelCore\Http\Controllers\User\UserController;
use Gingerminds\LaravelCore\Http\Requests\Permission\PermissionRequest;
use Gingerminds\LaravelCore\Http\Requests\Role\RoleRequest;
use Gingerminds\LaravelCore\Http\Requests\User\ContributorRequest;
use Gingerminds\LaravelCore\Http\Requests\User\UserRequest;
use Gingerminds\LaravelCore\Models\Permission\Permission;
use Gingerminds\LaravelCore\Models\Role\Role;
use Gingerminds\LaravelCore\Models\User\Contributor;
use Gingerminds\LaravelCore\Models\User\User;
use Gingerminds\LaravelCore\Repositories\Permission\PermissionRepository;
use Gingerminds\LaravelCore\Repositories\Role\RoleRepository;
use Gingerminds\LaravelCore\Repositories\User\ContributorRepository;
use Gingerminds\LaravelCore\Repositories\User\UserRepository;
use Gingerminds\LaravelCore\StateProcessor\Permission\PermissionStateProcessor;
use Gingerminds\LaravelCore\StateProcessor\Role\RoleStateProcessor;
use Gingerminds\LaravelCore\StateProcessor\User\ContributorStateProcessor;
use Gingerminds\LaravelCore\StateProcessor\User\UserStateProcessor;

return [
    /*
    |--------------------------------------------------------------------------
    | Route Prefix
    |--------------------------------------------------------------------------
    |
    | This value is the prefix that will be applied to the routes of the package.
    |
    */
    'admin_prefix' => env('GINGERMINDS_CORE_PREFIX', 'admin'),

    /*
    |--------------------------------------------------------------------------
    | Health Check Route
    |--------------------------------------------------------------------------
    |
    | Path of a lightweight, unauthenticated route that always returns a 200
    | JSON response. Registered outside the admin prefix, so it is never
    | caught by the login redirect — useful as a CI/monitoring health check
    | target, since the application root usually redirects to the admin area.
    |
    */
    'health_check_path' => env('HEALTH_CHECK_PATH', 'health'),

    'hreflang' => [
        'locales'   => [],
        'x_default' => null,
    ],

    'redirect_after_save' => [
        'store'     => 'index',
        'update'    => 'edit',
        'resources' => [],
    ],

    'resources' => [
        'user' => [
            'model' => User::class,
            'controller' => UserController::class,
            'repository' => UserRepository::class,
            'provider' => UserProvider::class,
            'request' => UserRequest::class,
            'state_processor' => UserStateProcessor::class,
        ],
        'contributor' => [
            'model' => Contributor::class,
            'controller' => ContributorController::class,
            'repository' => ContributorRepository::class,
            'provider' => ContributorProvider::class,
            'request' => ContributorRequest::class,
            'state_processor' => ContributorStateProcessor::class,
        ],
        'role' => [
            'model' => Role::class,
            'controller' => RoleController::class,
            'repository' => RoleRepository::class,
            'provider' => RoleProvider::class,
            'request' => RoleRequest::class,
            'state_processor' => RoleStateProcessor::class,
        ],
        'permission' => [
            'model' => Permission::class,
            'controller' => PermissionController::class,
            'repository' => PermissionRepository::class,
            'provider' => PermissionProvider::class,
            'request' => PermissionRequest::class,
            'state_processor' => PermissionStateProcessor::class,
        ],
    ]
];
