<?php

namespace Gingerminds\LaravelCore\Repositories\User;

use Gingerminds\LaravelCore\Http\Requests\FormRequestInterface;
use Gingerminds\LaravelCore\Models\ResourceModelInterface;
use Gingerminds\LaravelCore\Models\User\Contributor;
use Gingerminds\LaravelCore\Models\User\User;
use Gingerminds\LaravelCore\Repositories\AbstractRepository;
use Gingerminds\LaravelCore\Repositories\RepositoryInterface;
use Gingerminds\LaravelCore\Resolver\ResourceResolver;
use InvalidArgumentException;

/**
 * @extends AbstractRepository<User>
 * @implements RepositoryInterface<User>
 */
class UserRepository extends AbstractRepository implements RepositoryInterface
{
    public function getModelClass(): string
    {
        return ResourceResolver::model('user');
    }

    public function update(
        ?FormRequestInterface $request,
        ResourceModelInterface $resourceModel
    ): ResourceModelInterface {
        if (!$resourceModel instanceof User) {
            throw new InvalidArgumentException('ResourceModelInterface must be an instance of User');
        }

        if (!$request instanceof FormRequestInterface) {
            return $resourceModel;
        }

        $resourceModel->fill($request->only(['email']));
        if ($request->filled('password')) {
            $resourceModel->password = $request->input('password');
        }
        $resourceModel->save();

        if (is_array($request->input('roles'))) {
            $resourceModel->syncRoles($request->input('roles'));
        }

        if ($request->has('contributor_id')) {
            $this->handleContributorUpdate($request, $resourceModel);
        }

        return $resourceModel;
    }

    /**
     * Gère spécifiquement la logique liée au contributeur (Sépare la complexité)
     */
    protected function handleContributorUpdate(FormRequestInterface $request, User $user): void
    {
        $selector    = $request->input('contributor_id');
        $contributor = null;

        if ($selector === '__new__') {
            Contributor::query()->where('user_id', $user->id)->update(['user_id' => null]);
            $contributor = new Contributor();
        } elseif ($selector) {
            Contributor::query()
                ->where('user_id', $user->id)
                ->whereKeyNot($selector)
                ->update(['user_id' => null]);
            $contributor = Contributor::query()->find($selector);
        }

        if ($contributor instanceof Contributor) {
            $this->persistContributor($request, $contributor, $user->id);
        }
    }

    /**
     * Remplit et sauvegarde les données du contributeur
     */
    protected function persistContributor(FormRequestInterface $request, Contributor $contributor, int $userId): void
    {
        $payload = [
            'firstname' => (string) $request->input('contributor_firstname', ''),
            'lastname'  => (string) $request->input('contributor_lastname', ''),
        ];

        foreach (['trigram', 'civility'] as $field) {
            if ($request->filled("contributor_{$field}")) {
                $payload[$field] = (string) $request->input("contributor_{$field}");
            }
        }

        $contributor->fill($payload);
        $contributor->user_id = $userId;
        $contributor->save();
    }
}
