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
        throw new Exception('Google: Gruppo già esistente.', 409);//TODO lingua
        break;
      case 400:
        throw new Exception('Google: Errore nei dati inseriti.', 400);//TODO lingua
        break;
      default:
        throw new Exception('Google: Errore. Se l\'errore persiste contatta un amministratore.', 400); //TODO lingua
        break;
    }
  }

  $app->enqueueMessage('Gruppo Google creato con successo.', 'message'); //TODO Language
}
