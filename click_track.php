<?php
session_start();
require_once( "api.php" );
include_once($phpcas_path.'/CAS.php');

if ($_GET["url"])
{
	if ($_SESSION["uid"])
	{
		$u = User::find($_SESSION["uid"]);
	}
	else
	{
		$u = User::find(session_id());
	}
	$u->add_click($url);
	header("Location: " . $_GET["url"]);
}
else
{
	// No url
}
?>