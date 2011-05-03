<?php
$info = parse_ini_file( dirname(__FILE__) . '/config.ini', true );
phpCAS::client(CAS_VERSION_2_0, $info['cas']['host'], $info['cas']['port'], $info['cas']['context']);

require_once( "api.php" );
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
	if (!($u = User::find($_SESSION["uid"])))
	{
		$u = new User($_SESSION["uid"]);
	}
}
else
{
	phpCAS::forceAuthentication();
	$_SESSION["uid"] = phpCAS::getUser();
	if (!($u = User::find($_SESSION["uid"])))
	{
		$u = new User($_SESSION["uid"]);
	}
}
?>
