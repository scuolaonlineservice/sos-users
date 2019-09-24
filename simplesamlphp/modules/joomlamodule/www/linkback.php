<?php
// The user gets redirected here once they log in successfully

// We check that the user didn't skip any steps in the authentication flow by checking for a state_id in their request
if (!array_key_exists('state_id', $_REQUEST)) {
  throw new \SimpleSAML\Error\Exception('Lost Client State. Please retry');
}

// And we check that the id represents a valid state (loadState will throw otherwise)
$state = \SimpleSAML\Auth\State::loadState(
  base64_decode($_REQUEST['state_id']),
  \SimpleSAML\Module\joomlamodule\Auth\Source\JoomlaAuth::STAGE_INIT
);

// We check that the user got a token after logging in
if (!array_key_exists('token', $_REQUEST)) {
  throw new SimpleSAML\Error\Exception('Missing token. Please retry');
}

// And we save it in the state variable (required by SimpleSAML)
$state['joomlamodule:verification_token'] = $_REQUEST['token'];

// We tried to load our authentication source using the ID stored in state
assert(array_key_exists(\SimpleSAML\Module\joomlamodule\Auth\Source\JoomlaAuth::AUTH_ID_INDEX, $state));
$sourceId = $state[\SimpleSAML\Module\joomlamodule\Auth\Source\JoomlaAuth::AUTH_ID_INDEX];

$source = \SimpleSAML\Auth\Source::getById($sourceId);

// If there is no source with such ID, we throw
if ($source === null) {
  throw new \Exception("Could not find authentication source with id $sourceId");
}

// Finally, we call verify_token on (...)/JoomlaAuth.php
$source->verify_token($state);

// And then we end the authentication flow
\SimpleSAML\Auth\Source::completeAuth($state);
