<?php
defined( '_JEXEC' ) or die;

define('SCOPES', implode(' ', array(
    Google_Service_Directory::ADMIN_DIRECTORY_USER,
    //Google_Service_Directory::ADMIN_DIRECTORY_GROUP,
    //Google_Service_Reports::ADMIN_REPORTS_AUDIT_READONLY,
    //Google_Service_Reports::ADMIN_REPORTS_USAGE_READONLY
  )
));

$client = new Google_Client();
$client->setScopes(SCOPES);
$client->setAuthConfig(JPATH_COMPONENT . '/credentials.json');

class plgUserGoogleSync extends JPlugin {
  function onUserBeforeSave($old_user, $is_new, $new_user) {

	}
  function onUserBeforeDelete($user) {

  }
}
