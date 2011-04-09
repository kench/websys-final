<?php
/**
 * Recommender Service for Morning Mail
 *
 * FILE: database.class.php
 * COURSE: Web Systems Development
 * AUTHOR(s): Matthew Perry
 * DESCRIPTION:
 *  Class abstraction for mysql database
 *  queries.
 */

class database
{
    private $db;
    private $db_info;

    function database()
    {
        $db_info = parse_ini_file( dirname(__FILE__) . '/../config.ini' );
    }

    function connect()
    {
        if( !($db = mysql_connect( $db_info[ "host" ], $db_info[ "user" ], $db_info[ "pass" ] )) )
            die( "FATAL: Could not connect to DB server: " . mysql_error() );
        if( !mysql_select_db( $db_info[ "name" ], $db ) )
            die( "FATAL: Could not connect to DB: " . mysql_error() );
    }

    function query( $sql )
    {
        return mysql_query( $sql, $db );
    }

    function close()
    {
        if( !mysql_close( $db ) )
            die( "FATAL: Could not disconnect from DB: " . mysql_error() );
    }
}
?>
