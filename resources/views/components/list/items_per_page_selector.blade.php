@php
    $availableOptions = [10, 25, 50, 100];
    $selectedOption = (int) request()->query('itemsPerPage', $itemsPerPage ?? 10);
@endphp

<div class="col-md-12 d-flex justify-content-end">
    <select id="items_per_page" name="items_per_page" class="form-select w-fit-content">
        @foreach($availableOptions as $option)
            <option value="{{ $option }}" {{ $option === $selectedOption ? 'selected' : '' }}>
                @lang('gingerminds-core::translation.display')&nbsp;{{ $option }}
            </option>
        @endforeach
    </select>
</div>

<script>
    document.getElementById('items_per_page').addEventListener('change', function () {
        const selected = this.value;
        const url = new URL(window.location.href);

        url.searchParams.set('itemsPerPage', selected);
        url.searchParams.delete('page'); // reset pagination when the page size changes

        window.location.href = url.toString();
    });
</script>
