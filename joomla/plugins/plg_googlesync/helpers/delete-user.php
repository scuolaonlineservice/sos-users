<?php
defined('_JEXEC') or die;

function delete_user(&$service, $user_key) {
  $app = JFactory::getApplication();

  try {
    $service->users->delete($user_key);
  } catch (Google_Service_Exception $error) {
    switch ($error->getCode()) {
      case 403:
        $app->enqueueMessage('Errore (403). Impossibile rimuovere l\'utente. Se l\'errore persiste contatta un amministratore.', 'error');
        break;
      default:
        $app->enqueueMessage('Errore. Se l\'errore persiste contatta un amministratore.', 'error');
        break;
        //TODO finish error handling
    }

    $app->redirect(JRoute::_('index.php?option=com_users'));
  }

  $app->enqueueMessage('Utente Google rimosso con successo.', 'message');
}
