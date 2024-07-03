# User Roles

SAML SSO contains default functionality to handle roles for users created via SSO, but in many cases you may want more complex
behaviour.

A variety of filters are provided to help map the data across from your identity provider (IdP).

## Automatic default role

By default, when users log in, they'll receive the default role for your site, matching WordPress' behaviour for open registration
sites. (Typically, this is `subscriber`.)

For large multisites or complex use cases, this may not be desirable, as you may want to assign roles manually instead. This setting
can be changed by returning false from the `wpsimplesaml_add_users_to_site` filter.

```php
add_filter( 'wpsimplesaml_add_users_to_site', '__return_false' );
```

This filter receives the enabled boolean as the filterable value, and additionally receives the `WP_User` object as the second
parameter, allowing you to change behaviour based on the specific user.

## Mapping roles from your IdP

By default, roles aren't synchronized from your IdP, allowing you to manually assign them in WordPress instead.

In cases where you want to synchronize these roles, you can use the `wpsimplesaml_map_role` filter to determine the user's role.
This could be hardcoded, or use information from your IdP such as the ActiveDirectory role claim.

The `wpsimplesaml_map_role` filter receives the following parameters:

```php
/**
 * Filter the role to apply for the new user
 *
 * Example for single sites:
 * [ 'editor', 'job_admin' ]
 * 'administrator'
 *
 * Examples for multisite networks:
 *
 * - To apply a role for all sites in the network
 * [ 'network' => [ 'administrator' ] ]
 * 'administrator'
 *
 * - To grant network superadmin role
 * [ 'network' => [ 'superadmin' ] ]
 * 'superadmin'
 *
 * - To choose specific roles for each site ( user will be removed from omitted sites )
 * [ 'sites' => [ 1 => [ 'administrator' ], 2 => [ 'editor' ] ]
 *
 * @param array    $attributes SAML attributes
 * @param int      $user_id    User ID
 * @param \WP_User $user       User object
 *
 * @return string|array WP Role(s) to apply to the user
 */
apply_filters( 'wpsimplesaml_map_role', get_option( 'default_role' ), $attributes, $user->ID, $user );
```

You'll also need to enable mapping via the `wpsimplesaml_manage_roles` filter:

```php
add_filter( 'wpsimplesaml_manage_roles', '__return_true', 11 );
```

For example, you could hardcode this to make all users editors instead of the default role:

```php
add_filter( 'wpsimplesaml_manage_roles', '__return_true', 11 );
add_filter( 'wpsimplesaml_map_role', function ( $role ) {
    return [
        'editor',
    ];
} );
```

### Mapping from Active Directory role claim

When connecting to an Active Directory (AD) SAML IdP, you may wish to use roles stored in the IdP.

These are exposed via the `http://schemas.microsoft.com/ws/2008/06/identity/claims/role` claim in SAML, and can be mapped from the
SAML attributes in your custom code.

```php
// Map AD user group membership to WordPress roles.
add_filter( 'wpsimplesaml_map_role', __NAMESPACE__ . '\\map_user_role', 10, 2 );
add_filter( 'wpsimplesaml_manage_roles', '__return_true', 11 );

/**
 * Map Active Directory user group membership to WordPress roles.
 *
 * @param string|array $default_role Default user role.
 * @param array        $attributes   SAML attributes.
 *
 * @return string|array Updated user role.
 */
function map_user_role( $default_role, array $attributes ) {
    $role_key = 'http://schemas.microsoft.com/ws/2008/06/identity/claims/role';

    switch ( $attributes[ $role_key ][0] ?? null ) {
        case 'gg_rol_EX-PROD-Admins':
            return 'administrator';

        case 'gg_rol_EX-PROD-Editors':
            return 'editor';

        case 'gg_rol_EX-PROD-ReadOnly':
            return 'subscriber';

        default:
            // Give no roles by default.
            return [];
    }
}
```
