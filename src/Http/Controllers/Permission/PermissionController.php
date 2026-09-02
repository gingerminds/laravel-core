<?php

namespace Gingerminds\LaravelCore\Http\Controllers\Permission;

use Gingerminds\LaravelCore\Http\Controllers\AbstractController as Controller;
use Gingerminds\LaravelCore\Http\Requests\Permission\PermissionRequest;
use Gingerminds\LaravelCore\Models\Permission\Permission;
use Gingerminds\LaravelCore\Repositories\Permission\PermissionRepository;
use Gingerminds\LaravelCore\Resolver\ResourceResolver;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public const string LABEL_S = 'gingerminds-core::translation.permissions.name_s';

    private PermissionRepository $permissionRepository;

    public function __construct()
    {
        $this->permissionRepository = new PermissionRepository();
    }

    public function index(Request $request): Factory|View
    {
        $this->authorize('viewAny', ResourceResolver::model('permission'));

        $permissions = $this->permissionRepository->get($request);

        /** @var view-string $view */
        $view = 'gingerminds-core::pages.permissions.index';

        return view($view, [
            'resource' => ResourceResolver::model('permission'),
            'items'    => $permissions,
        ]);
    }

    public function create(): View
    {
        /** @var view-string $view */
        $view = 'gingerminds-core::pages.permissions.create';

        return view($view);
    }

    public function edit(Permission $permission): View
    {
        /** @var view-string $view */
        $view = 'gingerminds-core::pages.permissions.edit';

        return view($view, ['permission' => $permission]);
    }

    public function store(PermissionRequest $request): RedirectResponse
    {
        $this->authorize('create', ResourceResolver::model('permission'));

        $request->validated();

        $permission = new Permission();
        $this->permissionRepository->update($request, $permission);

        return redirect()
            ->route('gingerminds-core.permissions.index')
            ->with(
                'success',
                __(
                    'gingerminds-core::translation.successfully_created',
                    ['model' => __(self::LABEL_S) . ' ' . $permission->name]
                )
            );
    }

    public function update(PermissionRequest $request, Permission $permission): RedirectResponse
    {
        $this->authorize('update', $permission);

        $request->validated();

        $this->permissionRepository->update($request, $permission);

        return redirect()
            ->route('gingerminds-core.permissions.edit', $permission->id)
            ->with('success', __(
                'gingerminds-core::translation.successfully_updated',
                [
                    'model' => __(self::LABEL_S) . ' ' . $permission->name,
                ]
            ));
    }

    public function destroy(Permission $permission): RedirectResponse
    {
        $this->authorize('delete', $permission);

        $permission->delete();

        return redirect()
            ->route('gingerminds-core.permissions.index')
            ->with(
                'success',
                __(
                    'gingerminds-core::translation.successfully_deleted',
                    ['model' => __(self::LABEL_S) . ' ' . $permission->name]
                )
            );
    }
}
