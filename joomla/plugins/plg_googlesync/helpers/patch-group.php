<?php
defined('_JEXEC') or die;

function patch_group(&$service, $old_email, $new_name, $new_email) {
  $group = new Google_Service_Directory_Group();
  $app = JFactory::getApplication();

  $group->setEmail($new_email);
  $group->setName($new_name);

  try {
    $service->groups->update($old_email, $group);
  } catch (Google_Service_Exception $error){
    switch ($error->getCode()) {
      case 403:
        throw new Exception(
          'Impossibile modificare il gruppo. Controlla di aver inserito un indirizzo email corretto.',
          403
        );
        break;
      case 400:
        throw new Exception(
          'Errore nei dati inseriti.',
          400
        );
        break;
      default:
        throw new Exception(
          'Google Sync: Errore. Se l\'errore persiste contatta un amministratore.',
          $error->getCode()
        );
        break;
    }
  }

  $app->enqueueMessage(
    'Google Sync: Gruppo Google modificato con successo.',
    'message'
  );
}
