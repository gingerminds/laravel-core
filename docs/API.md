# API

The API layer runs on API Platform 4.

## Operations configuration

Laravel needs a bit of glue to work with API Platform's IRI-based model and our own repository logic, using plain IDs instead. Three pieces are involved:

- A **state processor** — maps requests from API Platform to our repositories (writes).
- An **API provider** — maps our repositories to API Platform (reads).
- **Model configuration** — wires the two above to the `#[ApiResource]` attribute.

### StateProcessor configuration

Extends `BaseStateProcessor` and only needs to set the resource's repository, form request, and model. Base model/repository/request structure is covered in [Resource Model](ResourceModel.md).

```php
use ApiPlatform\State\ProcessorInterface;
use App\Http\Requests\Model\ModelRequest;
use App\Models\Model\Model;
use App\Repositories\Model\ModelRepository;
use Gingerminds\LaravelCore\StateProcessor\BaseStateProcessor;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Http\Request;

/**
 * @implements ProcessorInterface<Model, Model>
 */
class ModelStateProcessor extends BaseStateProcessor implements ProcessorInterface
{
    public function __construct(
        ModelRepository $repository,
        Request $request,
        ValidationFactory $validationFactory
    ) {
        $this->repository = $repository;
        $this->formRequest = new ModelRequest();
        $this->resourceModel = new Model();

        parent::__construct($request, $validationFactory);
    }
}
```

Generate the scaffold with `php artisan make:state-processor Namespace/Model` (see [Commands](Commands.md)).

### API Provider configuration

Centralizes the read logic shared between the admin panel and the API.

```php
use ApiPlatform\State\ProviderInterface;
use App\Models\Model\Model;
use App\Repositories\Model\ModelRepository;
use Gingerminds\LaravelCore\ApiProvider\AbstractApiProvider;
use Gingerminds\LaravelCore\ApiProvider\ApiProviderInterface;

/**
 * @implements ProviderInterface<Model>
 */
class ModelProvider extends AbstractApiProvider implements ProviderInterface, ApiProviderInterface
{
    public function __construct(ModelRepository $repository)
    {
        parent::__construct($repository);
    }
}
```

Generate the scaffold with `php artisan make:api-provider Namespace/Model`.

#### Mapping URI variables to filters

For nested endpoints such as `/api/properties/{property}/{id}`, you don't need to handle `{id}` — `AbstractApiProvider` and the repository already do. But `{property}` needs to be turned into a filter:

```php
public function addFilters(Request $request, array $uriVariables, array $context): void
{
    $filters    = $request->query('filters', []);
    $propertyId = $uriVariables['property'] ?? $request->route('property');

    if ($propertyId) {
        $filters['property_id'] = $propertyId;
    }

    $request->query->set('filters', $filters);
}
```

You then need to enable that filter for the resource — see [Filters](partials/filters.md). If the resource has no filters or contextual/nested property, you don't need to do anything.

### Model configuration

To work with the state processor/provider above, the model needs to:

- Disable API Platform's deserialization (so it doesn't try to turn sub-resources into IRIs itself).
- Point each write operation at the state processor.
- Point each operation at the API provider.

```php
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\ApiProvider\Model\ModelProvider;
use App\StateProcessor\Model\ModelStateProcessor;
use Gingerminds\LaravelCore\Models\ResourceModelInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

#[ApiResource(
    operations: [
        new GetCollection(
            provider: ModelProvider::class
        ),
        new Get(
            provider: ModelProvider::class
        ),
        new Post(
            deserialize: false,
            provider: ModelProvider::class,
            processor: ModelStateProcessor::class
        ),
        new Patch(
            deserialize: false,
            provider: ModelProvider::class,
            processor: ModelStateProcessor::class
        ),
    ],
)]
class Model extends Model implements ResourceModelInterface
{
    use HasFactory;

    protected $fillable = [];
}
```

`make:resource --api` (see [Commands](Commands.md)) scaffolds this whole chain — model, repository, form request, state processor and API provider — in one go.

### Overriding a package model's `#[ApiResource]`

Some package models (e.g. `Gingerminds\LaravelCore\Models\User\User`) ship with `#[ApiResource]` already declared, ready to use out of the box. A consuming project sometimes needs different operations for that same resource — for instance, exposing `User` as read-only and dropping `Post`/`Patch`/`Delete`.

Per the project convention, this must never be done by editing the package's model. It's also not something you can do by simply extending the model and adding a bit more: **PHP attributes are not inherited**. `ReflectionClass::getAttributes()` only ever returns what's declared directly on the exact class being reflected, never on its parent. So a project subclass that redeclares `#[ApiResource]` isn't "patching" the parent's attribute — it's declaring a second, entirely separate one. Since API Platform scans both the package's `Models/` directory and the project's `app/Models` (see `config/api-platform.php`), that would normally register *both* classes as competing resources instead of one overriding the other.

