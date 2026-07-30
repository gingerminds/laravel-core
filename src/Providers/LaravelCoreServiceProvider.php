<?php

namespace Gingerminds\LaravelCore\Providers;

use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\Metadata\Resource\Factory\ResourceNameCollectionFactoryInterface;
use ApiPlatform\Metadata\ResourceClassResolverInterface;
use ApiPlatform\State\ProviderInterface;
use Gingerminds\LaravelCore\ApiPlatform\ApiHeaderParameterRegistry;
use Gingerminds\LaravelCore\ApiPlatform\ClassHierarchyResourceNameCollectionFactory;
use Gingerminds\LaravelCore\ApiPlatform\ContextHeaderParametersResourceMetadataCollectionFactory;
use Gingerminds\LaravelCore\ApiProvider\Permission\PermissionProvider;
use Gingerminds\LaravelCore\ApiProvider\Role\RoleProvider;
use Gingerminds\LaravelCore\ApiProvider\User\ContributorProvider;
use Gingerminds\LaravelCore\ApiProvider\User\UserProvider;
use Gingerminds\LaravelCore\Cache\CacheContextResolverInterface;
use Gingerminds\LaravelCore\Cache\CacheKeyBuilder;
use Gingerminds\LaravelCore\Cache\NullCacheContextResolver;
use Gingerminds\LaravelCore\Console\Commands\Make\CreateApiProvider;
use Gingerminds\LaravelCore\Console\Commands\Make\CreateControllerFull;
use Gingerminds\LaravelCore\Console\Commands\Make\CreateFormRequest;
use Gingerminds\LaravelCore\Console\Commands\Make\CreatePolicy;
use Gingerminds\LaravelCore\Console\Commands\Make\CreateRepository;
use Gingerminds\LaravelCore\Console\Commands\Make\CreateResource;
use Gingerminds\LaravelCore\Console\Commands\Make\CreateStateProcessor;
use Gingerminds\LaravelCore\Console\Commands\Security\CreateUser;
use Gingerminds\LaravelCore\Http\Controllers\Permission\PermissionController;
use Gingerminds\LaravelCore\Http\Controllers\Role\RoleController;
use Gingerminds\LaravelCore\Http\Controllers\User\ContributorController;
use Gingerminds\LaravelCore\Http\Controllers\User\UserController;
use Gingerminds\LaravelCore\Http\Middelware\Authenticate;
use Gingerminds\LaravelCore\Http\Middelware\EnsureAdminAreaIsAuthenticated;
use Gingerminds\LaravelCore\Http\Requests\Permission\PermissionRequest;
use Gingerminds\LaravelCore\Http\Requests\Role\RoleRequest;
use Gingerminds\LaravelCore\Http\Requests\User\ContributorRequest;
use Gingerminds\LaravelCore\Http\Requests\User\UserRequest;
use Gingerminds\LaravelCore\Livewire\Component\List\Filter\SelectModel;
use Gingerminds\LaravelCore\Models\CacheableResourceInterface;
use Gingerminds\LaravelCore\Models\CacheCascadeInterface;
use Gingerminds\LaravelCore\Models\EagerLoadableModelInterface;
use Gingerminds\LaravelCore\Models\Permission\Permission;
use Gingerminds\LaravelCore\Models\Role\Role;
use Gingerminds\LaravelCore\Models\User\Contributor;
use Gingerminds\LaravelCore\Models\User\User;
use Gingerminds\LaravelCore\Repositories\Filters\FilterHandlerRegistry;
use Gingerminds\LaravelCore\Repositories\Filters\Handlers\BooleanFilterHandler;
use Gingerminds\LaravelCore\Repositories\Filters\Handlers\DateFilterHandler;
use Gingerminds\LaravelCore\Repositories\Filters\Handlers\NumberFilterHandler;
use Gingerminds\LaravelCore\Repositories\Filters\Handlers\SelectFilterHandler;
use Gingerminds\LaravelCore\Repositories\Filters\Handlers\SelectModelFilterHandler;
use Gingerminds\LaravelCore\Repositories\Filters\Handlers\SelectStateFilterHandler;
use Gingerminds\LaravelCore\Repositories\Permission\PermissionRepository;
use Gingerminds\LaravelCore\Repositories\Role\RoleRepository;
use Gingerminds\LaravelCore\Repositories\User\ContributorRepository;
use Gingerminds\LaravelCore\Repositories\User\UserRepository;
use Gingerminds\LaravelCore\Resolver\ResourceResolver;
use Gingerminds\LaravelCore\Serializer\JsonCollectionNormalizer;
use Gingerminds\LaravelCore\StateProcessor\Permission\PermissionStateProcessor;
use Gingerminds\LaravelCore\StateProcessor\Role\RoleStateProcessor;
use Gingerminds\LaravelCore\StateProcessor\User\ContributorStateProcessor;
use Gingerminds\LaravelCore\StateProcessor\User\UserStateProcessor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Console\ModelMakeCommand as BaseModelMakeCommand;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplPriorityQueue;

class LaravelCoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(JsonCollectionNormalizer::class, function () {
            return new JsonCollectionNormalizer(
                $this->app->make(ResourceClassResolverInterface::class),
                config('api-platform.pagination.page_parameter_name'),
                $this->app->make(ResourceMetadataCollectionFactoryInterface::class),
            );
        });

        $this->app->extend('api_platform_normalizer_list', function (SplPriorityQueue $list) {
            $list->insert($this->app->make(JsonCollectionNormalizer::class), -800);

            return $list;
        });

        $this->mergeConfigFrom(
            __DIR__ . '/../../config/gingerminds-core.php',
            'gingerminds-core'
        );

        // Default cache context: no viewer axis (site/language/...) affects the
        // cache key. laravel-multisite rebinds this to a real resolver, and a
        // consuming project may extend it further (e.g. to add country) — see
        // CacheContextResolverInterface for why this can't just call
        // SiteContext/LanguageContext directly from core.
        $this->app->bind(CacheContextResolverInterface::class, NullCacheContextResolver::class);
        $this->app->singleton(CacheKeyBuilder::class);

        // Registre extensible des types de filtre (getFilters()' "type").
        // Tout package peut ajouter le sien via FilterHandlerRegistry::register()
        // depuis son propre service provider, sans modifier ce package.
        $this->app->singleton(FilterHandlerRegistry::class, function () {
            $registry = new FilterHandlerRegistry();
            $registry->register('date', new DateFilterHandler());
            $registry->register('number', new NumberFilterHandler());
            $registry->register('select', new SelectFilterHandler());
            $registry->register('select-model', new SelectModelFilterHandler());
            $registry->register('select-state', new SelectStateFilterHandler());
            $registry->register('boolean', new BooleanFilterHandler());

            return $registry;
        });

        // Enregistrement des configurations ou services si nécessaire
        $this->app->register(LaravelCoreAuthServiceProvider::class);

        $this->app->resolving('config', function ($config) {
            $resources = $config->get('api-platform.resources', []);
            $corePath  = realpath(__DIR__ . '/../Models');
            if ($corePath && !in_array($corePath, $resources, true)) {
                $config->set('api-platform.resources', array_merge($resources, [$corePath]));
            }
        });

        // Scanning both this package's Models/ (above) and the project's own
        // app/Models means a project subclass that redeclares #[ApiResource]
        // to override a package model (e.g. disabling operations on User)
        // would otherwise be registered alongside — not instead of — the
        // package's own class. This decorator keeps only the most-derived
        // class per hierarchy. See ClassHierarchyResourceNameCollectionFactory.
        $this->app->extend(
            ResourceNameCollectionFactoryInterface::class,
            static function (ResourceNameCollectionFactoryInterface $inner) {
                return new ClassHierarchyResourceNameCollectionFactory($inner);
            }
        );

        // Empty by default: core knows nothing about "site", "language" or
        // "country". Packages/projects that own a context register their own
        // marker (a trait or interface FQCN) -> HeaderParameter mapping from
        // their own service provider's boot() (see ApiHeaderParameterRegistry).
        $this->app->singleton(ApiHeaderParameterRegistry::class);

        $this->app->extend(
            ResourceMetadataCollectionFactoryInterface::class,
            static function (ResourceMetadataCollectionFactoryInterface $inner, $app) {
                return new ContextHeaderParametersResourceMetadataCollectionFactory(
                    $inner,
                    $app->make(ApiHeaderParameterRegistry::class)
                );
            }
        );

        $providerPath = __DIR__ . '/../ApiProvider';
        $iterator     = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($providerPath)
        );
        $toTag = [];
        foreach ($iterator as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $relativePath = $file->getPathname();
            $relativePath = substr($relativePath, strlen($providerPath) + 1, -4); // retire le préfixe et .php
            $class        = 'Gingerminds\\LaravelCore\\ApiProvider\\'
                . str_replace(DIRECTORY_SEPARATOR, '\\', $relativePath);
            if (class_exists($class) && is_subclass_of($class, ProviderInterface::class)) {
                $toTag[] = $class;
            }
        }
        if ($toTag !== []) {
            $this->app->tag($toTag, ProviderInterface::class);
        }

        $this->bindResources();
    }

    public function boot(): void
    {
        Route::model('user', ResourceResolver::model('user'));
        Route::model('contributor', ResourceResolver::model('contributor'));
        Route::model('role', ResourceResolver::model('role'));
        Route::model('permission', ResourceResolver::model('permission'));

        // Chargement des routes du package
        if (! $this->app->routesAreCached()) {
            $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');
            $this->loadRoutesFrom(__DIR__ . '/../../routes/api.php');
        }

        // Chargement des migrations
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        // Enregistrement des composants Livewire
        Livewire::component(
            'gingerminds.core.list.filter.select-model',
            SelectModel::class
        );

        // Chargement des vues
        $this->loadViewsFrom(
            __DIR__ . '/../../resources/views',
            'gingerminds-core'
        );

        // Chargement des traductions
        $this->loadTranslationsFrom(
            __DIR__ . '/../../resources/lang',
            'gingerminds-core'
        );

        // Publication des ressources pour surcharge par le projet Skeleton
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/gingerminds-core.php' => config_path('gingerminds-core.php'),
            ], 'gingerminds-config');

            $this->publishes([
              __DIR__ . '/../../resources/views' => resource_path('views/vendor/gingerminds-core'),
            ], 'gingerminds-views');

            $this->publishes([
              __DIR__ . '/../../resources/lang' => $this->app->langPath('vendor/gingerminds-core'),
            ], 'gingerminds-lang');

            $this->publishes([
              __DIR__ . '/../../stubs' => base_path('stubs/vendor/gingerminds-core'),
            ], 'gingerminds-stubs');

            $this->publishes([
              __DIR__ . '/../../resources/scss' => resource_path('scss/vendor/gingerminds-core'),
              __DIR__ . '/../../resources/js'   => resource_path('js/vendor/gingerminds-core'),
            ], 'gingerminds-assets');

            $this->commands([
              CreateApiProvider::class,
              CreateControllerFull::class,
              CreateFormRequest::class,
              CreatePolicy::class,
              CreateRepository::class,
              CreateResource::class,
              CreateStateProcessor::class,
              CreateUser::class,
            ]);

            $this->app->extend(
                BaseModelMakeCommand::class,
                function ($command, $app) {
                    // ← le 2ème argument est le container
                    return new class ($app->make('files')) extends BaseModelMakeCommand {
                        protected function getStub(): string
                        {
                            return __DIR__ . '/../../stubs/model.stub';
                        }
                    };
                }
            );
        }

        $this->app->make('router')->aliasMiddleware(
            'gingerminds-core.auth',
            Authenticate::class
        );

        $this->app->make('router')->pushMiddlewareToGroup(
            'web',
            EnsureAdminAreaIsAuthenticated::class
        );

        // Avoid web server static-file rules intercepting "/livewire/livewire.js" with a 404.
        Livewire::setScriptRoute(function ($handle) {
            return Route::get('/livewire/script', $handle);
        });

        Event::listen('eloquent.saved: *', function (string $eventName, array $payload): void {
            $this->handleCacheableWrite($payload[0] ?? null);
        });
        Event::listen('eloquent.restored: *', function (string $eventName, array $payload): void {
            $this->handleCacheableWrite($payload[0] ?? null);
        });
        Event::listen('eloquent.deleted: *', function (string $eventName, array $payload): void {
            $this->handleCacheableRemoval($payload[0] ?? null);
        });
        Event::listen('eloquent.forceDeleted: *', function (string $eventName, array $payload): void {
            $this->handleCacheableRemoval($payload[0] ?? null);
        });
    }

    /**
     * Covers create and update: the item cache is invalidated then
     * repopulated precisely (fresh row, eager loads applied) under the
     * writer's own cache context, while every other cached context for this
     * same id is simply dropped and left to repopulate lazily on next read
     * — same "invalidate, don't pre-warm" principle as listings. The
     * listing cache is always just invalidated: there is no bounded set of
     * filter/sort/pagination combinations to recompute eagerly.
     */
    private function handleCacheableWrite(mixed $model): void
    {
        if (! $model instanceof Model) {
            return;
        }

        // Same kill switch as AbstractRepository::get() (config('cache.activate'),
        // env CACHE_ACTIVATE) — if reads never populate the cache, these
        // Cache::tags() calls would otherwise be dead work at best, or throw
        // outright if the store is down/misconfigured (e.g. can't tag).
        if (! config('cache.activate', true)) {
            return;
        }

        if ($model instanceof CacheableResourceInterface) {
            $tag        = $model::getCacheKey();
            $id         = $model->getKey();
            $keyBuilder = app(CacheKeyBuilder::class);

            Cache::tags([$keyBuilder->itemTag($tag, $id)])->flush();

            if ($id !== null) {
                $fresh = $this->reloadForCache($model);

                if ($fresh instanceof Model) {
                    $ttl      = $model::getCacheTtl() ?? (int) config('cache.resource_ttl_seconds', 3600);
                    $key      = $keyBuilder->itemKey($tag, $keyBuilder->context(), $id);
                    $itemTags = Cache::tags([$tag, $keyBuilder->itemTag($tag, $id)]);

                    // Cache::put() treats a string $ttl by casting it to an int
                    // of seconds — passing the literal 'forever' through would
                    // cast to 0 and immediately forget() the entry, so 'forever'
                    // needs its own dedicated call.
                    if (is_string($ttl)) {
                        $itemTags->forever($key, $fresh);
                    } elseif ($ttl > 0) {
                        $itemTags->put($key, $fresh, now()->addSeconds($ttl));
                    }
                }
            }

            Cache::tags([$keyBuilder->listTag($tag)])->flush();
        }

        if ($model instanceof CacheCascadeInterface) {
            $this->cascadeFlush($model);
        }
    }

    /**
     * Covers delete and force delete: the item cache is removed outright
     * (no re-put, there's nothing left to cache), the listing cache is
     * invalidated the same way as on write.
     */
    private function handleCacheableRemoval(mixed $model): void
    {
        if (! $model instanceof Model) {
            return;
        }

        if (! config('cache.activate', true)) {
            return;
        }

        if ($model instanceof CacheableResourceInterface) {
            $tag        = $model::getCacheKey();
            $keyBuilder = app(CacheKeyBuilder::class);

            Cache::tags([$keyBuilder->itemTag($tag, $model->getKey())])->flush();
            Cache::tags([$keyBuilder->listTag($tag)])->flush();
        }

        if ($model instanceof CacheCascadeInterface) {
            $this->cascadeFlush($model);
        }
    }

    /**
     * Re-reads the model from the database (the in-memory instance may still
     * carry stale relations from before the save) with its declared eager
     * loads applied, so the cached item is never missing what its own
     * resource/serializer will ask for.
     */
    private function reloadForCache(Model $model): ?Model
    {
        $modelClass = $model::class;

        /** @var Model|null $fresh */
        $fresh = $modelClass::query()->find($model->getKey());

        if ($fresh === null) {
            return null;
        }

        if (is_subclass_of($modelClass, EagerLoadableModelInterface::class)) {
            $fresh->load($modelClass::getEagerLoads());
        }

        return $fresh;
    }

    /**
     * One level deep on purpose: cascading further would require a
     * tag-to-model-class registry to look up each parent's own
     * getCascadeCacheKeys(), which nothing currently needs. Add that if a
     * real multi-level case shows up.
     */
    private function cascadeFlush(CacheCascadeInterface $model): void
    {
        foreach ($model::getCascadeCacheKeys() as $parentTag) {
            Cache::tags([$parentTag])->flush();
        }
    }

    private function bindResources(): void
    {
        $this->app->bind(
            User::class,
            ResourceResolver::model('user')
        );
        $this->app->bind(
            UserController::class,
            ResourceResolver::controller('user')
        );
        $this->app->bind(
            UserRepository::class,
            ResourceResolver::repository('user')
        );
        $this->app->bind(
            UserProvider::class,
            ResourceResolver::provider('user')
        );
        $this->app->bind(
            UserRequest::class,
            ResourceResolver::request('user')
        );
        $this->app->bind(
            UserStateProcessor::class,
            ResourceResolver::stateProcessor('user')
        );

        $this->app->bind(
            Contributor::class,
            ResourceResolver::model('contributor')
        );
        $this->app->bind(
            ContributorController::class,
            ResourceResolver::controller('contributor')
        );
        $this->app->bind(
            ContributorRepository::class,
            ResourceResolver::repository('contributor')
        );
        $this->app->bind(
            ContributorProvider::class,
            ResourceResolver::provider('contributor')
        );
        $this->app->bind(
            ContributorRequest::class,
            ResourceResolver::request('contributor')
        );
        $this->app->bind(
            ContributorStateProcessor::class,
            ResourceResolver::stateProcessor('contributor')
        );

        $this->app->bind(
            Role::class,
            ResourceResolver::model('role')
        );
        $this->app->bind(
            RoleController::class,
            ResourceResolver::controller('role')
        );
        $this->app->bind(
            RoleRepository::class,
            ResourceResolver::repository('role')
        );
        $this->app->bind(
            RoleProvider::class,
            ResourceResolver::provider('role')
        );
        $this->app->bind(
            RoleRequest::class,
            ResourceResolver::request('role')
        );
        $this->app->bind(
            RoleStateProcessor::class,
            ResourceResolver::stateProcessor('role')
        );

        $this->app->bind(
            Permission::class,
            ResourceResolver::model('permission')
        );
        $this->app->bind(
            PermissionController::class,
            ResourceResolver::controller('permission')
        );
        $this->app->bind(
            PermissionRepository::class,
            ResourceResolver::repository('permission')
        );
        $this->app->bind(
            PermissionProvider::class,
            ResourceResolver::provider('permission')
        );
        $this->app->bind(
            PermissionRequest::class,
            ResourceResolver::request('permission')
        );
        $this->app->bind(
            PermissionStateProcessor::class,
            ResourceResolver::stateProcessor('permission')
        );
    }
}
