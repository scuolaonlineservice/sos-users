<?php
namespace SimpleSAML\Module\joomlamodule\Auth\Source;

use SimpleSAML\Error\Exception;

class JoomlaAuth extends \SimpleSAML\Auth\Source {

  // ID of the initial stage of the authentication flow (required by SimpleSAML)
  const STAGE_INIT = 'joomlamodule:init';
  // ID of this authentication source (required by SimpleSAML)
  const AUTH_ID_INDEX = 'joomlamodule:AuthId';

  // Joomla! URL where we redirect the user in order for him to login
  private $redirect_url;
  // Joomla! URL that we use to verify that the user logged in successfully
  private $verify_url;

  public function __construct($info, $config) {
    // We call the parent constructor first
    parent::__construct($info, $config);

    // If no redirect_url is specified in the configuration, we throw an error
    if (!array_key_exists('redirect_url', $config)) {
      throw new \SimpleSAML\Error\Exception('Invalid config: missing redirect URL.');
    }
    // If no verify_url is specified in the configuration, we throw an error
    if (!array_key_exists('verify_url', $config)) {
      throw new \SimpleSAML\Error\Exception('Invalid config: missing verify URL.');
    }

    // We save these values inside JoomlaAuth in order to use them outside the constructor
    $this->redirect_url = $config['redirect_url'];
    $this->verify_url = $config['verify_url'];
  }

  // This function gets called at the beginning of the authentication flow
  public function authenticate(&$state) {
    // We save authentication state and store $state_id to be used later
    $state[self::AUTH_ID_INDEX] = $this->authId;
    $state_id = \SimpleSAML\Auth\State::saveState($state, self::STAGE_INIT);

    // We build the redirect URL
    $login_URL =
      $this->redirect_url. //Redirect URL from config
      (strpos($this->redirect_url, '?') ? '&' : '?').'redirect_uri='. //Checks whether or not the url contains a query string already
      urlencode(
        \SimpleSAML\Module::getModuleURL('joomlamodule').'/linkback.php'. //linkback.php URL
        '?state_id='.base64_encode($state_id) //state_id we saved before
      );
    try {
      // We redirect the user
      \SimpleSAML\Utils\HTTP::redirectTrustedURL($login_URL);
      // After the users logs in, the flow continues in (...)/www/linkback.php
    } catch (Exception $error) {
      // If redirect_url is invalid, we throw an error
      throw new Exception('Cannot redirect to redirect_url. Please check your configuration.');
    }
  }

  // This function gets called from linkback.php after the user logs into Joomla!
  public function verify_token(&$state) {
    // We build the verify url
    $verify_url =
      $this->verify_url. //verify_url from config
      (strpos($this->verify_url, '?') ? '&' : '?').'task=verify_token' //we check for a querystring and add a parameter
      .'&token='.$state['joomlamodule:verification_token']; //we add the token parameter

    // Then we call the URL
    $contents = file_get_contents($verify_url);

    // And check for a valid response
    if ($contents === false) {
      throw new \SimpleSAML\Error\Exception('Joomla Auth source not reachable. Please retry later.');
    }

    // If it's valid, we parse the JSON response
    $contents = json_decode($contents);

    // And we check for errors
    if (!$contents->status) {
      throw new \SimpleSAML\Error\Exception($contents->message);
    }

    // Finally, we set the user attribute. The value is the Google account email address that will be accessed by the user
    $state['Attributes'] = ['user' => [$contents->user]];
  }

  // This function logs the user out of Joomla and SimpleSAML
  public function logout(&$state) {
    // We build the logout URL
    $logout_URL =
      $this->redirect_url. //redirect_url from config
      (strpos($this->redirect_url, '?') ? '&' : '?').'task=logout'; //we check for a querystring and add a parameter at the end.

    // We destroy SimpleSAML's session
    session_destroy();

    // And we redirect the user to the Joomla! logout url we built before.
    \SimpleSAML\Utils\HTTP::redirectTrustedURL($logout_URL);
  }
}
