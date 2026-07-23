<?php

declare(strict_types=1);

namespace Gingerminds\LaravelCore\Models\Trait;

use Gingerminds\LaravelCore\Models\EagerLoadableModelInterface;
use Illuminate\Database\Eloquent\Builder;

/**
 * Applies `EagerLoadableModelInterface::getEagerLoads()` to Laravel's
 * implicit route-model binding (`Route::resource()`'s `edit(Product $product)`
 * etc.).
 *
 * `AbstractRepository::get()` already merges `getEagerLoads()` in for every
 * listing and every API single-item GET — but admin "edit" pages are plain
 * `Route::resource()` controllers resolved through implicit route-model
 * binding, which never goes through the repository at all. Without this,
 * every relation declared in `getEagerLoads()` still lazy-loads (or, with
 * `Model::preventLazyLoading()` on, crashes) the moment an edit view renders.
 */
trait EagerLoadableModelTrait
{
    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function resolveRouteBindingQuery($query, $value, $field = null): Builder
    {
        $query = parent::resolveRouteBindingQuery($query, $value, $field);

        if ($this instanceof EagerLoadableModelInterface) {
            $query->with(static::getEagerLoads());
        }

        return $query;
    }
}
