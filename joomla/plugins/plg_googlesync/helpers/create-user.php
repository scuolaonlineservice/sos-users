<?php
defined('_JEXEC') or die;

function action($service, $email, $givenName, $familyName, $password, $id = null) {
  $user = new Google_Service_Directory_User();
  if($id != null) {
    $user->setId($id);
  }

  $user->setPrimaryEmail($email);

  $username = new Google_Service_Directory_UserName();
  $username->givenName = $givenName;
  $username->familyName = $familyName;
  $username->fullName = $givenName . " " . $familyName;
  $user->setName($username);

  $user->setPassword($password);
  try {
    $service->users->insert($user);
  } catch (Google_Service_Exception $err) {
    die($err);
  }
  die();
  //$response = $service->users->getUsers();
}
