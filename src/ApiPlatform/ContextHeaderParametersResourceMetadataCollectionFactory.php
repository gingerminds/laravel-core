<?php

declare(strict_types=1);

namespace Gingerminds\LaravelCore\ApiPlatform;

use ApiPlatform\Metadata\Parameters;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\Metadata\Resource\ResourceMetadataCollection;

/**
 * Adds the HeaderParameter(s) registered in ApiHeaderParameterRegistry to
 * every operation of a resource that matches one of the registry's markers,
 * so context headers (X-Site-Id, Accept-Language, X-Country-Id, ...) show up
 * in the OpenAPI/Swagger docs for exactly the resources that need them.
 */
final class ContextHeaderParametersResourceMetadataCollectionFactory implements ResourceMetadataCollectionFactoryInterface
{
    public function __construct(
        private readonly ResourceMetadataCollectionFactoryInterface $decorated,
        private readonly ApiHeaderParameterRegistry $registry,
    ) {
    }

    public function create(string $resourceClass): ResourceMetadataCollection
    {
        $resourceMetadataCollection = $this->decorated->create($resourceClass);

        $headerParameters = $this->registry->for($resourceClass);

        if ($headerParameters === []) {
            return $resourceMetadataCollection;
        }

        foreach ($resourceMetadataCollection as $i => $resource) {
            $operations = $resource->getOperations();

            if (!$operations) {
                continue;
            }

            foreach ($operations as $operationName => $operation) {
                $parameters = $operation->getParameters() ?? new Parameters();

                foreach ($headerParameters as $headerParameter) {
                    $parameters->add((string) $headerParameter->getKey(), $headerParameter);
                }

                $operations->add($operationName, $operation->withParameters($parameters));
            }

            $resourceMetadataCollection[$i] = $resource->withOperations($operations->sort());
        }

        return $resourceMetadataCollection;
    }
}
