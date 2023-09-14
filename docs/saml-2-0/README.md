# SAML 2.0

The SSO module includes support for SAML 2.0 as a Service Provider (SP). To enable SAML 2.0, you must define the `saml` setting in the Altis configuration. You'll need a copy of your SAML Identity Provider (IdP) metadata XML too.

## Service Provider Endpoints

In your IdP you can provide the following endpoint URLs to configure SSO, where `<site-url>` should be replaced with your application's primary site URL:

- Single Logout Service (SLS): `https://<site-url>/sso/sls`
- Assertion Consumer Service (ACS): `https://<site-url>/sso/verify`

**Note:**: `<site-url>` will default to your primary network URL rather than the current site's URL. For per site mode use the following filter:

```php
add_filter( 'wpsimplesaml_network_activated', '__return_false', 100 );
```

## Identity Provider Metadata XML

To enable SAML 2.0 support, add the IdP metadata XML files to your project's `.config/sso/` directory (you may need to create the directory first).

By default, Altis looks for `.config/sso/saml-idp-metadata-%ENVIRONMENT%.xml` where `%ENVIRONMENT%` is one of `local`, `development`, `staging`, or `production`, and falls back to `.config/sso/saml-idp-metadata.xml`. Make sure there are no XML formatting errors or leading whitespace.

Lastly define the following option in your Altis configuration:


```json
{
	"extra": {
		"altis": {
			"modules": {
				"sso": {
					"saml": {
						"required": true | false,
					}
				}
			}
		}
	}
}
```

The `required` setting defines whether authentication via the SAML 2.0 IdP _must_ be used to login, or if it should be optional. When set to `true`, all users attempting to login to the site will be redirected to the SAML IdP for authorization. When setting this to `false`, an "SSO Login" link will be added to the login page, where users can optionally authorize with the SAML IdP.

When you have an IdP Metadata XML file you also retrieve the Service Provider Metadata XML from the URL `https://<site-url>/sso/metadata`.

### Custom IdP Metadata XML File Paths

The SAML IdP Metadata XML file location can be overridden by the `sso.saml.metadata_file` config setting, which is a path relative to your project root. The setting doesn't need to be overridden if the files are in the expected location with the expected naming conventions. The config setting is provided as an option if your IdP Metadata XML is not added to the code base manually.

You can also override the settings on a per environment basis using the `environments.<env-type>` config path, for example:

```json
{
	"extra": {
		"altis": {
			"environments": {
				"development": {
					"modules": {
						"sso": {
							"saml": {
								"metadata_file": ".config/sso/custom-dev-idp-metadata.xml"
							}
						}
					}
				}
			}
		}
	}
}
```

For further details on SAML 2.0, [see the docs for wp-simple-saml plugin](https://github.com/humanmade/wp-simple-saml) that powers this feature.
