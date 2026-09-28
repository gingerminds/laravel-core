<?php

declare(strict_types=1);

namespace Gingerminds\LaravelCore\Seo;

use Illuminate\Http\Request;
use Illuminate\View\View;

class HreflangViewComposer
{
    public function __construct(
        private readonly AlternateUrlResolverInterface $resolver,
        private readonly Request $request,
    ) {
    }

    public function compose(View $view): void
    {
        $view->with(
            'hreflangAlternates',
            $view->getData()['alternateUrls'] ?? $this->resolver->resolve($this->request)
        );
    }
}
