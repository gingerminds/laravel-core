<?php

namespace Gingerminds\LaravelCore\Http\Controllers\User;

use Gingerminds\LaravelCore\Http\Controllers\AbstractController as Controller;
use Gingerminds\LaravelCore\Http\Requests\User\ContributorRequest;
use Gingerminds\LaravelCore\Models\User\Contributor;
use Gingerminds\LaravelCore\Models\User\User;
use Gingerminds\LaravelCore\Repositories\User\ContributorRepository;
use Gingerminds\LaravelCore\Resolver\ResourceResolver;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ContributorController extends Controller
{
    public const string LABEL_S = 'gingerminds-core::translation.contributor.name_s';

    private ContributorRepository $contributorRepository;

    public function __construct()
    {
        $this->contributorRepository = new ContributorRepository();
    }

    public function index(Request $request): Factory|View
    {
        $this->authorize('viewAny');

        $contributors = $this->contributorRepository->get($request);

        /** @var view-string $view */
        $view = 'gingerminds-core::pages.contributors.index';

        return view($view, [
            'resource' => ResourceResolver::model('contributor'),
            'items'    => $contributors,
        ]);
    }

    public function create(): Factory|View
    {
        $this->authorize('create', ResourceResolver::model('contributor'));

        $users = User::query()
            ->select(['id','email'])
            ->orderBy('email')
            ->get();

        /** @var view-string $view */
        $view = 'gingerminds-core::pages.contributors.create';

        return view($view, [
            'users' => $users,
        ]);
    }

    public function store(ContributorRequest $request): RedirectResponse
    {
        $this->authorize('create', ResourceResolver::model('contributor'));

        $request->validated();

        /** @var Contributor $contributor */
        $contributor = $this->contributorRepository->update($request, new Contributor());

        return redirect()
            ->route('gingerminds-core.contributors.index')
            ->with(
                'success',
                __(
                    'gingerminds-core::translation.successfully_created',
                    [
                        'model' => __(self::LABEL_S)
                            . ' '
                            . $contributor->firstname
                            . ' '
                            . $contributor->lastname,
                    ]
                )
            );
    }

    public function edit(Contributor $contributor): Factory|View
    {
        $this->authorize('update', $contributor);

        $users = User::query()
            ->select(['id','email'])
            ->orderBy('email')
            ->get();

        /** @var view-string $view */
        $view = 'gingerminds-core::pages.contributors.edit';

        return view($view, [
            'contributor' => $contributor,
            'users'       => $users,
        ]);
    }

    public function update(ContributorRequest $request, Contributor $contributor): RedirectResponse
    {
        $this->authorize('update', $contributor);

        $request->validated();

        /** @var Contributor $contributor */
        $contributor = $this->contributorRepository->update($request, $contributor);

        return redirect()
            ->route('gingerminds-core.contributors.index')
            ->with(
                'success',
                __(
                    'gingerminds-core::translation.successfully_updated',
                    [
                        'model' => __(self::LABEL_S)
                            . ' '
                            . $contributor->firstname
                            . ' '
                            . $contributor->lastname,
                    ]
                )
            );
    }

    public function destroy(Contributor $contributor): RedirectResponse
    {
        $contributor->delete();

        return redirect()
            ->route('gingerminds-core.contributors.index')
            ->with(
                'success',
                __(
                    'gingerminds-core::translation.successfully_deleted',
                    [
                        'model' => __(self::LABEL_S)
                            . ' '
                            . $contributor->firstname
                            . ' '
                            . $contributor->lastname,
                    ]
                )
            );
    }
}
