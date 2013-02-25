<?php
/*
 * Incomming parameters:
 *  url
 *  ticket
 *
 */

if (!array_key_exists('url', $_GET))
    throw new Exception('Required URL query parameter [url] not provided. (CAS Server)');

$url = $_GET['url'];

/* Load simpleSAMLphp, configuration and metadata */
$casconfig = SimpleSAML_Configuration::getConfig('module_sbcasserver.php');

$as = new SimpleSAML_Auth_Simple($casconfig->getValue('authsource'));

SimpleSAML_Logger::debug('sbcasserver config' . var_export($as, TRUE));

$session = SimpleSAML_Session::getInstance();

if (!is_null($session)) {
    $ticketStoreConfig = $casconfig->getValue('ticketstore', array('class' => 'sbcasserver:FileSystemTicketStore'));
    $ticketStoreClass = SimpleSAML_Module::resolveClass($ticketStoreConfig['class'], 'Cas_Ticket');
    $ticketStore = new $ticketStoreClass($casconfig);

    $ticketStore->deleteTicket($session->getSessionId());
}

if ($as->isAuthenticated()) {
    SimpleSAML_Logger::debug('sbcasserver logged out: real logout');

    $as->logout($url);
} else {
    SimpleSAML_Logger::debug('sbcasserver logged out: redirected');

    SimpleSAML_Utilities::redirect(SimpleSAML_Utilities::addURLparameter($url, array()));
}
?>

