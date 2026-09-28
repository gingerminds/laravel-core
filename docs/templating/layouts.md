# Layouts

All admin pages extend one of the layouts below. Pick based on what the page does, not by copy-pasting the closest existing page.

```
layouts.master-without-nav          (guest pages: login)
layouts.master                      (base authenticated shell: sidebar + header + footer)
 ├── layouts.crud.list              (paginated list with search/filters)
 ├── layouts.crud.list-tree         (hierarchical list with drag & drop)
 ├── layouts.crud.form              (create/edit form)
 │    └── layouts.crud.form-tabs    (tabbed create/edit form)
 └── layouts.crud.show              (bare content wrapper for custom detail pages)
```

## `master-without-nav`

Guest layout (no sidebar, no header). Used by the login page. Use it for any page that must be reachable without an authenticated session.

## `master`

The authenticated shell: sidebar, top area, footer, flash-message alerts, and a `content` section. You'll rarely extend it directly — extend one of the `crud.*` layouts below instead, which already extend it for you.

Sections/stacks it exposes:

| Section / stack | Purpose |
|---|---|
| `@yield('title')` | `<title>` tag |
| hreflang tags | `<link rel="alternate" hreflang>` rendered in `<head>` — see [Configuration → Hreflang](../Configuration.md#hreflang-multilingual-sites) (also in `master-without-nav`) |
| `@stack('head')` | Pushed tags rendered inside `<head>` (e.g. `<link rel="prev/next">`, extra `<meta>`) — also exposed by `master-without-nav` |
| `@yield('breadcrumb')` | Breadcrumb, typically `<x-gingerminds-core::navigation.breadcrumb>` |
| `@yield('content')` | Main content |
| `@stack('modals')` | Pushed modals (e.g. delete confirmation) rendered at the end of `<body>` |
| `@stack('scripts')` | Pushed page-specific `<script>` tags |

## `crud.list`

For any paginated/filterable index page. Extends `master` and renders, in order: a collapsible filters panel (only if the model implements `SearchableModelInterface` and/or `FilterableModelInterface` — see [Filters](../partials/filters.md)), a card with a sortable `<table>`, and pagination.

Minimal usage (see `pages/roles/index.blade.php` for a full example):

```blade
@extends('gingerminds-core::layouts.crud.list')

@php
    $filters = request()->get('filters', []);
    $indexRoute = 'gingerminds-core.roles.index';
    $columns = [
        ['name' => '#', 'sortable' => false, 'align' => 'center'],
        ['name' => __('...name_s'), 'sortable' => true, 'property' => 'name'],
        ['name' => __('...actions'), 'sortable' => false],
    ];
    $sortBy = request()->query('sortBy');
    $sortOrder = request()->query('sort');
@endphp

@section('title')...@endsection
@section('breadcrumb')...@endsection
@section('actions')
    {{-- "create" button, rendered next to the title --}}
@endsection

@section('table_list')
    @include('your-package::pages.roles.partials.list')
@endsection

@push('modals')
    <x-gingerminds-core::modal.modal-delete :model="..." routing="roles"/>
@endpush
```

Key variables the layout expects: `$items` (paginator or collection), `$columns` (array of `['name', 'sortable', 'property'?, 'align'?]`), `$sortBy` / `$sortOrder`, and optionally `$indexRoute` / `$filters` to enable the filters panel. Column click-sorting only works if the model implements `SortableModelInterface` — see [Sorting](../Sorting.md).

You can fully replace the table by defining `@section('table')` instead of just `table_list`.

When `$items` is a paginator and `$isPaginationDisabled` is not set, the layout also pushes `<link rel="prev">` / `<link rel="next">` onto the `head` stack (partial `components.list.pagination-head-links`). To add them to a custom paginated page, push the same partial yourself:

```blade
@push('head')
    @include('gingerminds-core::components.list.pagination-head-links', ['items' => $items])
@endpush
```

## `crud.list-tree`

Same header/actions/filters chrome as `crud.list`, but renders a `@yield('tree')` section instead of a table, and ships the Sortable.js wiring for drag & drop. Use it for hierarchical resources (categories, menus...). Full pattern documented in [Sorting → drag & drop](../Sorting.md#2-drag--drop-reordering-crudlist-tree-layout).

## `crud.form`

For create/edit pages. Renders the `<form>` tag (method spoofing included), a save button, and an optional cancel link back to `$indexRoute`.

```blade
@extends('gingerminds-core::layouts.crud.form')

@php
    $action = route('gingerminds-core.roles.store');
    $indexRoute = route('gingerminds-core.roles.index');
    $method = 'POST';
    $id = 'create-roles-form';
@endphp

@section('title')...@endsection
@section('breadcrumb')...@endsection

@section('fields')
    @include('your-package::pages.roles.partials.fields')
@endsection
```

Variables: `$action`, `$method` (`POST`/`PUT`/`PATCH`), `$id`, and optionally `$indexRoute` (hides the cancel button if omitted) and `$isDisabled` (disables the submit button). Put your `<x-form.inputs.*>` fields inside `@section('fields')` — see [Forms](forms.md) for the available field components.

Optional sections: `subheader`, `actions` (nav pills, top-right), `additional-infos` (rendered below the form).

## `crud.form-tabs`

A thin wrapper around `crud.form` for tabbed forms: it defines `form-nav` (the tab headers) and wraps `fields` in a `.tab-content` div for you.

```blade
@extends('gingerminds-core::layouts.crud.form-tabs')

@section('tabs')
    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#general">General</button>
@endsection

@section('tab-content')
    <div class="tab-pane fade show active" id="general">
        @include('...partials.fields-general')
    </div>
@endsection
```

## `crud.show`

The bare minimum: extends `master` and yields `sub_content`. Use it when a detail/show page doesn't fit the form or list mold and you want the authenticated shell without any built-in scaffolding.

## See also

- [Forms](forms.md) — form field components to use inside `crud.form`.
- [Filters](../partials/filters.md) — enabling the filters panel in `crud.list`.
- [Sorting](../Sorting.md) — column sorting and drag & drop reordering.
