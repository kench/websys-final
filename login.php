<?php
$info = parse_ini_file( dirname(__FILE__) . '/config.ini', true );
phpCAS::client(CAS_VERSION_2_0, $info["cas_host"], $info["cas_port"], $info["cas_context"]);

include_once($phpcas_path.'/CAS.php');

session_start();
if($_GET["logout"])
{
	// Logout
	$_SESSION["uid"] = NULL;
	phpCAS::logout();
}
else if ($_SESSION["uid"])
{
	// Already logged in.
	$_SESSION["uid"] = phpCAS::getUser();
}
else
{
	phpCAS::forceAuthentication();
	$_SESSION["uid"] = phpCAS::getUser();
}
?>