@props([
    'route' => null,
    'icon' => null,
    'label' => null,
    'name' => null,
    'items' => null,
    'id' => null,
    'expanded' => false,
    'permission' => null,
])

@php
    $label = $label ?? $name;

    // Filter items based on permissions
    $filteredItems = [];
    if ($items) {
        foreach ($items as $item) {
            if (!isset($item['permission']) || auth()->user()?->can($item['permission'])) {
                $filteredItems[] = $item;
            }
        }
    }

    // Check if current item or any sub-item is active
    $isActive = false;

    if ($route && is_url_active($route)) {
        $isActive = true;
    } elseif ($filteredItems) {
        foreach ($filteredItems as $item) {
            if (isset($item['route']) && is_url_active($item['route'])) {
                $isActive = true;
                $expanded = true; // Auto-expand if a sub-item is active
                break;
            }
        }
    }

    // Determine if the main item should be displayed
    $shouldDisplay = (!$permission || auth()->user()?->can($permission));
    if ($items && empty($filteredItems)) {
        $shouldDisplay = false;
    }
@endphp

@if($shouldDisplay)
    @if($items)
        <li class="sidebar-card-group">
            <button class="btn btn-toggle d-flex align-items-center rounded border-0 {{ $expanded ? '' : 'collapsed' }} {{ $isActive ? 'active' : '' }}" data-bs-toggle="collapse"
                    data-bs-target="#{{ $id }}" aria-expanded="{{ $expanded ? 'true' : 'false' }}">
                <i class="{{ $icon }} me-2"></i>
                <span class="label-text">{{ $label }}</span>
            </button>
            <div class="collapse {{ $expanded ? 'show' : '' }}" id="{{ $id }}" data-bs-parent="#sidebarMenuAccordion">
                <ul class="btn-toggle-nav list-unstyled fw-normal pb-1">
                    @foreach($filteredItems as $item)
                        <li>
                            <a href="{{ $item['route'] }}" class="d-flex text-decoration-none rounded align-items-center {{ is_url_active($item['route']) ? 'active' : '' }}">
                                @if(isset($item['icon']))
                                    <i class="{{ $item['icon'] }} me-2"></i>
                                @endif
                                {{ $item['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </li>
    @else
        <li class="sidebar-card-group">
            <a href="{{ $route }}" class="btn btn-single rounded border-0 d-flex align-items-center {{ $isActive ? 'active' : '' }}">
                <i class="{{ $icon }} me-2"></i>
                <span class="label-text">{{ $label }}</span>
            </a>
        </li>
    @endif
@endif
