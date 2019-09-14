<?php
defined('_JEXEC') or die;

function create_group(&$service, $name, $email) {
  $app = JFactory::getApplication();
  $group = new Google_Service_Directory_Group();

  $group->setEmail($email);
  $group->setName($name);

  try {
    $service->groups->insert($group);
  } catch (Google_Service_Exception $error) {
    switch ($error->getCode()) {
      case 409:
        throw new Exception(
          'Google Sync: Gruppo già esistente.',
          409
        );
        break;
      case 400:
        throw new Exception(
          'Google Sync: Errore nei dati inseriti. Controlla la configurazione del plugin "SOS Google Sync"',
          400
        );
        break;
      default:
        throw new Exception(
          'Google Sync: Errore. Se l\'errore persiste contatta un amministratore.',
          400
        );
        break;
    }
  }

  $app->enqueueMessage(
    'Gruppo Google creato con successo.',
    'message'
  );
}
