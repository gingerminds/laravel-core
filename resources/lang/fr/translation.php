<?php

return [
    'dashboard' => 'Tableau de bord',
    'actions' => 'Actions',
    'display' => 'Afficher',
    'none' => 'Aucun',
    'settings' => 'Paramètres',
    'previous' => 'Précédent',
    'next' => 'Suivant',
    'nb_results' => 'Résultats :current sur :total',
    'yes' => 'Oui',
    'no' => 'Non',
    'from' => 'Du',
    'to' => 'Au',
    'number_from' => 'De',
    'number_to' => 'A',
    'here' => 'ici',
    'all' => 'Tous',
    'value' => 'Valeur',

    'successfully_created' => ':model créé avec succès.',
    'successfully_updated' => ':model mis à jour avec succès.',
    'successfully_deleted' => ':model supprimé avec succès.',

    'title_list' => 'Liste des :model',
    'title_m_create' => 'Ajouter un :model',
    'title_f_create' => 'Ajouter une :model',
    'title_m_edit' => 'Modifier un :model',
    'title_f_edit' => 'Modifier une :model',
    'modal' => [
        'title_create' => 'Ajouter {model}',
        'title_edit' => 'Modifier {model}',
        'title_delete' => 'Supprimer {model}',
        'title_default' => 'Chargement...',
    ],

    'message' => [
        'no_result' => 'Aucun résultat trouvé',
        'login' => [
            'welcome_back' => 'Bon retour !',
            'sign_in_to_access' => 'Connectez-vous pour accéder'
        ],
    ],

    'auth' => [
        'form' => [
            'login' => 'Identifiant',
            'login_placeholder' => 'Entrez votre identifiant',
            'password' => 'Mot de passe',
            'password_placeholder' => 'Entrez votre mot de passe',
            'remember' => 'Se souvenir de moi',
        ]
    ],

    'menu' => [
        'general' => 'Géneral',
        'configuration' => 'Configuration'
    ],

    'title' => [
        'login' => 'Connexion',
    ],

    'form' => [
        'name' => 'Nom',
        'lastname' => 'Nom',
        'firstname' => 'Prénom',
        'code' => 'Code',
        'email' => 'Email',
        'label' => 'Libellé',
        'title' => 'Titre',
        'description' => 'Description',
        'is_active' => 'Actif ?',
        'active' => 'Actif',
        'inactive' => 'Inactif',
        'position' => 'Position',
        'placeholder' => [
            'search' => 'Rechercher...',
        ],
        'helpers' => [
            'no_change_if_kept_empty' => 'Laisser vide pour conserver le paramètre actuel.',
        ],
    ],

    'action' => [
        'filter' => 'Filtrer',
        'filters' => 'Filtres',
        'search' => 'Rechercher',
        'clear_filters' => 'Effacer les filtres',
        'select' => 'Selectionner',
        'save' => 'Enregistrer',
        'cancel' => 'Annuler',
        'remove' => 'Supprimer',
        'remove_confirm' => 'Êtes-vous sûr de vouloir supprimer cet élément ?',
        'sign_out' => 'Se déconnecter',
        'see' => 'Voir',
        'move_up' => 'Monter',
        'move_down' => 'Descendre',
        'back' => 'Retour'
    ],

    'users' => [
        'manage' => 'Gérer les utilisateurs',
        'name_s' => 'Utilisateur',
        'name_p' => 'Utilisateurs',
        'form' => [
            'mr' => "M.",
            'mrs' => "Mme",
            'email' => "Email",
            'password' => "Mot de passe",
            'password_confirmation' => "Confirmation",
            'roles' => "Rôles",
            'roles_placeholder' => "Maintenez Ctrl/Cmd pour sélectionner plusieurs rôles.",
            'contributor' => "Collaborateur",
            'contributor_associate' => "Collaborateur associé",
            'contributor_new' => "Nouveau collaborateur",
            'contributor_civility' => "Civilité",
            'contributor_lastname' => "Nom",
            'contributor_firstname' => "Prénom",
            'contributor_trigram' => "Trigramme",
            'contributor_info' => "Modifier les informations du collaborateur associé.",
        ],
        'message' => [
            'no_users' => 'Aucun utilisateur',
        ],
    ],

    'contributors' => [
        'manage' => 'Gérer les collaborateurs',
        'name_s' => 'Collaborateur',
        'name_p' => 'Collaborateurs',
        'associated_user' => 'Utilisateur associé',
        'form' => [
            'civility' => "Civilité",
            'lastname' => "Nom",
            'firstname' => "Prénom",
            'trigram' => "Trigramme",
        ],
        'message' => [
            'no_contributors' => 'Aucun collaborateur',
        ],
    ],

    'roles' => [
        'manage' => 'Gérer les rôles',
        'name_s' => 'Rôle',
        'name_p' => 'Rôles',
        'form' => [
            'name' => "Nom",
            'name_placeholder' => "Veuillez saisir le nom du rôle",
            'permissions' => "Permissions",
            'permissions_placeholder' => "Maintenez Ctrl/Cmd pour sélectionner plusieurs permissions.",
            'resource' => "Ressource",
            'check_all' => "Tout cocher",
            'is_external' => "Externe ?",
            'is_default' => "Par défaut ?",
        ],
        'message' => [
            'no_roles' => 'Aucun rôle',
        ],
    ],

    'permissions' => [
        'manage' => 'Gérer les permissions',
        'name_s' => 'Permission',
        'name_p' => 'Permissions',
        'form' => [
            'name' => "Nom",
            'name_placeholder' => "Veuillez saisir le nom de la permission",
        ],
        'message' => [
            'no_permissions' => 'Aucune permission',
        ],
    ],

    'profile' => [
        'name' => 'Profil',
        'settings' => 'Paramètres du profil',
    ],
];