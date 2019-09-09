<?php
defined('_JEXEC') or die;

function delete_user(&$service, $user_key) {
  try {
    $service->users->delete($user_key);
  } catch (Google_Service_Exception $googleServiceException) {
    die($googleServiceException);
  }
}
