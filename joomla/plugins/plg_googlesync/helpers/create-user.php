<?php
defined('_JEXEC') or die;

function create_user(&$service, $email, $full_name, $password = null, $id = null) {
  $id = ($id === 0) ? null : $id;
  $user = new Google_Service_Directory_User();

  $name = explode(" ", $full_name, 2)[0];
  $surname = explode(" ", $full_name, 2)[1];

  $username = new Google_Service_Directory_UserName();
  $username->givenName = $name;
  $username->familyName = $surname;
  $username->fullName = $full_name;

  $user->setId($id);
  $user->setPrimaryEmail($email);
  $user->setName($username);
  $user->setPassword($password);

  try {
    $response = $service->users->insert($user);
  } catch (Google_Service_Exception $googleServiceException) {
    die($googleServiceException);
  }
  return $response;
}
