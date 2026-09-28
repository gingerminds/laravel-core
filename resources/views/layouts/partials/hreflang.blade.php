{{-- Language alternates, fed by Gingerminds\LaravelCore\Seo\HreflangViewComposer --}}
@foreach ($hreflangAlternates as $hreflang => $url)
    <link rel="alternate" hreflang="{{ $hreflang }}" href="{{ $url }}">
@endforeach
