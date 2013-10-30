<?php

PHPWS_Core::initModClass('sdr', 'Environment.php');

$env = \sdr\Environment::getInstance();
$env->bootstrap();

PHPWS_Core::initModClass('sdr', 'SDRFactory.php');
$controller = SDRFactory::getSDR();

try {
    javascript('jquery');
    $controller->process();
} catch(Exception $e) {
    $env->handleException($e, 'Uncaught Exception from direct PHP');
    $env->errorBack();
}

$env->unbootstrap();

?>
