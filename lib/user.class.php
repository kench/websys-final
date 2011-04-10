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
    private static $SQL_UIDS = "SELECT DISTINCT uid FROM clicks;";

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
            $parent = Database::prepare( self::$SQL_PARENT );

            // Execute the query and throw an exception if it fails
            if( !$clicks->execute( array( $uid ) ) )
                throw new PDOException( "Could not execute clicks query" );
            if( !$parent->execute( array( $uid ) ) )
                throw new PDOException( "Could not execute parent query" );

            // Fetch according to the symantics of the database
            // and return a new user with this information
            $clicks = $clicks->fetchAll( PDO::FETCH_COLUMN, 0 );
            $parent = $parent->fetch( PDO::FETCH_NUM );

            if( empty( $clicks ) ) $clicks = null;
            if( empty( $parent ) ) 
                $parent = null;
            else
                $parent = $parent[0];
            return new User( $uid, $clicks, $parent );
        } 
        catch( PDOException $e )
        {
            echo "Error: " . $e;
            return false;
        }
    }

    // Return all of the recorded UIDs of users
    public static function getUIDs()
    {
        try
        {
            // Prepare the necessary queries
            $uids = Database::prepare( self::$SQL_UIDS );

            // Execute the query and throw an exception if it fails
            if( !$uids->execute() )
                throw new PDOException( "Could not execute uids query" );

            // Return all the rows according to the symantics
            // of the database
            return $uids->fetchAll( PDO::FETCH_COLUMN, 0 );
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
    public function User( $uid, $clicks = null, $parent = null )
    {
        $this->data['uid'] = $uid;
        $this->data['clicks'] = $clicks;
        $this->data['parent'] = $parent;
    }

    // Special override function which allows
    // for the quick definition of all the getters
    // for the private data variable
    public function __get( $name )
    {
        if( array_key_exists( $name, $this->data ) )
            return $this->data[$name];

        return null;
    }

    // Return the cluster that this user belongs to
    public function getCluster()
    {
        // A little caching
        if( isset( $m_cluster ) )
            return $m_cluster;
        else
            return $m_cluster = Cluster::find_by_user( $this );
    }

    // Return recommendations for this user
    public function recommendations( $num = 10 )
    {
        return $this->getCluster()->recommendations( $num );
    }

    // This returns a user class which is the
    // parent of this user. This is different
    // than user->parent which returns just
    // the parent's id
    public function getParent()
    {
        if( $this->isCenter() ) return $this

        // A little caching
        if( isset( $m_parent ) )
            return $m_parent
        else
            return $m_parent = User::find( $this->data['parent'] );
    }

    // This gives the current user a new parent
    public function setParent( $pid )
    {
        try
        {
            // Prepare the query and execute, throwing errors if necessary
            if( $this->data['parent'] == null )
                $query = Database::prepare( self::$SQL_ADD_PARENT );
            else
                $query = Database::prepare( self::$SQL_SET_PARENT );

            if( !$query->execute( array( $pid, $this->data['uid'] ) ) )
                throw new PDOException( "Could not execute set parent query" );

            // Set the data of this object if the query executed
            $this->data['parent'] = $pid;
            return true;
        }
        catch( PDOException $e )
        {
            echo "Error: " . $e;
            return false;
        }
    }

    // This records a click for this user
    public function addClick( $url, $time = null )
    {
        try
        {
            // Prepare the query and execute, throwing errors if necessary
            $query = Database::prepare( self::$SQL_ADD_CLICK );
            
            if( $time == null ) $time = date( "Y-m-d H:i:s" );
            if( !$query->execute( array( $this->data['uid'], $url, $time ) ) )
                throw new PDOException( "Could not execute add click query" );

            // Add a click to this user object if the query executed
            array_push( $this->data['clicks'], $url );
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
    public function isCenter()
    {
        return $this->data['uid'] == $this->data['parent'];
    }
}
?>
