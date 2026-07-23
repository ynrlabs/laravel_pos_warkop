<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Models
    |--------------------------------------------------------------------------
    |
    | When using the "HasRoles" trait from this package, we need to know which
    | Eloquent model should be used to retrieve your roles. Of course, it
    | is often just the "User" model but you may use whatever you like.
    |
    | The model you want to use as a Role model needs to have the
    | "Spatie\Permission\Contracts\Role" interface.
    |
    */

    'models' => [

        'role' => Spatie\Permission\Models\Role::class,

        'permission' => Spatie\Permission\Models\Permission::class,

        'team' => Spatie\Permission\Models\Team::class,

    ],

    /*
    |--------------------------------------------------------------------------
    | Table Names
    |--------------------------------------------------------------------------
    |
    | When using the "HasRoles" trait from this package, we need to know which
    | tables should be used to retrieve your roles and permissions. We have
    | chosen a basic default value but you may easily change it to any
    | table you like.
    |
    */

    'table_names' => [

        'roles' => 'roles',

        'permissions' => 'permissions',

        'model_has_permissions' => 'model_has_permissions',

        'model_has_roles' => 'model_has_roles',

        'role_has_permissions' => 'role_has_permissions',

    ],

    /*
    |--------------------------------------------------------------------------
    | Column Names
    |--------------------------------------------------------------------------
    |
    | You can change the column names that the package uses in the migrations.
    |
    */

    'column_names' => [

        'role_pivot_key' => 'role_id',

        'permission_pivot_key' => 'permission_id',

        'model_morph_key' => 'model_id',

        'team_foreign_key' => 'team_id',

    ],

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    |
    | When using the "HasRoles" trait, we will cache the roles and permissions
    | to prevent hitting the database repeatedly. Here you can set the
    | expiration time (in seconds) of this cache. Set to 0 to disable.
    |
    */

    'cache' => [

        'expiration_time' => \DateInterval::createFromDateString('24 hours'),

        'key' => 'spatie.permission.cache',

        'store' => 'database',

    ],

    /*
    |--------------------------------------------------------------------------
    | Wildcard Permissions
    |--------------------------------------------------------------------------
    |
    | You can enable wildcard permissions. This means that you can check for
    | permissions like "edit.*" and it will match "edit.post", "edit.user", etc.
    |
    */

    'wildcard_permissions' => [

        'enabled' => true,

    ],

    /*
    |--------------------------------------------------------------------------
    | Permission Checks
    |--------------------------------------------------------------------------
    |
    | You can disable permission checks. This will make the "can" method
    | always return true. Useful for local development.
    |
    */

    'permission_checks' => [

        'enabled' => true,

    ],

    /*
    |--------------------------------------------------------------------------
    | Super Admin
    |--------------------------------------------------------------------------
    |
    | Here you can configure the super admin role name. Users with this role
    | will have all permissions automatically.
    |
    */

    'super_admin' => [

        'name' => 'super_admin',

    ],

];