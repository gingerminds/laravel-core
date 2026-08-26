<?php

declare(strict_types=1);

namespace Gingerminds\LaravelCore\Serializer\ContextBuilder;

use ApiPlatform\State\SerializerContextBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

/**
 * `ApiPlatform\Laravel\Eloquent\Serializer\SerializerContextBuilder` fills
 * `AbstractNormalizer::ATTRIBUTES` with the requested resource's own flat
 * property list whenever no explicit `?properties[]=` filter was given. That
 * flat whitelist then leaks into every embedded resource property — e.g. a
 * Product exposing a `news` collection of News resources: Symfony's
 * `AbstractNormalizer::isAllowedAttribute()` checks each nested News
 * property against it, so a News field survives only if its name happens to
 * also be a Product property (`id`, `code`, `slug`), while every other News
 * field (`title`, `published_at`, `thumbnail`, `status`, ...) is silently
 * dropped despite carrying the correct serialization Groups. Removing the
 * auto-filled ATTRIBUTES key restores normal Group-based filtering, which
 * resolves embedded resources correctly.
 */
final class EmbeddedResourceAttributesFixContextBuilder implements SerializerContextBuilderInterface
{
    public function __construct(
        private readonly SerializerContextBuilderInterface $decorated,
    ) {
    }

    public function createFromRequest(Request $request, bool $normalization, ?array $extractedAttributes = null): array
    {
        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);

        if (!$request->query->has('properties')) {
            unset($context[AbstractNormalizer::ATTRIBUTES]);
        }

        return $context;
    }
}
