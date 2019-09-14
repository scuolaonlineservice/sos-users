<?php
defined('_JEXEC') or die;

function add_user_to_groups(&$service, $email, $group_emails) {
  $app = JFactory::getApplication();

  foreach ($group_emails as $group_email) {
    $member = new Google_Service_Directory_Member();
    $member->setRole('MEMBER');
    $member->setEmail($email);

    try {
      $service->members->insert($group_email, $member);
      $app->enqueueMessage('Utente aggiunto al gruppo '.$group_email.' con successo.', 'message'); //TODO language
    } catch (Google_Service_Exception $error) {
      switch ($error->getCode()) {
        case 409:
          $app->enqueueMessage(
            'L\'utente fa già parte del gruppo '.$group_email.'.',
            'warning'
          );
          break;
        default:
          $app->enqueueMessage(
            "Impossibile aggiungere l'utente al gruppo ".$group_email.'.',
            'warning'
          );
          break;
      }
    }
  }
}
