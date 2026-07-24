<?php

declare(strict_types=1);

namespace Gingerminds\LaravelCore\ApiPlatform;

use ApiPlatform\Metadata\HeaderParameter;

/**
 * Maps a "context marker" (a trait or interface FQCN) to the HeaderParameter
 * API Platform should document for any resource using it — e.g. every model
 * using laravel-multisite's SiteContextedModelTrait needs `X-Site-Id`
 * documented in its Swagger operations.
 *
 * This exists so that context headers don't need `#[HeaderParameter]`
 * declared by hand on every resource that needs one (and, since it's a
 * class-level attribute, isn't inherited any more than #[ApiResource] is —
 * see ClassHierarchyResourceNameCollectionFactory). A model only needs to
 * use the trait it already uses for its context-scoping behaviour; nothing
 * else changes on the model itself.
 *
 * Bound as a singleton by LaravelCoreServiceProvider, empty by default —
 * core has no notion of "site", "language" or "country". Packages that own
 * a context (laravel-multisite for site/language) register their own
 * markers from their own service provider's boot(), and a project can
 * likewise register project-specific ones (e.g. a country context) without
 * modifying this package. See ContextHeaderParametersResourceMetadataCollectionFactory
 * for how these are applied to the OpenAPI/Swagger docs.
 */
final class ApiHeaderParameterRegistry
{
    /** @var array<class-string, HeaderParameter> */
    private array $parameters = [];

    /**
     * @param class-string $marker
     */
    public function register(string $marker, HeaderParameter $parameter): void
    {
        $this->parameters[$marker] = $parameter;
    }

    /**
     * @param string|class-string $resourceClass accepts a plain string since
     *                                            ResourceMetadataCollectionFactoryInterface::create()
     *                                            itself only guarantees `string|class-string`
     *
     * @return HeaderParameter[]
     */
    public function for(string $resourceClass): array
    {
        $traits  = class_uses_recursive($resourceClass);
        $matched = [];

        foreach ($this->parameters as $marker => $parameter) {
            $usesTrait     = isset($traits[$marker]);
            $implementsApi = interface_exists($marker) && is_a($resourceClass, $marker, true);

            if ($usesTrait || $implementsApi) {
                $matched[] = $parameter;
            }
        }

        return $matched;
    }
}
