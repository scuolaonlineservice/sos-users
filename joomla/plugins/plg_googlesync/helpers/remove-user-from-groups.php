<?php
defined('_JEXEC') or die;

function remove_user_from_groups(&$service, $email, $group_emails) {
  $app = JFactory::getApplication();

  foreach ($group_emails as $group_email) {
    try {
      $service->members->delete($group_email, $email);
      $app->enqueueMessage(
        'Google Sync: Utente rimosso dal gruppo '.$group_email.' con successo.',
        'message'
      );
    } catch (Google_Service_Exception $error) {
      switch ($error->getCode()) {
        case 404:
          $app->enqueueMessage(
            'Google Sync: Gruppo '.$group_email.' non esistente su Google: non verrà sincronizzato.',
            'warning'
          );
          break;
        default:
          $app->enqueueMessage(
            'Google Sync: Impossibile rimuovere l\'utente dal gruppo '.$group_email.'.',
            'warning'
          );
          break;
      }
    }
  }
}
