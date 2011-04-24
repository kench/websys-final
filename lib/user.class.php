<?php
/**
 * Recommender Service for Morning Mail
 *
 * FILE: user.class.php
 * COURSE: Web Systems Development
 * AUTHOR(s): Matthew Perry
 * DESCRIPTION:
 *  Class for manipulating users and
 *  their related data
 */
require_once( "api.php" );

class User
{
    // Static strings which represent the queries that this class performs
    // on a regular basis.
    //
    // This kind of definition allows for easier editing if a db scheme change
    // occurs.
    private static $SQL_CLICKS = "SELECT DISTINCT url FROM clicks WHERE uid = ?;";
    private static $SQL_PARENT = "SELECT parent FROM centers WHERE uid = ?;";
    private static $SQL_UIDS = "SELECT DISTINCT uid FROM clicks WHERE clicked_at > ?;";
    private static $SQL_UIDS_CLICKS = "SELECT * FROM clicks WHERE clicked_at > ?;";

    private static $SQL_ADD_CLICK = "INSERT INTO clicks VALUES( ?, ?, ? );";
    private static $SQL_ADD_PARENT = "INSERT INTO centers VALUES( ?, ? );";
    private static $SQL_SET_PARENT = "UPDATE centers SET parent = ? WHERE uid = ?";

    // Find a user by it's uid
    public static function find( $uid )
    {
        try
        {
            // Prepare the necessary queries
            $clicks = Database::prepare( self::$SQL_CLICKS );
            $pid = Database::prepare( self::$SQL_PARENT );

            // Execute the queries
            $clicks->execute( array( $uid ) );
            $pid->execute( array( $uid ) );

            // Fetch according to the symantics of the database
            // and return a new user with this information
            $clicks = $clicks->fetchAll( PDO::FETCH_COLUMN );
            $pid = $pid->fetch( PDO::FETCH_NUM );

            if( empty( $clicks ) ) $clicks = array();
            if( empty( $pid ) ) 
                $pid = null;
            else
                $pid = $pid[0];
            return new User( $uid, $clicks, $pid );
        } 
        catch( PDOException $e )
        {
            echo "Error: " . $e;
            return false;
        }
    }

    // Return all of the recorded UIDs of users
    public static function get_raw_data( $opts = false )
    {
        // Psuedo unordered default arguements
        $clicks = false; $time = 0;
        if( $opts ) extract( $opts );
        try
        {
            // Prepare the necessary queries
            if( $clicks )
                $uids = Database::prepare( self::$SQL_UIDS_CLICKS );
            else
                $uids = Database::prepare( self::$SQL_UIDS );

            // Execute the query
            $uids->execute( array( $time ) );

            // Return all the rows according to the symantics
            // of the database
            $fetch_type = PDO::FETCH_COLUMN;
            if( $clicks ) $fetch_type |= PDO::FETCH_GROUP;
            return $uids->fetchAll( $fetch_type );
        }
        catch( PDOException $e )
        {
            echo "Error: " . $e;
            return false;
        }
    }

    private $data;
    private $m_cluster;
    private $m_parent;

    // User constructor
    public function __construct( $uid, $clicks = null, $pid = null )
    {
        $this->data['uid'] = $uid;
        $this->data['clicks'] = $clicks;
        $this->data['pid'] = $pid;
    }

    // Special override function which allows
    // for the quick definition of all the getters
    // for the private data variable
    public function __get( $name )
    {
        if( array_key_exists( $name, $this->data ) )
            return $this->data[$name];

        $function = "get_" . $name;
        if( method_exists( $this, $function ) )
            return call_user_func( array( $this, $function ) );

        return null;
    }

    // Special override function for setting
    // properties of this class
    public function __set( $name, $value )
    {
        $function = "set_" . $name;
        if( method_exists( $this, $function ) )
            return call_user_func( array( $this, $function ), $value );

        return null;
    }

    // This records a click for this user
    public function add_click( $url, $time = null )
    {
        try
        {
            // Prepare the query and execute, throwing errors if necessary
            $query = Database::prepare( self::$SQL_ADD_CLICK );
            
            if( $time == null ) $time = date( "Y-m-d H:i:s" );
            $query->execute( array( $this->data['uid'], $url, $time ) );

            // Add a click to this user object if the query executed
            $this->data['clicks'][] = $url;
            return true;
        }
        catch( PDOException $e )
        {
            echo "Error: " . $e;
            return false;
        }
    }

    // Boolean function which returns true
    // if this user is a center of a cluster
    public function is_center()
    {
        return $this->data['uid'] == $this->data['pid'];
    }

    // Return recommendations for this user
    public function recommendations( $num = 10 )
    {
        return $this->get_cluster()->recommendations( $num );
    }

    /******************************************************************
     * PRIVATE METHODS
     *****************************************************************/

    // Return the cluster that this user belongs to
    private function get_cluster()
    {
        // A little caching
        if( isset( $m_cluster ) )
            return $m_cluster;
        else
            return $m_cluster = Cluster::find_by_user( $this );
    }

    // This returns a user class which is the
    // parent of this user. This is different
    // than user->parent which returns just
    // the parent's id
    private function get_parent()
    {
        if( $this->is_center() ) return $this;

        // A little caching
        if( isset( $m_parent ) )
            return $m_parent;
        else
            return $m_parent = User::find( $this->data['pid'] );
    }

    // This gives the current user a new parent
    private function set_parent( $pid )
    {
        unset( $m_parent );
        if( is_object( $pid ) ) 
        {
            $m_parent = $pid;
            $pid = $pid->uid;
        }
        try
        {
            // Prepare the query and execute, throwing errors if necessary
            if( $this->data['parent'] == null )
                $query = Database::prepare( self::$SQL_ADD_PARENT );
            else
                $query = Database::prepare( self::$SQL_SET_PARENT );

            $query->execute( array( $pid, $this->data['uid'] ) );

            // Set the data of this object if the query executed
            $this->data['pid'] = $pid;
            return true;
        }
        catch( PDOException $e )
        {
            echo "Error: " . $e;
            return false;
        }
    }
}
?>
