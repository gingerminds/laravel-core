<?php

declare(strict_types=1);

namespace Gingerminds\LaravelCore\ApiPlatform;

use ApiPlatform\Metadata\Resource\Factory\ResourceNameCollectionFactoryInterface;
use ApiPlatform\Metadata\Resource\ResourceNameCollection;

/**
 * Lets a project fully replace a package model's #[ApiResource] instead of
 * merely adding to it.
 *
 * PHP attributes are never inherited by ReflectionClass::getAttributes(), so
 * when a package model (e.g. Gingerminds\LaravelCore\Models\User\User)
 * carries #[ApiResource] and a project subclasses it (App\Models\User\User)
 * to redeclare #[ApiResource] with a different operations list, API
 * Platform's attribute scanner has no notion of the two classes being the
 * "same" resource — it registers both, since both directories are scanned
 * (see LaravelCoreServiceProvider's `api-platform.resources` wiring) and both
 * classes carry the attribute directly. That produces two competing
 * resources instead of one overridden one.
 *
 * This decorator restores "one class, one resource" by dropping any scanned
 * class that is a strict ancestor of another scanned class also present in
 * the collection. Concretely: if only the package's base model is scanned,
 * nothing changes; the moment a project subclass also declares its own
 * #[ApiResource], the subclass wins and the package's base model is
 * excluded entirely.
 *
 * This is deliberately a full replacement, not a merge: the subclass's
 * #[ApiResource] must restate everything it wants to keep (providers,
 * contexts, the operations it keeps), not just the diff. See docs/API.md.
 */
final class ClassHierarchyResourceNameCollectionFactory implements ResourceNameCollectionFactoryInterface
{
    public function __construct(private readonly ResourceNameCollectionFactoryInterface $decorated)
    {
    }

    public function create(): ResourceNameCollection
    {
        $classes = iterator_to_array($this->decorated->create());

        /** @var array<class-string, true> $ancestors */
        $ancestors = [];
        foreach ($classes as $class) {
            $parent = get_parent_class($class);
            while ($parent !== false) {
                $ancestors[$parent] = true;
                $parent             = get_parent_class($parent);
            }
        }

        $resolved = array_values(array_filter(
            $classes,
            static fn (string $class): bool => !isset($ancestors[$class])
        ));

        return new ResourceNameCollection($resolved);
    }
}
