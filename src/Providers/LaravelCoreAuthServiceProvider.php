<?php

namespace Gingerminds\LaravelCore\Providers;

use Gingerminds\LaravelCore\Models\Permission\Permission;
use Gingerminds\LaravelCore\Models\Role\Role;
use Gingerminds\LaravelCore\Models\User\Contributor;
use Gingerminds\LaravelCore\Models\User\User;
use Gingerminds\LaravelCore\Policies\Permission\PermissionPolicy;
use Gingerminds\LaravelCore\Policies\Role\RolePolicy;
use Gingerminds\LaravelCore\Policies\User\ContributorPolicy;
use Gingerminds\LaravelCore\Policies\User\UserPolicy;
use Gingerminds\LaravelCore\Resolver\ResourceResolver;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\PermissionRegistrar;

class LaravelCoreAuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Contributor::class => ContributorPolicy::class,
        Permission::class  => PermissionPolicy::class,
        Role::class        => RolePolicy::class,
        User::class        => UserPolicy::class,
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Pins the morph alias to the app's configured user model so it stays
        // stable in model_has_roles/model_has_permissions regardless of which
        // User subclass an app or package ends up instantiating.
        Relation::morphMap([
            'user' => ResourceResolver::model('user'),
        ]);

        Gate::before(function ($user = null) {
            return $user?->hasRole('Super-Admin') ? true : null;
        });

        app(PermissionRegistrar::class)
            ->registerPermissions(app(\Illuminate\Contracts\Auth\Access\Gate::class));

        $this->registerPolicies();
    }
}
