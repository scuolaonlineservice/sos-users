<?php

// SAML Metadata for Google Suite SSO
// The only value that needs to be changed is DOMAIN_NAME (See README.md)

$metadata['google.com'] = [
  'AssertionConsumerService'   => 'https://www.google.com/a/DOMAIN_NAME/acs', //Google ACS URL
  'NameIDFormat'               => 'urn:oasis:names:tc:SAML:1.1:nameid-format:emailAddress', //Google NameIDFormat
  'simplesaml.nameidattribute' => 'user', //Attribute returned by SimpleSAML that contains the user's Google Suite email address
  'simplesaml.attributes'      => false, //Whether or not Google should receive other attributes
];
