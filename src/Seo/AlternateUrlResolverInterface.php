<?php

declare(strict_types=1);

namespace Gingerminds\LaravelCore\Seo;

use Illuminate\Http\Request;

interface AlternateUrlResolverInterface
{
    /**
     * @return array<string, string> hreflang code (e.g. "fr-FR", "x-default") => absolute URL;
     *                               empty when the page has no alternate
     */
    public function resolve(Request $request): array;
}
