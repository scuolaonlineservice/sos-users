<?php
defined('_JEXEC') or die;

function delete_user(&$service, $user_key) {
  $app = JFactory::getApplication();

  try {
    $service->users->delete($user_key);
  } catch (Google_Service_Exception $error) {
    switch ($error->getCode()) {
      case 403:
        $app->enqueueMessage(
          'Google Sync: Errore (403). Impossibile rimuovere l\'utente. Se l\'errore persiste contatta un amministratore.',
          'error'
        );
        break;
      default:
        $app->enqueueMessage(
          'Google Sync: Errore . Se l\'errore persiste contatta un amministratore.',
          'error'
        );
        break;
    }

    $app->redirect(JRoute::_('index.php?option=com_users'));
  }

  $app->enqueueMessage(
    'Google Sync: Utente Google rimosso con successo.',
    'message'
  );
}
