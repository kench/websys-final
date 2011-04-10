<?php
/**
 * Recommender Service for Morning Mail
 *
 * FILE: database.class.php
 * COURSE: Web Systems Development
 * AUTHOR(s): Matthew Perry
 * DESCRIPTION:
 *  Singleton class abstraction for all 
 *  database queries.
 */

class Database
{
    private static $link = null;

    // Static function to establish a connection
    // with the database, not called directly
    private static function getLink() 
    {
        // Return $link variable if its defined
        if( self::$link ) return self::$link;

        // Parse the .ini file for the db information
        $info = parse_ini_file( dirname(__FILE__) . '/../config.ini', true );

        // Set up the Data Source Name, which contains the info for
        // the DB connection
        $dsn = "{$info['driver']}:";
        foreach( $info['dsn'] as $key => $value )
            $dsn .= "$key=$value;";

        // Actually connect
        self::$link = new PDO( $dsn, $info['user'], $info['pass'] );
        return self::$link;
    }

    // This is a special PHP member function which takes
    // in any attempted call to a static member of this class
    // which isnt explicitly defined.
    //
    // This particular implementation just redirects the static
    // call to the $link variable which is a PDO in this case
    public static function __callStatic( $name, $args )
    {
        $callback = array( self::getLink(), $name );
        return call_user_func_array( $callback, $args );
    }
}
?>
