@props([
    'id',
    'label',
    'size' => null,
    'required' => true,
    'multiple' => false,
    'search' => false,
    'disabled' => false,
    'helper' => null,
    'ajaxUrl' => null,
    'maxSelection' => null,
])

@php
    $sizeClass = match ($size) {
        'tiny' => 'col-md-2 col-sm-12',
        'sm' => 'col-md-4 col-sm-12',
        'lg' => 'col-md-8 col-sm-12',
        'xl' => 'col-md-12',
        default => 'col-md-6 col-sm-12'
    };
@endphp

<div class="{{ $sizeClass }}">
    <label for="{{ $id }}" class="form-label">{{ $label }} @if($required) <span
                class="text-danger">*</span>@endif</label>
    <select
            name="{{ $id }}"
            id="{{ $id }}"
            class="form-select select2 @error($id) is-invalid @enderror @if($search || $ajaxUrl) select2-search @endif"
            @if($ajaxUrl) data-ajax-url="{{ $ajaxUrl }}" @endif
            @if($maxSelection) data-max-selection="{{ $maxSelection }}" @endif
            @if($required) required @endif
            @if($multiple) multiple @endif
            @if($disabled) disabled @endif
            {{ $attributes }}
    >
        {{ $slot }}
    </select>
    @if(isset($helper))
        <div class="form-text" id="basic-addon4">{{ $helper }}</div>
    @endif
    @error($id)
    <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
