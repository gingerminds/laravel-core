<?php

return [
    'dashboard' => 'Dashboard',
    'actions' => 'Actions',
    'display' => 'Display',
    'none' => 'None',
    'settings' => 'Settings',
    'previous' => 'Previous',
    'next' => 'Next',
    'nb_results' => 'Results :current of :total',
    'here' => 'here',
    'all' => 'All',
    'value' => 'Value',
    'from' => 'From',
    'to' => 'To',
    'yes' => 'Yes',
    'no' => 'No',

    'successfully_created' => ':model successfully created.',
    'successfully_updated' => ':model updated successfully.',
    'successfully_deleted' => ':model deleted successfully.',

    'title_list' => ':model List',
    'title_m_create' => 'Add a :model',
    'title_f_create' => 'Add an :model',
    'title_m_edit' => 'Modify a :model',
    'title_f_edit' => 'Modify an :model',
    'modal' => [
        'title_create' => 'Add {model}',
        'title_edit' => 'Modify {model}',
        'title_delete' => 'Delete {model}',
        'title_default' => 'Loading...',
    ],

    'message' => [
        'no_result' => 'No results found',
        'login' => [
            'welcome_back' => 'Welcome back !',
            'sign_in_to_access' => 'Sign in to access'
        ],
    ],

    'auth' => [
        'form' => [
            'login' => 'Login',
            'login_placeholder' => 'Enter your login',
            'password' => 'Password',
            'password_placeholder' => 'Enter your password',
            'remember' => 'Remember me',
        ]
    ],

    'menu' => [
        'general' => 'General',
        'configuration' => 'Configuration',
    ],

    'title' => [
       'login' => 'Log in',
    ],

    'form' => [
        'name' => 'Name',
        'lastname' => 'Name',
        'firstname' => 'Firstname',
        'code' => 'Code',
        'email' => 'Email',
        'label' => 'Label',
        'title' => 'Title',
        'description' => 'Description',
        'is_active' => 'Is active ?',
        'active' => 'Active',
        'inactive' => 'Inactive',
        'position' => 'Position',
        'placeholder' => [
            'search' => 'Search...',
        ],
        'helpers' => [
            'no_change_if_kept_empty' => 'Leave blank to keep the current setting.',
        ],
    ],

    'action' => [
        'filter' => 'Filter',
        'filters' => 'Filters',
        'search' => 'Search',
        'clear_filters' => 'Clear filters',
        'select' => 'Select',
        'save' => 'Save',
        'cancel' => 'Cancel',
        'remove' => 'Remove',
        'remove_confirm' => 'Are you sure you want to remove this item?',
        'sign_out' => 'Sign out',
        'see' => 'See',
        'move_up' => 'Move up',
        'move_down' => 'Move down',
        'back' => 'Back'
    ],

    'users' => [
        'manage' => 'Manage users',
        'name_s' => 'User',
        'name_p' => 'Users',
        'form' => [
            'mr' => "Mr",
            'mrs' => "Mrs",
            'email' => "Email",
            'password' => "Password",
            'password_confirmation' => "Confirmation",
            'roles' => "Roles",
            'roles_placeholder' => "Hold down Ctrl/Cmd to select multiple roles.",
            'contributor' => "Contributor",
            'contributor_associate' => "Associated contributor",
            'contributor_new' => "New contributor",
            'contributor_civility' => "Civility",
            'contributor_lastname' => "Lastname",
            'contributor_firstname' => "Firstname",
            'contributor_trigram' => "Trigram",
            'contributor_info' => "Edit the associated contributor's information.",
        ],
        'message' => [
            'no_users' => 'Empty users',
        ],
    ],

    'contributors' => [
        'manage' => 'Manage contributors',
        'name_s' => 'Contributor',
        'name_p' => 'Contributors',
        'associated_user' => 'Associated user',
        'form' => [
            'civility' => "Civility",
            'lastname' => "Lastname",
            'firstname' => "Firstname",
            'trigram' => "Trigram",
        ],
        'message' => [
            'no_contributors' => 'Empty contributors',
        ],
    ],

    'roles' => [
        'manage' => 'Manage roles',
        'name_s' => 'Role',
        'name_p' => 'Roles',
        'form' => [
            'name' => "Name",
            'name_placeholder' => "Please enter the role name",
            'permissions' => "Permissions",
            'permissions_placeholder' => "Hold down Ctrl/Cmd to select multiple permissions.",
            'resource' => "Ressource",
            'check_all' => "Check all",
            'is_external' => "External ?",
            'is_default' => "Default ?",
        ],
        'message' => [
            'no_roles' => 'Empty roles',
        ],
    ],

    'permissions' => [
        'manage' => 'Manager permissions',
        'name_s' => 'Permission',
        'name_p' => 'Permissions',
        'form' => [
            'name' => "Name",
            'name_placeholder' => "Please enter the permission name",
        ],
        'message' => [
            'no_permissions' => 'Empty permissions',
        ],
    ],

    'profile' => [
        'name' => 'Profile',
        'settings' => 'Profile Settings',
    ],
];
