<?php
defined('_JEXEC') or die;

function remove_user_from_groups(&$service, $email, $group_emails) {
  $app = JFactory::getApplication();

  foreach ($group_emails as $group_email) {
    try {
      $service->members->delete($group_email, $email);
      $app->enqueueMessage('Utente rimosso dal gruppo '.$group_email.' con successo.', 'message'); //TODO language
    } catch (Google_Service_Exception $error) {
      switch ($error->getCode()) {
        default:
          $app->enqueueMessage("Impossibile rimuovere l'utente dal gruppo ".$group_email.'.', 'warning'); //TODO language
          break;
      }
    }
  }
}
