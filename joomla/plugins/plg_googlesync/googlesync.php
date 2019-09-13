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
  private $app;

  public function __construct(&$subject, $config = array()) {
    parent::__construct($subject, $config);

    $client = new Google_Client();
    $client->setSubject('admin@liceoariostospallanzani-re.edu.it'); //todo config
    $client->setScopes(SCOPES);
    $client->setAuthConfig(JPATH_ROOT.'/plugins/user/googlesync/credentials.json'); //TODO insert credentials

    $this->client = $client;
    $this->service = new Google_Service_Directory($this->client);
    $this->app = JFactory::getApplication();

    require __DIR__.'/helpers/create-user.php';
    require __DIR__.'/helpers/patch-user.php';
    require __DIR__.'/helpers/delete-user.php';
    require __DIR__.'/helpers/create-group.php';
    require __DIR__.'/helpers/patch-group.php';
    require __DIR__.'/helpers/delete-group.php';
    //require __DIR__.'/helpers/add-user-to-groups.php'; //TODO
    //require __DIR__.'/helpers/remove-user-from-groups.php'; //TODO
  }

  function get_group_title_by_id($id) {
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);

    $query
      ->select('title')
      ->from($db->quoteName('#__usergroups'))
      ->where($db->quoteName('id').' = '.$db->quote($id));

    $db->setQuery($query);
    return $db->loadObject()->title;
  }

  function onUserBeforeSave($old_user, $is_new, $new_user) {
    if ($is_new) { //TODO check empty password and empty name fields
      create_user($this->service, $new_user['email'], $new_user['name'], $new_user['password_clear'], $new_user['id']);
      //todo add_user_to_groups($this->client, );
    } else {
      patch_user($this->service, $old_user['email'], $new_user['email'], $new_user['name'], $new_user['password_clear']);
      //TODO patch user groups
    }
	}

  function onUserBeforeDelete($user) {
    delete_user($this->service, $user['email']);
  }

  function onUserBeforeSaveGroup($_, $__, $is_new, $group) {
    $name = explode('@', $group['title'], 2)[0];
    $email = explode('@', $group['title'], 2)[1];

    if (!isset($name) || $name === '') {
      throw new Exception('Perfavore, assegna un nome al gruppo.');//TODO lingua
    }
    if (!isset($email) || $email === '') {
      $this->app->enqueueMessage('Nessuna mail inserita. Il gruppo Google non verrà creato.', 'warning'); //TODO language
      return;
    }

    if ($is_new) {
      create_group($this->service, $name, $email.'@liceoariostospallanzani-re.edu.it'); //TODO test and config
    } else {
      $old_title = $this->get_group_title_by_id($group['id']);
      $old_email = explode('@', $old_title, 2)[1];

      if (!isset($old_email) || $old_email === '') {
        throw new Exception('Impossibile aggiungere mail ad un gruppo già esistente.'); //TODO lingua
      }

      patch_group($this->service, $old_email.'@liceoariostospallanzani-re.edu.it', $name, $email.'@liceoariostospallanzani-re.edu.it'); //TODO test and config
    }
  }

  function onUserBeforeDeleteGroup($group) {
    $group_email = explode('@', $group['title'], 2)[1];

    if (!isset($group_email) || $group_email === '') {
      $this->app->enqueueMessage('Gruppo non presente su Google.', 'notice'); //TODO language
      return;
    }

    delete_group($this->service, $group_email.'@liceoariostospallanzani-re.edu.it'); //TODO config
  }
}
