<?php
defined('_JEXEC') or die;

class Response{
  public $statusCode = 200;
  public $errorMessage = null;
  public $result = null;

  public function getStatusCode()
  {
    return $this->statusCode;
  }
}

function action($service, $email, $givenName = '', $familyName = '', $password = '', $id = null) {
  $user = new Google_Service_Directory_User();
  if($id != null) {
    $user->setId($id);
  }

  $user->setPrimaryEmail($email);
  //die(var_dump($user));
  $username = new Google_Service_Directory_UserName();
  $username->givenName = $givenName;
  $username->familyName = $familyName;
  $username->fullName = $givenName . " " . $familyName;
  $user->setName($username);

  $user->setPassword($password);

  $response = new Response();
  try{
    $response = $service->users->insert($user);
  } catch (Google_Service_Exception $googleServiceException) {
    die($googleServiceException);
  }
  //die(var_dump($response));
  die();
}
