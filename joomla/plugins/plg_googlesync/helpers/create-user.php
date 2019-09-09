<?php
defined('_JEXEC') or die;

function create_user(&$service, $email, $full_name, $password = null, $id = null) {
  $id = ($id === 0) ? null : $id;
  $name = explode(" ", $full_name, 2)[0];
  $surname = explode(" ", $full_name, 2)[1];

  $user = new Google_Service_Directory_User();
  $app = JFactory::getApplication();

  $username = new Google_Service_Directory_UserName();
  $username->givenName = $name;
  $username->familyName = $surname;
  $username->fullName = $full_name;

  $user->setId($id);
  $user->setPrimaryEmail($email);
  $user->setName($username);
  $user->setPassword($password);

  try {
    $service->users->insert($user);
  } catch (Google_Service_Exception $error) {
    switch ($error->getCode()) {
      case 403:
        throw new Exception('Impossibile creare l\'utente. Controlla di aver inserito un indirizzo email corretto.', 403);
        break;
      default:
        throw new Exception('Errore. Se l\'errore persiste contatta un amministratore.', 400);
        break;
        //TODO finish error handling
    }
  }

  $app->enqueueMessage('Utente Google creato con successo.', 'message');
  //TODO test multiple users deleted at the same time
}
