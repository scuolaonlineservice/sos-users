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
        throw new Exception('Impossibile modificare il gruppo. Controlla di aver inserito un indirizzo email corretto.', 403); //TODO lingua
        break;
      case 400:
        throw new Exception('Errore nei dati inseriti.', 400);//TODO lingua
        break;
      default:
        throw new Exception('Errore. Se l\'errore persiste contatta un amministratore.', 400); //TODO lingua
        break;
    }
  }

  $app->enqueueMessage('Gruppo Google modificato con successo.', 'message'); //TODO Language
}
