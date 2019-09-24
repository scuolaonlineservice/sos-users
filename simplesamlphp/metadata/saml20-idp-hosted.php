<?php


// SAML Metadata for SimpleSAMLphp (configured for Google Suite SSO)
// No values need to be changed here (everything's already configured for Google Suite SSO)

$metadata['__DYNAMIC:1__'] = [

  // The hostname of the server (VHOST) that this SAML entity will use.
  'host'              =>  '__DEFAULT__',

  // X.509 key and certificate. Relative to the cert directory.
  'privatekey'   => 'googleappsidp.pem',
  'certificate'  => 'googleappsidp.crt',

  // Authentication module
  'auth' => 'joomlamodule:JoomlaAuth',
];
