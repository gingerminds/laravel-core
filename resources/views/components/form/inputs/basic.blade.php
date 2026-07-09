@props([
    'id',
    'name' => null,
    'type' => 'text',
    'label',
    'value' => null,
    'size' => null,
    'required' => false,
    'disabled' => false,
    'value' => null,
    'placeholder' => null,
    'min' => null,
    'max' => null,
    'step' => null,
    'helper' => null,
    'prefix' => null,
    'suffix' => null,
])

@php
    $fieldName = $name ?? $id;

    $sizeClass = match ($size) {
        'tiny' => 'col-md-2 col-sm-12',
        'sm'   => 'col-md-4 col-sm-12',
        'lg'   => 'col-md-8 col-sm-12',
        'xl'   => 'col-md-12',
        default => 'col-md-6 col-sm-12'
    };

    /**
     * Laravel expects dot notation for errors + old()
     */
    $errorKey = str_replace(['[', ']'], ['.', ''], $fieldName);

    /**
     * Important: old() fallback ONLY works with dot notation
     */
    $oldValue = old($errorKey, $value);

    // `prefix`/`suffix` wrap the input in a Bootstrap input-group (e.g. a
    // read-only "/news/" segment before a page's slug field) instead of
    // every call site hand-rolling its own <div class="input-group"> markup.
    $hasGroup = null !== $prefix || null !== $suffix;
@endphp

<div class="{{ $sizeClass }}">
    <label for="{{ $id }}" class="form-label">
        {{ $label }}
        @if($required)
            <span class="text-danger">*</span>
        @endif
    </label>

    @if($hasGroup)
        <div class="input-group @if($size) input-group-{{ $size }} @endif">
            @if(null !== $prefix)
                <span class="input-group-text">{{ $prefix }}</span>
            @endif
    @endif

    <input
            type="{{ $type }}"
            id="{{ $id }}"
            name="{{ $fieldName }}"
            value="{{ $oldValue }}"
            @if(isset($value))value="{{ $value }}" @endif
            @if(isset($placeholder))placeholder="{{ $placeholder }}" @endif
            @if(isset($min))min="{{ $min }}" @endif
            @if(isset($max))max="{{ $max }}" @endif
            @if(isset($step))step="{{ $step }}" @endif
            class="form-control @error($errorKey) is-invalid @enderror"
            @if($required) required @endif
            @if($disabled) disabled @endif
            {{ $attributes }}
    >

    @if($hasGroup)
            @if(null !== $suffix)
                <span class="input-group-text">{{ $suffix }}</span>
            @endif
        </div>
    @endif

    @if($helper)
        <div class="form-text">{{ $helper }}</div>
    @endif

    @error($errorKey)
    <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
