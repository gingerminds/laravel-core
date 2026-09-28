<?php

declare(strict_types=1);

namespace Gingerminds\LaravelCore\Seo;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Http\Request;

class ConfigAlternateUrlResolver implements AlternateUrlResolverInterface
{
    public function __construct(private readonly Repository $config)
    {
    }

    /**
     * @return array<string, string>
     */
    public function resolve(Request $request): array
    {
        /** @var array<string, string> $baseUrls */
        $baseUrls = array_map(
            static fn (string $url): string => rtrim($url, '/'),
            (array) $this->config->get('gingerminds-core.hreflang.locales', [])
        );

        // A lone language has nothing to point to.
        if (count($baseUrls) < 2) {
            return [];
        }

        $currentUrl  = $request->fullUrl();
        $currentBase = $this->findCurrentBaseUrl($currentUrl, $baseUrls);
        if ($currentBase === null) {
            return [];
        }

        $pathAndQuery = substr($currentUrl, strlen($currentBase));
        $alternates   = array_map(
            fn (string $baseUrl): string => $this->buildUrl($baseUrl, $pathAndQuery),
            $baseUrls
        );

        $xDefault = $this->config->get('gingerminds-core.hreflang.x_default');
        if (is_string($xDefault) && isset($baseUrls[$xDefault])) {
            $alternates['x-default'] = $this->buildUrl($baseUrls[$xDefault], $pathAndQuery);
        }

        return $alternates;
    }

    private function buildUrl(string $baseUrl, string $pathAndQuery): string
    {
        // Language root: "", "/", "?query" or "/?query" depending on the source base URL.
        $rootQuery = preg_replace('#^/(?=\?|$)#', '', $pathAndQuery) ?? $pathAndQuery;
        if ($rootQuery === '' || !str_starts_with($rootQuery, '?')) {
            return $baseUrl . $rootQuery;
        }

        $hasPath = (parse_url($baseUrl, PHP_URL_PATH) ?? '') !== '';

        return $baseUrl . ($hasPath ? '' : '/') . $rootQuery;
    }

    /**
     * @param array<string, string> $baseUrls
     */
    private function findCurrentBaseUrl(string $currentUrl, array $baseUrls): ?string
    {
        $match = null;
        foreach ($baseUrls as $baseUrl) {
            $isUnderBase = $currentUrl === $baseUrl
                || str_starts_with($currentUrl, $baseUrl . '/')
                || str_starts_with($currentUrl, $baseUrl . '?');

            if ($isUnderBase && strlen($baseUrl) > strlen($match ?? '')) {
                $match = $baseUrl;
            }
        }

        return $match;
    }
}
