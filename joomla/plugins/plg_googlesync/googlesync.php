<?php
defined('_JEXEC') or die;

define('SCOPES', implode(' ', array(
    Google_Service_Directory::ADMIN_DIRECTORY_USER,
    Google_Service_Directory::ADMIN_DIRECTORY_GROUP,
  )
));

class plgUserGoogleSync extends JPlugin {

  private $app;
  private $auth_credentials;
  private $user_to_impersonate;
  private $domain;
  private $client;
  private $service;

  public function __construct(&$subject, $config = array()) {
    parent::__construct($subject, $config);

    $this->auth_credentials = json_decode($this->params->get('auth_credentials'), true);
    $this->user_to_impersonate = $this->params->get('user_to_impersonate');
    $this->domain = $this->params->get('domain');
    $this->app = JFactory::getApplication();

    $client = new Google_Client();
    $client->setSubject($this->user_to_impersonate);
    $client->setScopes(SCOPES);
    $client->setAuthConfig($this->auth_credentials);

    $this->client = $client;
    $this->service = new Google_Service_Directory($this->client);

    require __DIR__.'/helpers/create-user.php';
    require __DIR__.'/helpers/patch-user.php';
    require __DIR__.'/helpers/delete-user.php';
    require __DIR__.'/helpers/create-group.php';
    require __DIR__.'/helpers/patch-group.php';
    require __DIR__.'/helpers/delete-group.php';
    require __DIR__.'/helpers/add-user-to-groups.php';
    require __DIR__.'/helpers/remove-user-from-groups.php';

    if (!isset($this->auth_credentials)) {
      $this->app->enqueueMessage(
        'Credeniali per Google Sync non impostate. Disabilita il plugin o configurale prima di continuare.',
        'error'
      );
    }
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
      return $db->loadObject()->title;
    } catch (Exception $_) {
      return null;
    }
  }

  function onUserBeforeSave($old_user, $is_new, $new_user) {
    $first_name = explode(' ', $new_user['name'], 2)[0];
    $family_name = explode(' ', $new_user['name'], 2)[1];

    if (!$first_name || !$family_name) {
      throw new Exception(
        'Google Sync: L\'utente deve possedere nome e cognome, separati da spazio.',
        403
      );
    }
    if ($new_user['password_clear'] && strlen($new_user['password_clear']) < 8) {
      throw new Exception(
        'Google Sync: La password deve contenere almeno otto caratteri.',
        403
      );
    }

    if ($is_new) {
      create_user($this->service, $new_user['email'], $first_name, $family_name, $new_user['password_clear'], $new_user['id']);

      $groups = [];
      foreach ($new_user['groups'] as $group_id) {
        $group_email = $this->get_group_email_by_id($group_id);
        if ($group_email) {
          array_push($groups, $group_email.'@'.$this->domain);
        }
      }

      add_user_to_groups($this->service, $new_user['email'], $groups);
    } else {
      patch_user($this->service, $old_user['email'], $new_user['email'], $first_name, $family_name, $new_user['password_clear']);

      $new_groups = [];
      foreach ($new_user['groups'] as $group_id) {
        $group_email = $this->get_group_email_by_id($group_id);
        if ($group_email) {
          array_push($new_groups, $group_email);
        }
      }

      $old_groups = [];
      foreach ($old_user['groups'] as $group_id) {
        $group_email = $this->get_group_email_by_id($group_id);
        if ($group_email) {
          array_push($old_groups, $group_email);
        }
      }

      $groups_to_join = [];
      foreach ($new_groups as $group_email) {
        if (!in_array($group_email, $old_groups)) {
          array_push($groups_to_join, $group_email.'@'.$this->domain);
        }
      }

      $groups_to_leave = [];
      foreach ($old_groups as $group_email) {
        if (!in_array($group_email, $new_groups)) {
          array_push($groups_to_leave, $group_email.'@'.$this->domain);
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
    $email = $group['title'];

    if ($is_new) {
      create_group($this->service, $name, $email.'@'.$this->domain);
    } else {
      $old_email = $this->get_group_email_by_id($group['id']);

      if (!$old_email) {
        throw new Exception(
          'Google Sync: Impossibile aggiungere mail ad un gruppo Joomla! già esistente.'
        );
      }

      patch_group($this->service, $old_email.'@'.$this->domain, $email, $email.'@'.$this->domain);
    }
  }

  function onUserBeforeDeleteGroup($group) {
    delete_group($this->service, $group['title'].'@'.$this->domain);
  }
}
