<?php
session_start();
require_once( "lib/api.php" );

if( isset( $_GET["url"] ) )
{
    if( preg_match( "/(.*)\?(.*)/", $_GET["url"], $matches ) )
        $track_url = $matches[1] . '?' . str_replace( "/", "%2F", $matches[2] );
    else
        $track_url = $_GET["url"];

	if( isset( $_SESSION["uid"] ) )
	    User::find( $_SESSION["uid"] )->add_click( $track_url );
	header( "Location: " . $_GET["url"] );
}
?>
