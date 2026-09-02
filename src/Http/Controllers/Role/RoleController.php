<?php

namespace Gingerminds\LaravelCore\Http\Controllers\Role;

use Gingerminds\LaravelCore\Http\Controllers\AbstractController as Controller;
use Gingerminds\LaravelCore\Http\Requests\Role\RoleRequest;
use Gingerminds\LaravelCore\Models\Permission\Permission;
use Gingerminds\LaravelCore\Models\Role\Role;
use Gingerminds\LaravelCore\Repositories\Role\RoleRepository;
use Gingerminds\LaravelCore\Resolver\ResourceResolver;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public const string LABEL_S = 'gingerminds-core::translation.roles.name_s';

    private RoleRepository $roleRepository;

    public function __construct()
    {
        $this->roleRepository = new RoleRepository();
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', ResourceResolver::model('role'));

        $roles = $this->roleRepository->get($request);

        /** @var view-string $view */
        $view = 'gingerminds-core::pages.roles.index';

        return view($view, [
            'resource' => ResourceResolver::model('role'),
            'items'    => $roles,
        ]);
    }

    public function create(): View
    {
        $permissions = Permission::all()->groupBy(function ($permission) {
            $parts = explode(' ', $permission->name);
            return count($parts) > 1 ? $parts[1] : 'other';
        });

        /** @var view-string $view */
        $view = 'gingerminds-core::pages.roles.create';

        return view($view, [
            'permissionsGrouped' => $permissions,
        ]);
    }

    public function edit(Role $role): View
    {
        $permissions = Permission::all()->groupBy(function ($permission) {
            $parts = explode(' ', $permission->name);
            return count($parts) > 1 ? $parts[1] : 'other';
        });

        /** @var view-string $view */
        $view = 'gingerminds-core::pages.roles.edit';

        return view($view, [
            'role'               => $role,
            'permissionsGrouped' => $permissions,
        ]);
    }

    public function store(RoleRequest $request): RedirectResponse
    {
        $this->authorize('create', ResourceResolver::model('role'));

        $request->validated();

        $role = new Role();
        $this->roleRepository->update($request, $role);

        return redirect()
            ->route('gingerminds-core.roles.index')
            ->with(
                'success',
                __(
                    'gingerminds-core::translation.successfully_created',
                    ['model' => __(self::LABEL_S) . ' ' . $role->name]
                )
            );
    }

    public function update(RoleRequest $request, Role $role): RedirectResponse
    {
        $this->authorize('update', $role);

        $request->validated();

        $this->roleRepository->update($request, $role);

        return redirect()
            ->route('gingerminds-core.roles.edit', $role->id)
            ->with('success', __('gingerminds-core::translation.successfully_updated', [
                'model' => __(self::LABEL_S) . ' ' . $role->name,
            ]));
    }

    public function destroy(Role $role): RedirectResponse
    {
        $this->authorize('delete', $role);

        if ($role->name === 'Super-Admin') {
            return redirect()->back()->with('error', __('gingerminds-core::validation.superadmin_delete'));
        }
        $role->delete();

        return redirect()
            ->route('gingerminds-core.roles.index')
            ->with('success', __('gingerminds-core::translation.successfully_deleted', [
                'model' => __(self::LABEL_S) . ' ' . $role->name,
            ]));
    }
}
