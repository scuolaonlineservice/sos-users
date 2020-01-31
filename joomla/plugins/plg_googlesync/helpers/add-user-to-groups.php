<?php
defined('_JEXEC') or die;

function add_user_to_groups(&$service, $email, $group_emails) {
  $app = JFactory::getApplication();

  foreach ($group_emails as $group_email) {
    $member = new Google_Service_Directory_Member();
    $member->setRole('MEMBER');
    $member->setEmail($email);

    try {
      $service->members->insert($group_email, $member);
      $app->enqueueMessage(
        'Google Sync: Utente aggiunto al gruppo '.$group_email.' con successo.',
        'message'
      );
    } catch (Google_Service_Exception $error) {
      switch ($error->getCode()) {
        case 409:
          $app->enqueueMessage(
            'Google Sync: L\'utente fa già parte del gruppo '.$group_email.'.',
            'warning'
          );
          break;
        case 404:
          $app->enqueueMessage(
            'Google Sync: Gruppo '.$group_email.' non esistente su Google: non verrà sincronizzato.',
            'warning'
          );
          break;
        default:
          $app->enqueueMessage(
            'Google Sync: Impossibile aggiungere l\'utente al gruppo '.$group_email.'.',
            'warning'
          );
          break;
      }
    }
  }
}
