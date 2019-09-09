<?php
defined('_JEXEC') or die;

function add_user_to_groups(&$client, $groups, $email) {
  $client->setUseBatch(true);
  try {
    $batch = $this->service->createBatch();
    foreach ($groups as $groupId){
      $batch->add($this->addUserToGroup($email, $groupId)->result);
    }

    $batch->execute();
  } catch (Google_Service_Exception $googleServiceException) {
    $this->fillResponse($response, $googleServiceException);
  } finally {
    $this->client->setUseBatch(false);
  }
}
