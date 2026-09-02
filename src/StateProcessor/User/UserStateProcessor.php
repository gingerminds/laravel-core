<?php

declare(strict_types=1);

namespace Gingerminds\LaravelCore\StateProcessor\User;

use ApiPlatform\State\ProcessorInterface;
use Gingerminds\LaravelCore\Http\Requests\User\UserRequest;
use Gingerminds\LaravelCore\Models\User\User;
use Gingerminds\LaravelCore\Repositories\User\UserRepository;
use Gingerminds\LaravelCore\StateProcessor\BaseStateProcessor;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;

/**
 * @implements ProcessorInterface<User, User>
 */
class UserStateProcessor extends BaseStateProcessor implements ProcessorInterface
{
    public function __construct(
        UserRepository $repository,
        ValidationFactory $validationFactory
    ) {
        $this->repository  = $repository;
        $this->formRequest = new UserRequest();

        // app(User::class) resolves to the project's configured model (see
        // LaravelCoreServiceProvider::bindResources()), not the hardcoded
        // core class `new User()` would give — required so Spatie's
        // syncRoles()/assignRole() persist the morph map's short alias
        // instead of falling back to the raw core FQCN.
        $this->resourceModel = app(User::class);

        parent::__construct($validationFactory);
    }
}
