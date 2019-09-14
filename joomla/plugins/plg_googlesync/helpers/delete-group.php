<?php
defined('_JEXEC') or die;

function delete_group(&$service, $group_mail) {
  $app = JFactory::getApplication();

  try {
    $service->groups->delete($group_mail);
  } catch (Google_Service_Exception $error) {
    switch ($error->getCode()) {
      case 403:
        $app->enqueueMessage(
          'Errore (403). Impossibile rimuovere il gruppo. Se l\'errore persiste contatta un amministratore.',
          'error'
        );
        break;
      default:
        $app->enqueueMessage(
          'Errore . Se l\'errore persiste contatta un amministratore.',
          'error'
        );
        break;
    }

    $app->redirect(JRoute::_('index.php?option=com_users&view=groups'), false);
  }

  $app->enqueueMessage(
    'Gruppo Google rimosso con successo.',
    'message'
  );
}
