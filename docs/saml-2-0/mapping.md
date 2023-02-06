# Mapping Data

The SAML SSO functionality includes out-of-the-box functionality for mapping data from your identity provider (IdP) to WordPress-native data.

This mapping can be customized and extended to match the way your IdP stores data.


## User details

By default, the following fields are mapped:

* `user_login` field (equivalent to a user slug): `email` SAML attribute
* `user_email`: `email` SAML attribute
* `first_name`: `firstName` SAML attribute
* `last_name`: `lastName` SAML attribute

This can be filtered via the `wpsimplesaml_attribute_mapping` filter, which receives an associative array mapping user properties to SAML attribute name. (Note: only these four fields are supported.)

For example, for Active Directory (AD), you may want to use the [AD claims](https://learn.microsoft.com/en-us/windows-server/identity/ad-fs/technical-reference/the-role-of-claims) instead:

```php
add_filter( 'wpsimplesaml_attribute_mapping', function() {
	return [
		'user_email' => 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress',
		'first_name' => 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/givenname',
		'last_name'  => 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/surname',
		'user_login' => 'http://schemas.microsoft.com/identity/claims/displayname',
	];
} );
```

### Advanced user details

Aside from the direct mapping of these , you can also filter the final data before it is sent to WordPress, via the `wpsimplesaml_user_data` filter.

This filter receives the full user data being passed to `wp_insert_user` if you need to apply any other customization to this user data. You'll also receive the SAML attributes.

```
/**
 * Filters user data before insertion to the database
 *
 * @param array $user_data  User data being passed to wp_insert_user()
 * @param array $attributes Attributes array coming from SAML Response object
 */
apply_filters( 'wpsimplesaml_user_data', $user_data, $attributes );
```


## Unique user matching

By default, when SAML SSO attempts to find an existing user, it'll look for any user with a matching email address (as returned from your mapping code). You may want to change this to deduplicate based on other user data instead.

The `wpsimplesaml_match_user` filter allows you to implement your own user matching code:

```php
/**
 * Filters matched user, allows matching via other SAML attributes
 *
 * @param null|false|\WP_User $user       User object or false if not found
 * @param string              $email      Email from SAMLResponse
 * @param array               $attributes SAML Attributes parsed from SAMLResponse
 */
$user = apply_filters( 'wpsimplesaml_match_user', null, $email, $attributes );
```

For example, if you have multiple corporate domains and need to deduplicate them:

```php
// Treat all users from corporate domains as equal.
add_filter( 'wpsimplesaml_match_user', function ( $user, $email ) {
	$email_parts = explode( '@', $email );
	$domains = [
		'example.com',
		'example.org',
		'example.net',
	];
	foreach ( $aliases as $alias ) {
		$aliased_user = get_user_by( 'email', $email_parts[0] . '@' . $alias );
		if ( $aliased_user ) {
			return $aliased_user;
		}
	}

	return $user;
}, 10, 2 );
```


## Role mapping

Roles can also be mapped; see the [user roles](./roles.md) documentation for more details.
