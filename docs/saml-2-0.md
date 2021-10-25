# SAML 2.0

The SSO module includes support for SAML 2.0 as a Service Provider. To enable SAML 2.0, you must define the `saml` setting in the Altis configuration. You'll need a copy of your SAML IdP Metadata XML too.

To generate the Service Provider metadata file from Altis, which is typically needed to setup the Identity provider application for each environment, visit [`/sso/metadata/`](site://sso/metadata/) on your application to download the metadata XML file. Collect these XML files for all environments and pass it to your colleagues responsible for the SSO integration setup. You should expect back an Identity provider metadata XML file per environment.

To enable SAML 2.0 support, define the following options in the configuration file, and provide the SAML IdP Metadata in a file per environment:


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
			},
			"environments": {
				"development": {
					"modules": {
						"sso": {
							"saml": {
								"metadata_file": ".config/sso/saml-idp-metadata-development.xml"
							}
						}
					}
				}
			}
		}
	}
}
```

The `required` setting defines whether authentication via the SAML 2.0 IdP for all users, or if it should be optional. When set to `true`, all users attempting to login to the site will be redirected to the SAML IdP for authorization. When setting this to `false`, an "SSO Login" link will be added to the login page, where users can optionally authorize with the SAML IdP.

Your SAML IdP Metadata file is specified by the `metadata_file` setting, which is a path relative to your project root. By default, this is set to `.config/sso/saml-idp-metadata-%ENVIRONMENT%.xml` where `%ENVIRONMENT%` is one of `local`, `development`, `staging`, or `production`, and falls back to the sample config at `vendor/altis/sso/config/saml-idp-metadata.xml`. Make sure there are no XML formatting errors or leading whitespeace. The setting doesn't need to be overridden if the files are in the expected location with the expected naming conventions.

For further details on SAML 2.0, see the [wp-simple-saml](https://github.com/humanmade/wp-simple-saml) plugin details.
