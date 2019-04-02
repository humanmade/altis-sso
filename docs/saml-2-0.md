# SAML 2.0

The SSO module includes support for SAML 2.0 as a Service Provider. To enable SAML 2.0, you must define the `saml` setting in the Platform configuration. You'll need a copy of your SAML IdP Metadata XML too.

To enabled SAML 2.0 support, define the following options in the configuration file, and provider the SAML IdP Metadata in a file:


```json
"sso": {
	"saml": {
		"required": true | false
	}
}
```

The `required` setting defines whether authentication via the SAML 2.0 IdP for all users, or if it should be optional. When set to `true`, all users attempting to login to the site will be redirected to the SAML IdP for authorization. When setting this to `false`, an "SSO Login" link will be added to the login page, where users can optionally authorize with the SAML IdP.

To provide the SAML IdP Metadata file, paste your SAML IpP's metadata into a new file at the path `config/sso/saml-idp-metadata.xml`. Make sure there are no XML formatting errors or leading whitespeace. For example:

Contents of `config/sso/saml-idp-metadata.xml`:

```xml
<?xml version="1.0"?>
<md:EntityDescriptor xmlns:md="urn:oasis:names:tc:SAML:2.0:metadata" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" entityID="http://localhost:8082/simplesaml/saml2/idp/metadata.php">
  <md:IDPSSODescriptor protocolSupportEnumeration="urn:oasis:names:tc:SAML:2.0:protocol">
    <md:KeyDescriptor use="signing">
      <ds:KeyInfo xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
        <ds:X509Data>
          <ds:X509Certificate>//redactoed</ds:X509Certificate>
        </ds:X509Data>
      </ds:KeyInfo>
    </md:KeyDescriptor>
    <md:KeyDescriptor use="encryption">
      <ds:KeyInfo xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
        <ds:X509Data>
          <ds:X509Certificate>//redactoed</ds:X509Certificate>
        </ds:X509Data>
      </ds:KeyInfo>
    </md:KeyDescriptor>
    <md:SingleLogoutService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect" Location="http://localhost:8082/simplesaml/saml2/idp/SingleLogoutService.php"/>
    <md:NameIDFormat>urn:oasis:names:tc:SAML:2.0:nameid-format:transient</md:NameIDFormat>
    <md:SingleSignOnService Binding="urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect" Location="http://localhost:8082/simplesaml/saml2/idp/SSOService.php"/>
  </md:IDPSSODescriptor>
</md:EntityDescriptor>
```

For further details on SAML 2.0, see the [wp-simple-sample](https://github.com/humanmade/wp-simple-saml) plugin details.
