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
    $client->setAuthConfig(JPATH_ROOT.'/plugins/user/googlesync/credentials.json'); //TODO config

    $this->client = $client;
    $this->service = new Google_Service_Directory($this->client);
    $this->app = JFactory::getApplication();

    require __DIR__.'/helpers/create-user.php';
    require __DIR__.'/helpers/patch-user.php';
    require __DIR__.'/helpers/delete-user.php';
    require __DIR__.'/helpers/create-group.php';
    require __DIR__.'/helpers/patch-group.php';
    require __DIR__.'/helpers/delete-group.php';
    require __DIR__.'/helpers/add-user-to-groups.php';
    require __DIR__.'/helpers/remove-user-from-groups.php';
  }

  function get_group_email_by_id($id) {
    $db = JFactory::getDbo();
    $query = $db->getQuery(true);

    $query
      ->select('title')
      ->from($db->quoteName('#__usergroups'))
      ->where($db->quoteName('id').' = '.$db->quote($id));

    try {
      $db->setQuery($query);
      return explode('@', $db->loadObject()->title, 2)[1];
    } catch (Exception $_) {
      return null;
    }
  }

  function onUserBeforeSave($old_user, $is_new, $new_user) {
    $first_name = explode(" ", $new_user['name'], 2)[0];
    $family_name = explode(" ", $new_user['name'], 2)[1];

    if (!$first_name || !$family_name) {
      throw new Exception('L\'utente deve possedere nome e cognome, separati da spazio', 403); //TODO lingua
    }
    if ($new_user['password_clear'] && strlen($new_user['password_clear']) < 8) {
      throw new Exception('La password deve contenere almeno otto caratteri.', 403); //TODO lingua
    }

    if ($is_new) {
      create_user($this->service, $new_user['email'], $first_name, $family_name, $new_user['password_clear'], $new_user['id']);

      $groups = [];
      foreach ($new_user['groups'] as $group_id) {
        $group_email = $this->get_group_email_by_id($group_id);
        if ($group_email) {
          array_push($groups, $group_email.'@liceoariostospallanzani-re.edu.it'); //TODO domain
        }
      }

      add_user_to_groups($this->service, $new_user['email'], $groups);
    } else {
      patch_user($this->service, $old_user['email'], $new_user['email'], $first_name, $family_name, $new_user['password_clear']);

      $new_groups = [];
      foreach ($new_user['groups'] as $group_id) {
        $group_email = $this->get_group_email_by_id($group_id);
        if ($group_email) {
          array_push($new_groups, $group_email); //TODO domain
        }
      }

      $old_groups = [];
      foreach ($old_user['groups'] as $group_id) {
        $group_email = $this->get_group_email_by_id($group_id);
        if ($group_email) {
          array_push($old_groups, $group_email); //TODO domain
        }
      }

      $groups_to_join = [];
      foreach ($new_groups as $group_email) {
        if (!in_array($group_email, $old_groups)) {
          array_push($groups_to_join, $group_email.'@liceoariostospallanzani-re.edu.it'); //TODO domain
        }
      }

      $groups_to_leave = [];
      foreach ($old_groups as $group_email) {
        if (!in_array($group_email, $new_groups)) {
          array_push($groups_to_leave, $group_email.'@liceoariostospallanzani-re.edu.it'); //TODO domain
        }
      }

      add_user_to_groups($this->service, $new_user['email'], $groups_to_join);
      remove_user_from_groups($this->service, $new_user['email'], $groups_to_leave);
    }
	}

  function onUserBeforeDelete($user) {
    delete_user($this->service, $user['email']);
  }

  function onUserBeforeSaveGroup($_, $__, $is_new, $group) {
    $name = explode('@', $group['title'], 2)[0];
    $email = explode('@', $group['title'], 2)[1];

    if (!$name) {
      throw new Exception('Perfavore, assegna un nome al gruppo.');//TODO lingua
    }
    if (!$email) {
      $this->app->enqueueMessage('Nessuna mail inserita. Il gruppo Google non verrà creato.', 'warning'); //TODO language
      return;
    }

    if ($is_new) {
      create_group($this->service, $name, $email.'@liceoariostospallanzani-re.edu.it'); //TODO config
    } else {
      $old_email = $this->get_group_email_by_id($group['id']);

      if (!$old_email) {
        throw new Exception('Impossibile aggiungere mail ad un gruppo già esistente.'); //TODO lingua
      }

      patch_group($this->service, $old_email.'@liceoariostospallanzani-re.edu.it', $name, $email.'@liceoariostospallanzani-re.edu.it'); //TODO config
    }
  }

  function onUserBeforeDeleteGroup($group) {
    $group_email = explode('@', $group['title'], 2)[1];

    if (!$group_email) {
      $this->app->enqueueMessage('Gruppo non presente su Google.', 'notice'); //TODO language
      return;
    }

    delete_group($this->service, $group_email.'@liceoariostospallanzani-re.edu.it'); //TODO config
  }
}
