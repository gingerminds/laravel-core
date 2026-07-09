<?php

declare(strict_types=1);

namespace Gingerminds\LaravelCore\Policies;

use Gingerminds\LaravelCore\Models\User\User;

abstract class AbstractResourcePolicy
{
    abstract protected function resourceName(): string;

    /**
     * Determine whether the user can view any models. Gated behind its own
     * `view {resource}` permission by default (the safer default) — for a
     * resource that should be readable by anyone, override this (and
     * `view()`) in the concrete policy to `return true;` instead.
     */
    public function viewAny(?User $user): bool
    {
        return (bool) $user?->can('view ' . $this->resourceName());
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user): bool
    {
        return $this->viewAny($user);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('edit ' . $this->resourceName());
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user): bool
    {
        return $user->can('edit ' . $this->resourceName());
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user): bool
    {
        return $user->can('delete ' . $this->resourceName());
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(): bool
    {
        return false;
    }
}
