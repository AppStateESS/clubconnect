<?php

if (PHPWS_Core::atHome() && Current_User::isLogged()) {
	$path = $_SERVER['SCRIPT_NAME'].'?module=sdr';

	header('HTTP/1.1 303 See Other');
	header("Location: $path");
    exit();
}

?>
