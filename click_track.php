<?php
session_start();
require_once( "lib/api.php" );

if( isset( $_GET["url"] ) )
{
	if( isset( $_SESSION["uid"] ) )
	    User::find( $_SESSION["uid"] )->add_click( $_GET["url"] );
	header( "Location: " . html_entity_decode( $_GET["url"] ) );
}
?>
