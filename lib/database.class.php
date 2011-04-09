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
require_once( dirname( __FILE__ ) . '/../config.inc.php' );

class database
{
    private $db;
    private $db_info;

    function database()
    {
        $db_info = new stdClass();
        $db_info->db_host = DB_HOST;
        $db_info->db_user = DB_USER;
        $db_info->db_pass = DB_PASS;
        $db_info->db_name = DB_NAME;
    }

    function connect()
    {
        if( !($db = mysql_connect( $db_info->db_host, $db_info->db_user, $db_info->db_pass )) )
            die( "FATAL: Could not connect to DB server: " . mysql_error() );
        if( !mysql_select_db( $db_info->db_name, $db ) )
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
