<?php
session_start();
require_once( "api.php" );
include_once($phpcas_path.'/CAS.php');

if ($_GET["url"] && $_SESSION["uid"])
{
	$u = User::find($_SESSION["uid"]);
	$u->add_click($url);
	header("Location: " . $_GET["url"]);
}
else if ($_SESSION["uid"])
{
	// User not logged in
}
else
{
	// No url
}
?>