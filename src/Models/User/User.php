<?php

declare(strict_types=1);

namespace Gingerminds\LaravelCore\Models\User;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use Gingerminds\LaravelCore\ApiProvider\User\UserProvider;
use Gingerminds\LaravelCore\Database\Factories\User\UserFactory;
use Gingerminds\LaravelCore\Models\ResourceModelInterface;
use Gingerminds\LaravelCore\Models\SearchableModelInterface;
use Gingerminds\LaravelCore\Models\SortableModelInterface;
use Gingerminds\LaravelCore\StateProcessor\User\UserStateProcessor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Symfony\Component\Serializer\Attribute\Groups;

/**
 * @property string $password
 * @property string $email
 * @property string|null $email_verified_at
 * @property-read Contributor|null $contributor Relation to the contributor profile
 * @property-read int<0, max> $id
 */
#[ApiResource(
    operations: [
        new GetCollection(
            normalizationContext: ['groups' => [User::GROUP_LIST]],
            provider: UserProvider::class,
        ),
        new Get(
            normalizationContext: ['groups' => [User::GROUP_READ]],
            provider: UserProvider::class,
        ),
        new Post(
            normalizationContext: ['groups' => [User::GROUP_READ]],
            denormalizationContext: ['groups' => [User::GROUP_CREATE]],
            deserialize: false,
            provider: UserProvider::class,
            processor: UserStateProcessor::class
        ),
        new Delete(),
        new Patch(
            normalizationContext: ['groups' => [User::GROUP_READ]],
            denormalizationContext: ['groups' => [User::GROUP_EDIT]],
            deserialize: false,
            provider: UserProvider::class,
            processor: UserStateProcessor::class
        ),
    ],
    middleware: ['auth:sanctum']
)]
#[ApiProperty(
    identifier: true,
    property: 'id',
    serialize: new Groups([
        User::GROUP_LIST,
        User::GROUP_READ,
        Contributor::GROUP_LIST,
        Contributor::GROUP_READ,
    ])
)]
#[ApiProperty(property: 'email', serialize: new Groups([
    User::GROUP_LIST,
    User::GROUP_READ,
    User::GROUP_CREATE,
    User::GROUP_EDIT,
    Contributor::GROUP_LIST,
    Contributor::GROUP_READ,
]))]
#[ApiProperty(property: 'roles', serialize: new Groups([
    User::GROUP_LIST,
    User::GROUP_READ,
    User::GROUP_CREATE,
    User::GROUP_EDIT,
]))]
#[ApiProperty(property: 'password', serialize: new Groups([
    User::GROUP_CREATE,
    User::GROUP_EDIT,
]))]
#[ApiProperty(property: 'password_confirmation', serialize: new Groups([
    User::GROUP_CREATE,
    User::GROUP_EDIT,
]))]
#[ApiProperty(property: 'contributor', serialize: new Groups([
    User::GROUP_LIST,
    User::GROUP_READ,
]))]
#[ApiProperty(property: 'contributor_id', serialize: new Groups([
    User::GROUP_EDIT,
]))]
#[ApiProperty(property: 'contributor_firstname', serialize: new Groups([
    User::GROUP_EDIT,
]))]
#[ApiProperty(property: 'contributor_lastname', serialize: new Groups([
    User::GROUP_EDIT,
]))]
#[ApiProperty(property: 'contributor_trigram', serialize: new Groups([
    User::GROUP_EDIT,
]))]
#[ApiProperty(property: 'contributor_civility', serialize: new Groups([
    User::GROUP_EDIT,
]))]
class User extends Authenticatable implements
    ResourceModelInterface,
    SortableModelInterface,
    SearchableModelInterface
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;
    use HasApiTokens;
    use Notifiable;
    use HasRoles;

    public const string GROUP_LIST   = 'user:list';
    public const string GROUP_READ   = 'user:read';
    public const string GROUP_CREATE = 'user:create';
    public const string GROUP_EDIT   = 'user:edit';

    protected string $guardName = 'web';

    /**
     * `roles` (Spatie `HasRoles`) and `contributor` are both serialized in
     * GROUP_LIST/GROUP_READ, but are also read outside any repository
     * listing — e.g. the auth guard resolving the current user, role/policy
     * checks (`hasRole()`, `role:` middleware) — none of which go through
     * `AbstractRepository::get()` and its `EagerLoadableModelInterface`
     * eager-load merge. A global scope (same pattern `TranslatableModelTrait`
     * uses for `translations`/`currentTranslation`) is the only way to cover
     * every one of those paths at once.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('rolesAndContributor', function (Builder $builder): void {
            $builder->with(['roles', 'contributor']);
        });
    }

    public function guardName(): string
    {
        return 'web';
    }

    /**
     * @return string[]
     */
    public function getFillable(): array
    {
        return [
            'email',
            'email_verified_at',
            'password',
        ];
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'contributor_id'                  => 'integer',
            'contributor_show_personal_phone' => 'boolean',
            'email_verified_at'               => 'datetime',
            'password'                        => 'hashed',
        ];
    }

    /**
     * @return string[]
     */
    public function getHidden(): array
    {
        return [
            'password',
            'remember_token',
        ];
    }

    /**
     * Get the contributor profile for the user.
     *
     * @return HasOne<Contributor, $this>
     */
    public function contributor(): HasOne
    {
        return $this->hasOne(Contributor::class);
    }

    public static function getSearchableFields(): array
    {
        return ['email'];
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return Factory<User>
     */
    protected static function newFactory(): Factory
    {
        return UserFactory::new();
    }
}
