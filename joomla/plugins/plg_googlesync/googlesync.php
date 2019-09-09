<?php
defined('_JEXEC') or die;

define('SCOPES', implode(' ', array(
    Google_Service_Directory::ADMIN_DIRECTORY_USER,
    Google_Service_Directory::ADMIN_DIRECTORY_GROUP,
    Google_Service_Reports::ADMIN_REPORTS_AUDIT_READONLY,
    Google_Service_Reports::ADMIN_REPORTS_USAGE_READONLY
  )
));

class plgUserGoogleSync extends JPlugin {

  private $client;
  private $service;

  public function __construct(&$subject, $config = array()) {
    parent::__construct($subject, $config);

    $this->client = new Google_Client();
    $this->client->setSubject("admin@liceoariostospallanzani-re.edu.it");
    $this->client->setScopes(SCOPES);
    $this->client->setAuthConfig(JPATH_ROOT.'/plugins/user/googlesync/credentials.json');
    $this->service = new Google_Service_Directory($this->client);
  }

  function onUserBeforeSave($old_user, $is_new, $new_user) {
   require __DIR__.'/helpers/create-user.php';
   action($this->service, "test-joomla", "given_name", "family_name", "password");
	}

  function onUserBeforeDelete($user) {
    die("die");
  }
}
