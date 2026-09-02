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
use Gingerminds\LaravelCore\ApiProvider\User\ContributorProvider;
use Gingerminds\LaravelCore\Database\Factories\User\ContributorFactory;
use Gingerminds\LaravelCore\Models\EagerLoadableModelInterface;
use Gingerminds\LaravelCore\Models\ResourceModelInterface;
use Gingerminds\LaravelCore\Models\SearchableModelInterface;
use Gingerminds\LaravelCore\Models\SortableModelInterface;
use Gingerminds\LaravelCore\Models\Trait\EagerLoadableModelTrait;
use Gingerminds\LaravelCore\StateProcessor\User\ContributorStateProcessor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Symfony\Component\Serializer\Attribute\Groups;

/**
 * @property int|null $user_id
 * @property string $firstname
 * @property string $lastname
 * @property string $trigram
 * @property string $civility
 */
#[ApiResource(
    operations: [
        new GetCollection(
            normalizationContext: ['groups' => [Contributor::GROUP_LIST]],
            provider: ContributorProvider::class,
        ),
        new Get(
            normalizationContext: ['groups' => [Contributor::GROUP_READ]],
            provider: ContributorProvider::class,
        ),
        new Post(
            normalizationContext: ['groups' => [Contributor::GROUP_READ]],
            denormalizationContext: ['groups' => [Contributor::GROUP_EDIT]],
            deserialize: false,
            provider: ContributorProvider::class,
            processor: ContributorStateProcessor::class
        ),
        new Delete(),
        new Patch(
            normalizationContext: ['groups' => [Contributor::GROUP_READ]],
            denormalizationContext: ['groups' => [Contributor::GROUP_EDIT]],
            deserialize: false,
            provider: ContributorProvider::class,
            processor: ContributorStateProcessor::class
        ),
    ],
    middleware: ['auth:sanctum']
)]
#[ApiProperty(property: 'id', serialize: new Groups([
    Contributor::GROUP_LIST,
    Contributor::GROUP_READ,
    User::GROUP_READ,
    User::GROUP_LIST,
]))]
#[ApiProperty(property: 'firstname', serialize: new Groups([
    Contributor::GROUP_LIST,
    Contributor::GROUP_READ,
    Contributor::GROUP_EDIT,
    User::GROUP_READ,
    User::GROUP_LIST,
]))]
#[ApiProperty(property: 'lastname', serialize: new Groups([
    Contributor::GROUP_LIST,
    Contributor::GROUP_READ,
    Contributor::GROUP_EDIT,
    User::GROUP_READ,
    User::GROUP_LIST,
]))]
#[ApiProperty(property: 'trigram', serialize: new Groups([
    Contributor::GROUP_LIST,
    Contributor::GROUP_READ,
    Contributor::GROUP_EDIT,
    User::GROUP_READ,
    User::GROUP_LIST,
]))]
#[ApiProperty(property: 'civility', serialize: new Groups([
    Contributor::GROUP_LIST,
    Contributor::GROUP_READ,
    Contributor::GROUP_EDIT,
    User::GROUP_READ,
    User::GROUP_LIST,
]))]
#[ApiProperty(property: 'user', serialize: new Groups([
    Contributor::GROUP_LIST,
    Contributor::GROUP_READ,
]))]
#[ApiProperty(property: 'user_id', serialize: new Groups([
    Contributor::GROUP_EDIT,
]))]
class Contributor extends Model implements
    ResourceModelInterface,
    SortableModelInterface,
    SearchableModelInterface,
    EagerLoadableModelInterface
{
    /** @use HasFactory<ContributorFactory> */
    use HasFactory;
    use SoftDeletes;
    use EagerLoadableModelTrait;

    public const string GROUP_LIST = 'contributor:list';
    public const string GROUP_READ = 'contributor:read';
    public const string GROUP_EDIT = 'contributor:edit';

    /**
     * `user` backs both the `user`/`user_id` ApiProperty (GROUP_LIST/GROUP_READ)
     * and the admin listing/edit views' `$contributor->user` access — without
     * this, AbstractRepository never eager-loads it and every one of those
     * reads lazy-loads instead, throwing under Model::preventLazyLoading().
     *
     * @return array<int, string>
     */
    public static function getEagerLoads(): array
    {
        return ['user'];
    }

    /**
     * @return string[]
     */
    public function getFillable(): array
    {
        return [
            'firstname',
            'lastname',
            'trigram',
            'civility',
            'avatar',
            'user_id',
        ];
    }

    /**
     * @return string[]
     */
    public function getCasts(): array
    {
        return [
            'user_id' => 'integer',
        ];
    }

    /**
     * Get the user that owns the Contributor
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function getSearchableFields(): array
    {
        return ['firstname', 'lastname', 'trigram'];
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return Factory<Contributor>
     */
    protected static function newFactory(): Factory
    {
        return ContributorFactory::new();
    }
}
