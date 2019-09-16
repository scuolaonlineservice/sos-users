<?php
defined('_JEXEC') or die;

function patch_user(&$service, $old_email, $new_email, $first_name, $family_name, $password = null){
  $user = new Google_Service_Directory_User();
  $app = JFactory::getApplication();

  $username = new Google_Service_Directory_UserName();
  $username->givenName = $first_name;
  $username->familyName = $family_name;
  $username->fullName = $full_name;

  $user->setId($id);
  $user->setPrimaryEmail($new_email);
  $user->setName($username);
  $user->setPassword($password === '' ? null : $password);

  try {
      $service->users->update($old_email, $user);
  } catch (Google_Service_Exception $error) {
    switch ($error->getCode()) {
      case 403:
        throw new Exception(
          'Google Sync: Impossibile creare l\'utente. Controlla di aver inserito un indirizzo email corretto.',
          403
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
          $error->getCode()
        );
        break;
    }
  }

  $app->enqueueMessage(
    'Google Sync: Utente Google modificato con successo.',
    'message'
  );
}
