<?php
include_once( "CAS.php" );

$info = parse_ini_file( 'config.ini', true );
phpCAS::client(CAS_VERSION_2_0, $info['cas']['host'], (int)$info['cas']['port'], $info['cas']['context']);

if( isset( $_GET["logout"] ) )
{
	// Logout
    $_SESSION = array();
    session_destroy();
    session_start();
    phpCAS::logout();
}
else if( !isset( $_SESSION["cas"] ) || !$_SESSION["cas"] )
{
    phpCAS::setNoCasServerValidation();
	phpCAS::forceAuthentication();
	$_SESSION["uid"] = phpCAS::getUser();
    $_SESSION["cas"] = true;
	header( "Location: index.php" );
}
?>
