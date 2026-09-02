<?php

namespace Gingerminds\LaravelCore\Http\Controllers\User;

use Gingerminds\LaravelCore\Http\Controllers\AbstractController as Controller;
use Gingerminds\LaravelCore\Http\Requests\User\UserProfileRequest;
use Gingerminds\LaravelCore\Http\Requests\User\UserRequest;
use Gingerminds\LaravelCore\Models\Role\Role;
use Gingerminds\LaravelCore\Models\User\Contributor;
use Gingerminds\LaravelCore\Models\User\User;
use Gingerminds\LaravelCore\Repositories\User\UserRepository;
use Gingerminds\LaravelCore\Resolver\ResourceResolver;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public const string LABEL_S = 'gingerminds-core::translation.users.name_s';

    private UserRepository $userRepository;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
    }

    public function index(Request $request): Factory|View
    {
        $this->authorize('viewAny');

        $users = $this->userRepository->get($request);
        $roles = Role::query()->orderBy('name')->get();

        /** @var view-string $view */
        $view = 'gingerminds-core::pages.users.index';

        return view($view, [
            'resource' => ResourceResolver::model('user'),
            'items'    => $users,
            'roles'    => $roles,
        ]);
    }

    public function create(): Factory|View
    {
        $this->authorize('create', ResourceResolver::model('user'));

        $roles        = Role::query()->orderBy('name')->get();
        $contributors = Contributor::query()
            ->orderBy('lastname')
            ->orderBy('firstname')
            ->get();

        /** @var view-string $view */
        $view = 'gingerminds-core::pages.users.create';

        return view($view, [
            'roles'        => $roles,
            'contributors' => $contributors,
        ]);
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $this->authorize('create', ResourceResolver::model('user'));

        $request->validated();

        // app(User::class) resolves to the project's configured model (see
        // LaravelCoreServiceProvider::bindResources()), not the hardcoded
        // core class `new User()` would give — required so Spatie's
        // syncRoles()/assignRole() persist the morph map's short alias
        // instead of falling back to the raw core FQCN.
        /** @var User $user */
        $user = $this->userRepository->update($request, app(User::class));

        return redirect()
            ->route('gingerminds-core.users.index')
            ->with('success', __('gingerminds-core::translation.successfully_created', [
                'model' => __(self::LABEL_S) . ' ' . $user->email,
            ]));
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $request->validated();

        /** @var User $user */
        $user = $this->userRepository->update($request, $user);

        return redirect()
            ->route('gingerminds-core.users.edit', $user->id)
            ->with('success', __('gingerminds-core::translation.successfully_updated', [
                'model' => __(self::LABEL_S) . ' ' . $user->email,
            ]));
    }

    public function edit(User $user): Factory|View
    {
        $this->authorize('update', $user);

        $roles        = Role::query()->orderBy('name')->get();
        $contributors = Contributor::query()
            ->orderBy('lastname')
            ->orderBy('firstname')
            ->get();

        /** @var view-string $view */
        $view = 'gingerminds-core::pages.users.edit';

        return view($view, [
            'user'         => $user->load(['roles', 'contributor']),
            'roles'        => $roles,
            'contributors' => $contributors,
        ]);
    }

    public function editProfile(): Factory|View
    {
        /** @var User $user */
        $user = Auth::user();

        /** @var view-string $view */
        $view = 'gingerminds-core::pages.users.edit-profile';

        return view($view, [
            'user' => $user->load('contributor'),
        ]);
    }

    public function updateProfile(UserProfileRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        // On injecte un contributor_id virtuel pour la logique de UserRepository
        if ($user->contributor) {
            $request->merge(['contributor_id' => $user->contributor->id]);
        } else {
            $request->merge(['contributor_id' => '__new__']);
        }

        $this->userRepository->update($request, $user);

        return redirect()
            ->route('gingerminds-core.profile.edit-profile')
            ->with('success', __('gingerminds-core::translation.successfully_updated', [
                'model' => __('gingerminds-core::translation.profile.name'),
            ]));
    }

    public function destroy(User $user): RedirectResponse
    {
        $user->delete();

        return redirect()
            ->route('gingerminds-core.users.index')
            ->with('success', __('gingerminds-core::translation.successfully_deleted', [
                'model' => __(self::LABEL_S) . ' ' . $user->email,
            ]));
    }
}
