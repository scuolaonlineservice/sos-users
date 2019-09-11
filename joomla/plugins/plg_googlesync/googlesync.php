<?php
defined('_JEXEC') or die;

define('SCOPES', implode(' ', array(
    Google_Service_Directory::ADMIN_DIRECTORY_USER,
    Google_Service_Directory::ADMIN_DIRECTORY_GROUP,
  )
));

class plgUserGoogleSync extends JPlugin {

  private $client;
  private $service;

  public function __construct(&$subject, $config = array()) {
    parent::__construct($subject, $config);
    $client = new Google_Client();
    $client->setSubject('admin@liceoariostospallanzani-re.edu.it');
    $client->setScopes(SCOPES);
    $client->setAuthConfig(JPATH_ROOT.'/plugins/user/googlesync/credentials.json'); //TODO insert credentials
    $this->client = $client;
    $this->service = new Google_Service_Directory($this->client);

    require __DIR__.'/helpers/create-user.php';
    require __DIR__.'/helpers/patch-user.php';
    require __DIR__.'/helpers/delete-user.php';
    //require __DIR__.'/helpers/create-group.php'; //TODO
    //require __DIR__.'/helpers/delete-group.php'; //TODO
    //require __DIR__.'/helpers/add-user-to-groups.php'; //TODO
    //require __DIR__.'/helpers/remove-user-from-groups.php'; //TODO
  }

  function onUserBeforeSave($old_user, $is_new, $new_user) {
    if ($is_new) { //TODO check empty password and empty name fields
      create_user($this->service, $new_user['email'], $new_user['name'], $new_user['password_clear'], $new_user['id']);
      //add_user_to_groups($this->client, ); //TODO
    } else {
      patch_user($this->service, $old_user['email'], $new_user['email'], $new_user['name'], $new_user['password_clear']);
      //TODO patch groups
    }
	}

  function onUserBeforeDelete($user) {
    delete_user($this->service, $user['email']);
  }

  function onUserBeforeSaveGroup($arg1, $arg2, $arg3, &$arg4) {
   //TODO
  }

  function onUserBeforeDeleteGroup() {
    //TODO
  }
}