To make this a real override, `LaravelCoreServiceProvider` decorates `ResourceNameCollectionFactoryInterface` with `Gingerminds\LaravelCore\ApiPlatform\ClassHierarchyResourceNameCollectionFactory`: whenever a scanned class is a strict ancestor of another scanned class that also carries `#[ApiResource]`, the ancestor is dropped from the resource collection. Concretely: as soon as a project subclass declares its own `#[ApiResource]`, it wins outright and the package's base model stops being registered as a resource at all — no route conflict, no duplicate.

This is a **full replacement, not a merge**. Since none of it is inherited, the subclass must restate everything it wants to keep — operations, `provider`/`processor`, contexts — as well as any class-level `#[ApiProperty]` needed for serialization groups (those aren't inherited either). Only list what the override actually needs; don't copy properties/groups tied to operations you're dropping (e.g. no need for `password`/`GROUP_EDIT` groups on a read-only override).

**Check per-operation tuning before overriding, not just `operations`/`provider`/`processor`.** Anything set on an individual `Get`/`GetCollection`/... in the package (`paginationItemsPerPage`, `paginationMaximumItemsPerPage`, `paginationClientItemsPerPage`, filters, `security`, custom `middleware`, ...) is just as easy to silently drop as a serialization group, and much easier to miss in review since nothing errors — the endpoint keeps working, just with the framework's defaults instead of the package's deliberate ones. This has already happened in practice: `laravel-media-manager`'s `Media`/`MediaCategory` both set `paginationClientItemsPerPage` (and `MediaCategory` a 200/500 page-size ceiling) on their `GetCollection`, and a project override of both had silently dropped it. Diff the operation you're overriding, not just skim it.

```php
namespace App\Models\User;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Gingerminds\LaravelCore\ApiProvider\User\UserProvider;
use Gingerminds\LaravelCore\Models\User\User as CoreUser;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new GetCollection(
            normalizationContext: ['groups' => [User::GROUP_LIST]],
            provider: UserProvider::class,
        ),
        new Get(
            normalizationContext: ['groups' => [User::GROUP_READ]],
            provider: UserProvider::class,
        ),
        // no Post/Patch/Delete: this project never creates/edits/deletes users via the API
    ],
    middleware: ['auth:sanctum']
)]
#[ApiProperty(property: 'email', serialize: new Groups([
    User::GROUP_LIST,
    User::GROUP_READ,
]))]
class User extends CoreUser
{
    // …
}
```

This works out of the box for models from any of the gingerminds packages (multisite, media-manager, cms), not just laravel-core — they all depend on `gingerminds/laravel-core`, so its service provider (and this decorator) is always registered once a project uses any of them. Nothing needs to be repeated per package.

### Documenting context headers (`X-Site-Id`, `Accept-Language`, ...)

Some resources only make sense in the context of a request header — e.g. any model scoped by site, language or a project-specific context needs that header documented in the OpenAPI/Swagger docs, on every operation, without hand-declaring `#[HeaderParameter]` on each one (itself a class-level attribute, so it isn't inherited across an override the way operations aren't — see above).

`Gingerminds\LaravelCore\ApiPlatform\ApiHeaderParameterRegistry` maps a **marker** — a trait or interface FQCN a model uses/implements — to the `HeaderParameter` that should document it. `Gingerminds\LaravelCore\ApiPlatform\ContextHeaderParametersResourceMetadataCollectionFactory` (also decorating `ResourceMetadataCollectionFactoryInterface`) reads this registry for every resource class and injects the matching `HeaderParameter`(s) into all of its operations — checking the full class hierarchy (`class_uses_recursive()`), so it works whether the trait is used on the resource class itself or on a package model it extends.

The registry is bound as an empty singleton by this package — core has no notion of "site" or "language". Whoever owns a context registers its own marker from their own service provider's `boot()` (not `register()` — the registry's binding must already exist, which is only guaranteed once every provider's `register()` phase has run):

```php
// e.g. LaravelMultisiteServiceProvider::boot()
$this->app->make(ApiHeaderParameterRegistry::class)->register(
    SiteContextedModelTrait::class,
    new HeaderParameter(
        key: 'X-Site-Id',
        description: 'Restricts the response to the given site.',
        schema: ['type' => 'string'],
    )
);
```

A project can register its own project-specific context (e.g. a `Country`) the exact same way, from its own `AppServiceProvider::boot()`, without needing a package to own it.

## See also

- [Resource Model](ResourceModel.md) — the model/repository/request contract these classes build on.
- [Configuration](Configuration.md) — registering the resource in `config/gingerminds-core.php` so it can also be resolved dynamically (e.g. by `ResourceResolver`).
- [Filters](partials/filters.md) — enabling filters consumed by both the admin panel and the API.
