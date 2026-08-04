<ul class="list-unstyled ps-0" id="sidebarMenuAccordion">
    <x-gingerminds-core::navigation.partial.nav_item
        :route="route('dashboard')"
        :icon="'bi bi-speedometer2'"
        :name="__('gingerminds-core::translation.dashboard')"
    />
    <x-gingerminds-core::navigation.partial.nav_item
        :id="'settings'"
        :icon="'bi bi-gear'"
        :label="__('gingerminds-core::translation.settings')"
        :permission="'view settings'"
        :items="[
            [
                'route' => route('gingerminds-core.users.index'),
                'icon' => 'bi bi-people',
                'label' => __('gingerminds-core::translation.users.name_p'),
                'permission' => 'view users',
            ],
            [
                'route' => route('gingerminds-core.contributors.index'),
                'icon' => 'bi bi-person-badge',
                'label' => __('gingerminds-core::translation.contributors.name_p'),
                'permission' => 'view contributors',
            ],
            [
                'route' => route('gingerminds-core.roles.index'),
                'icon' => 'bi bi-shield-check',
                'label' => __('gingerminds-core::translation.roles.name_p'),
                'permission' => 'manage roles',
            ],
            [
                'route' => route('gingerminds-core.permissions.index'),
                'icon' => 'bi bi-key',
                'label' => __('gingerminds-core::translation.permissions.name_p'),
                'permission' => 'view permissions',
            ],
        ]"
    />
</ul>
