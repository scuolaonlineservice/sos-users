<?php
defined('_JEXEC') or die('Restricted access');

class SAMLLoginViewSAMLLogin extends \Joomla\CMS\MVC\View\HtmlView {
	function display($tpl = null) {
    $app = JFactory::getApplication();
    $db = JFactory::getDbo();
    $input = $app->input;

    $redirect_uri =
      $input->get('redirect_uri', false, 'STRING') ?:
      base64_decode($input->get('redirect_uri_base64', false, 'BASE64'));

    //If the user gets here without going through SimpleSAML authentication flow, we display an error
    if (!$redirect_uri) {
      $this->msg='Qualcosa è andato storto, riprova.';
      return parent::display($tpl);
    }

    $id = JFactory::getUser()->id;
    if ($id === 0)  {
      $redirect_url = urlencode(base64_encode("index.php?option=com_samllogin&redirect_uri_base64=" . base64_encode($redirect_uri)));
      $app->redirect("index.php?option=com_users&view=login&return=$redirect_url",'');
    }

    $token = $db->quote(JFactory::getSession()->getId());
    $exp = 'FROM_UNIXTIME('.(time() + 1 * 60).')';

    $columns = array('token', 'user_id', 'exp');
    $values = array($token, $id, $exp);

    $query = $db->getQuery(true);
    $query
      ->insert($db->quoteName('#__LoginTokens'))
      ->columns($db->quoteName($columns))
      ->values(implode(',', $values));
    $query.=
      ' ON DUPLICATE KEY UPDATE '.
      $db->quoteName('token')." = $token, ".
      $db->quoteName('exp')." = $exp";

    $db->setQuery($query);
    $db->execute();

    $app->redirect(
      $redirect_uri.
      (strpos($redirect_uri, '?') ? '&' : '?').
      'token='.str_replace('\'',"", $token)
    );
	}
}
