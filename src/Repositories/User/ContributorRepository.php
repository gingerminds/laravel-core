<?php

namespace Gingerminds\LaravelCore\Repositories\User;

use Gingerminds\LaravelCore\Http\Requests\FormRequestInterface;
use Gingerminds\LaravelCore\Models\ResourceModelInterface;
use Gingerminds\LaravelCore\Models\User\Contributor;
use Gingerminds\LaravelCore\Repositories\AbstractRepository;
use Gingerminds\LaravelCore\Repositories\RepositoryInterface;
use InvalidArgumentException;

/**
 * @extends AbstractRepository<Contributor>
 * @implements RepositoryInterface<Contributor>
 */
class ContributorRepository extends AbstractRepository implements RepositoryInterface
{
    protected int $perPage = 25;

    public function getModelClass(): string
    {
        return Contributor::class;
    }

    public function update(
        ?FormRequestInterface $request,
        ResourceModelInterface $resourceModel
    ): ResourceModelInterface {
        if (!$resourceModel instanceof Contributor) {
            throw new InvalidArgumentException('ResourceModelInterface must be an instance of Contributor');
        }

        if (!$request instanceof FormRequestInterface) {
            return $resourceModel;
        }

        $resourceModel->fill($request->all());
        $resourceModel->save();

        return $resourceModel;
    }
}
