# Resource Model

To create a new resource, use `php artisan make:resource Namespace/Model` (see [Commands](Commands.md) for the full list of generators and options, including `--api` for API Platform wiring and `--trad-base` for a custom translation key).

To work with API Platform, the model, repository and form request all need to follow the structure below.

## Base model structure

```php
use Illuminate\Database\Eloquent\Model;
use Gingerminds\LaravelCore\Models\ResourceModelInterface;

class Model extends Model implements ResourceModelInterface
{
    protected $fillable = [];
}
```

You can create a bare model with `php artisan make:model Namespace/Model` (the package registers a custom stub, so this already implements `ResourceModelInterface`).

### Optional interfaces

All of these live under `Gingerminds\LaravelCore\Models`.

| Interface | Effect |
|---|---|
| `SortableModelInterface` | Enables column-header sorting (`?sortBy=&sort=`) on list pages. See [Sorting](Sorting.md). |
| `SearchableModelInterface` | Enables the free-text search box on list pages. Requires a `getSearchableFields(): array` method. |
| `FilterableModelInterface` | Enables the filters panel on list pages. Requires a `getFilters(): array` method — see [Filters](partials/filters.md). |
| `CacheableResourceInterface` | Enables query caching for this resource — use it when the resource is actually served/listed through `AbstractRepository`. Requires `getCacheKey(): string` (`use CacheableResourceTrait` to get a sane default for `getCacheTtlSeconds()`). On save/restore the item cache is invalidated then repopulated precisely; on delete it's simply removed; the listing cache is always invalidated (never pre-warmed). See [Cache System](./CacheSystemRedesign.md). |
| `CacheCascadeInterface` | For models that are *not* independently served (translation rows, child rows) but must still invalidate a parent resource's cache when they change. Requires `getCascadeCacheKeys(): array` — the parent's `getCacheKey()` value(s). Independent of `CacheableResourceInterface`: implement only this one if the model has no cache of its own. |
| `EagerLoadableModelInterface` | Declares relations (`getEagerLoads(): array`) that `AbstractRepository` always preloads for this resource, whether or not it's also cacheable — fixes N+1 queries at the source instead of relying on callers to pass `$with`. |

## Base repository structure

```php
use Gingerminds\LaravelCore\Http\Requests\FormRequestInterface;
use Gingerminds\LaravelCore\Models\ResourceModelInterface;
use Gingerminds\LaravelCore\Repositories\AbstractRepository;
use App\Models\Model;
use InvalidArgumentException;

/**
 * @extends AbstractRepository<Model>
 */
class ModelRepository extends AbstractRepository
{
    public function getModelClass(): string
    {
        return Model::class;
    }

    public function update(
        ?FormRequestInterface $request,
        ResourceModelInterface $resourceModel
    ): ResourceModelInterface {
        if (!$resourceModel instanceof Model) {
            throw new InvalidArgumentException('ResourceModelInterface must be an instance of Model');
        }

        if (!$request instanceof FormRequestInterface) {
            return $resourceModel;
        }

        $resourceModel->fill($request->all());
        $resourceModel->save();

        return $resourceModel;
    }
}
```

`getModelClass()` and `update()` are the two methods `AbstractRepository` doesn't implement for you — everything else (pagination, caching, sorting, filtering) is handled by the base class. Generate the scaffold with `php artisan make:repository Namespace/Model`.

## Base form request structure

```php
use Illuminate\Foundation\Http\FormRequest;
use Gingerminds\LaravelCore\Http\Requests\FormRequestInterface;

class ModelRequest extends FormRequest implements FormRequestInterface
{
    public function rules(): array
    {
        return [];
    }
}
```

Generate it with `php artisan make:form-request Namespace/Model`.

## Base controller structure

Generated controllers implement `index`, `create`, `edit`, `store`, `update`, `destroy` (no `show` — `edit` is used as the detail page), wired to the model's `FormRequest` and repository.

```bash
php artisan make:controller-full Namespace/Model [--trad-base=]
```

## See also

- [Commands](Commands.md) — every generator, including the all-in-one `make:resource`.
- [Configuration](Configuration.md) — registering the resource (and overriding a built-in one) in `config/gingerminds-core.php`.
- [API](API.md) — exposing the resource through API Platform.
- [Layouts](templating/layouts.md) and [Forms](templating/forms.md) — building the Blade views.
