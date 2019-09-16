<?php
defined('_JEXEC') or die;

function delete_group(&$service, $group_mail) {
  $app = JFactory::getApplication();

  try {
    $service->groups->delete($group_mail);
  } catch (Google_Service_Exception $error) {
    switch ($error->getCode()) {
      case 404:
        $app->enqueueMessage(
          'Google Sync: Gruppo '.$group_email.' non esistente su Google: non verrà sincronizzato.',
          'warning'
        );
        break;
      case 403:
        throw new Exception(
          'Google Sync: Errore. Impossibile rimuovere il gruppo. Se l\'errore persiste contatta un amministratore.',
          '403'
        );
        break;
      default:
        throw new Exception(
          'Google Sync: Errore . Se l\'errore persiste contatta un amministratore.',
          $error->getCode()
        );
        break;
    }
  }

  $app->enqueueMessage(
    'Google Sync: Gruppo Google rimosso con successo.',
    'message'
  );
}
