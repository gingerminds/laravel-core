<?php

declare(strict_types=1);

namespace Gingerminds\LaravelCore\Models\Role;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use Gingerminds\LaravelCore\ApiProvider\Role\RoleProvider;
use Gingerminds\LaravelCore\Models\ResourceModelInterface;
use Gingerminds\LaravelCore\Models\SearchableModelInterface;
use Gingerminds\LaravelCore\Models\SortableModelInterface;
use Gingerminds\LaravelCore\Models\User\User;
use Gingerminds\LaravelCore\Resolver\ResourceResolver;
use Gingerminds\LaravelCore\StateProcessor\Role\RoleStateProcessor;
use Illuminate\Database\Eloquent\Relations\MorphPivot;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Spatie\Permission\Models\Role as SpatieRole;
use Symfony\Component\Serializer\Attribute\Groups;

/**
 * @property bool|null $is_external
 * @property bool|null $is_default
 * @property string $name
 * @property string $guard_name
 * @property int $permissions_count
 */
#[ApiResource(
    operations: [
        new GetCollection(
            normalizationContext: ['groups' => [Role::GROUP_LIST]],
            provider: RoleProvider::class
        ),
        new Get(
            normalizationContext: ['groups' => [Role::GROUP_READ]],
            provider: RoleProvider::class
        ),
        new Post(
            normalizationContext: ['groups' => [Role::GROUP_READ]],
            denormalizationContext: ['groups' => [Role::GROUP_EDIT]],
            deserialize: false,
            provider: RoleProvider::class,
            processor: RoleStateProcessor::class
        ),
        new Delete(),
        new Patch(
            normalizationContext: ['groups' => [Role::GROUP_READ]],
            denormalizationContext: ['groups' => [Role::GROUP_EDIT]],
            deserialize: false,
            provider: RoleProvider::class,
            processor: RoleStateProcessor::class
        ),
    ],
    middleware: ['auth:sanctum']
)]
#[ApiProperty(
    identifier: true,
    property: 'id',
    serialize: new Groups([
        Role::GROUP_LIST,
        Role::GROUP_READ,
        User::GROUP_LIST,
        User::GROUP_READ,
    ])
)]
#[ApiProperty(property: 'name', serialize: new Groups([
    Role::GROUP_LIST,
    Role::GROUP_READ,
    Role::GROUP_EDIT,
    User::GROUP_LIST,
    User::GROUP_READ,
]))]
#[ApiProperty(property: 'permissions', serialize: new Groups([
    Role::GROUP_READ,
    Role::GROUP_EDIT,
]))]
#[ApiProperty(property: 'permissions_count', serialize: new Groups([
    Role::GROUP_LIST,
]))]
#[ApiProperty(property: 'is_external', serialize: new Groups([
    Role::GROUP_LIST,
    Role::GROUP_READ,
    Role::GROUP_EDIT,
]))]
#[ApiProperty(property: 'is_default', serialize: new Groups([
    Role::GROUP_LIST,
    Role::GROUP_READ,
    Role::GROUP_EDIT,
]))]
class Role extends SpatieRole implements
    ResourceModelInterface,
    SortableModelInterface,
    SearchableModelInterface
{
    public const string GROUP_LIST = 'role:list';
    public const string GROUP_READ = 'role:read';
    public const string GROUP_EDIT = 'role:edit';

    protected $attributes = [
        'guard_name' => 'web',
    ];

    /**
     * @return string[]
     */
    public function getFillable(): array
    {
        return [
            'name',
            'is_external',
            'is_default',
            'guard_name',
        ];
    }

    /**
     * @return string[]
     */
    public function getCasts(): array
    {
        return [
            'is_external' => 'boolean',
            'is_default'  => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(static function (Role $role): void {
            if (empty($role->guard_name)) {
                $role->guard_name = config('auth.defaults.guard');
            }
        });

        static::saving(static function (Role $role): void {
            if ($role->is_default) {
                static::query()
                    ->where('id', '!=', $role->id)
                    ->where('is_external', $role->is_external)
                    ->update(['is_default' => null]);
            } else {
                $role->is_default = null;
            }
        });
    }

    public function getPermissionsCountAttribute(): int
    {
        return $this->permissions->count();
    }

    /**
     * @return MorphToMany<\Illuminate\Database\Eloquent\Model, $this, MorphPivot, 'pivot'>
     */
    public function users(): MorphToMany
    {
        return $this->morphedByMany(
            ResourceResolver::model('user'),
            'model',
            'model_has_roles',
            'role_id',
            'model_id'
        );
    }

    public static function getSearchableFields(): array
    {
        return ['name'];
    }
}
