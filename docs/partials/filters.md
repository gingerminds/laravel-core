# Filters

Filters power the collapsible panel on `crud.list` [pages](../templating/layouts.md#crudlist). To add one, implement `Gingerminds\LaravelCore\Models\FilterableModelInterface` on your model and add a static `getFilters(): array` method (see [Resource Model](../ResourceModel.md#optional-interfaces)). Each key of the returned array is a request/query parameter name; the value configures how it's rendered and applied.

## Date filter

```php
public static function getFilters(): array
{
    return [
        'date_property' => [ // Ex: updated_at
            'type' => 'date',
            'label' => 'your_label_translation_key',
            'disabled_for_back' => true, // this option is optional, by default is true,
        ],
    ];
}
```

This will automatically add two fields into crud filters `date_property.from` and `date_property.to`.

## Number filter

```php
public static function getFilters(): array
{
    return [
        'number_property' => [ // Ex: cost
            'type' => 'number',
            'label' => 'your_label_translation_key',
            'disabled_for_back' => true, // this option is optional, by default is true,
        ],
    ];
}
```

This will automatically add two fields into crud filters `number_property.from` and `number_property.to`.

## Boolean filter

```php
public static function getFilters(): array
{
    return [
        'boolean_property' => [ //Ex: is_default
            'type' => 'boolean',
            'label' => 'your_label_translation_key',
            'disabled_for_back' => true, // this option is optional, by default is true,
        ],
    ];
}
```

This will automatically add a select field into crud filters `boolean_property`.

## Select filters

### Select filter

```php
public static function getFilters(): array
{
    return [
        'select_state_property' => [
            'type'    => 'select',
            'label'   => 'your_label_translation_key',
            'choices' => [
                    0 => 'translation.choice.0',
                    1 => 'translation.choice.1',
                    2 => 'translation.choice.2',
                    3 => 'translation.choice.3',
                    4 => 'translation.choice.4',
                ],
            'multiple' => true, // this option is optional, by default if not set it's false
            'disabled_for_back' => true, // this option is optional, by default is true,
        ],
    ];
}
```

### Select state filter

Use this for a property backed by a [`spatie/laravel-model-states`](https://spatie.be/docs/laravel-model-states) cast (e.g. `protected $casts = ['status' => StatusState::class];`, where `StatusState extends \Spatie\ModelStates\State`).

```php
public static function getFilters(): array
{
    $stateChoices = [];

    foreach (StatusState::getStateMapping() as $state) {
        $stateChoices[$state::code()] = 'translation.models.states.' . $state::code(); // translation key will be different between your cases
    }

    return [
        'select_state_property' => [ // Ex: status
            'type'    => 'select-state',
            'label'   => 'your_label_translation_key',
            'choices' => $stateChoices,
            'multiple' => true, // this option is optional, by default if not set it's false
            'disabled_for_back' => true, // this option is optional, by default is true,
        ],
    ];
}
```

This will automatically add a select field into crud filters `select_state_property`, rendered the same way as a plain `select` filter. On the API side, send the same value(s) you used as `choices` keys above: `filters[select_state_property]=draft` for a single value, or `filters[select_state_property][]=draft&filters[select_state_property][]=archived` when `multiple` is `true`. Send `all` (single-value only) to clear the filter.

The repository resolves each submitted value against every state registered on the property's base state class (`AbstractRepository::convertState()`), matching — in order — its `code()`, its `label()`, its morph class, or its short class name (case-insensitively), and converts it to the fully-qualified state class actually stored in the column before querying. If a value can't be resolved this way, it's used as-is (so an already-qualified state class name still works). This requires the model's cast for that property to resolve to a class extending `Spatie\ModelStates\State` (declared either via the `$casts` property or a `casts()` method) — otherwise the value is passed through unconverted.

### Select Model filter

```php
public static function getFilters(): array
{
    return [
        'model_property' => [ // Ex: model_id
            'type'    => 'select-model',
            'label'   => 'your_label_translation_key',
            'model'   => Model::class, 
            'multiple' => true, // this option is optional, by default if not set it's false
            'disabled_for_back' => true, // this option is optional, by default is true,
        ],
    ];
}
```

This will automatically add a select field into crud filters `model_property`, backed by a `SelectModel` Livewire component that queries `model` directly. If `multiple` is set to `true`, the select also enables search.

## Custom filter types

Each filter `type` (`date`, `number`, `select`, `select-model`, `select-state`, `boolean`) is applied by a `Gingerminds\LaravelCore\Repositories\Filters\FilterHandlerInterface` implementation, looked up by type from `Gingerminds\LaravelCore\Repositories\Filters\FilterHandlerRegistry` (bound as a singleton). Any package — not just `gingerminds-laravel-core` — can register its own `type` without modifying this package:

```php
use Gingerminds\LaravelCore\Repositories\Filters\FilterHandlerInterface;
use Gingerminds\LaravelCore\Repositories\Filters\FilterHandlerRegistry;
use Illuminate\Database\Eloquent\Builder;

class MyCustomFilterHandler implements FilterHandlerInterface
{
    public function apply(Builder $query, string $property, mixed $value): void
    {
        // build your query constraint here
    }
}

// From your own package's ServiceProvider::register() or boot():
$this->app->make(FilterHandlerRegistry::class)->register('my-custom-type', new MyCustomFilterHandler());
```

Once registered, `'my-custom-type'` can be used as a `getFilters()` entry's `type` like any built-in one, and `AbstractRepository::applyFilters()` picks it up automatically. An unregistered type is silently ignored (no filter applied), same as an unrecognized value previously fell through the old hardcoded `match`.

## See also

- [Resource Model](../ResourceModel.md#optional-interfaces) — where `FilterableModelInterface` fits among the other optional model interfaces.
- [Layouts](../templating/layouts.md#crudlist) — how the filters panel is rendered on list pages.
- [Sorting](../Sorting.md) — column sorting, the other list-refinement mechanism.
- [Facets](facets.md) — computing per-filter option counts/bounds (e.g. "Concerts (12)") behind a faceted-search collection endpoint, built on top of `getFilters()`.
