<?php
/**
 * Recommender Service for Morning Mail
 *
 * FILE: cluster.class.php
 * COURSE: Web Systems Development
 * AUTHOR(s): Matthew Perry
 * DESCRIPTION:
 *  Class abstraction for manipulating
 *  clusters and extrapolating information
 *  about clusters from the DB
 */
require_once( "api.php" );

class Cluster
{
    // Static strings which represent the queries that this class performs
    // on a regular basis.
    //
    // This kind of definition allows for easier editing if a db scheme change
    // occurs.
    private static $SQL_FIND = "SELECT * FROM centers WHERE parent = ?;"; 
    private static $SQL_SAVE = "INSERT INTO centers VALUES( ?, ? );";

    // Find cluster by its center id
    public static function find( $cid )
    {
        try
        {
            // Prepare the necessary queries
            $cluster = Database::prepare( self::$SQL_FIND );

            // Execute the query
            $cluster->execute( array( $cid ) );

            // Fetch according to the symantics of the database
            // and return a new cluster with this information
            if( $cluster->rowCount() == 0 ) return false;
            $cluster = $cluster->fetchAll( PDO::FETCH_COLUMN | PDO::FETCH_GROUP );
            return new Cluster( $cid, $cluster[$cid] );
        }
        catch( PDOException $e )
        {
            echo "Error: " . $e;
            return false;
        }
    }

    // Find the cluster that this user belongs to
    public static function find_by_user( $user )
    {
        if( is_object( $user ) )
            return self::find( $user->parent );
        else
            return self::find( User::find( $user )->parent );
    }

    private $data;

    // Cluster constructor
    public function __construct( $cid, $users = null )
    {
        $this->data['center'] = $cid;
        if( $users == null )
            $this->data['users'] = array( $cid );
        else
            $this->data['users'] = $users;
    }

    // PHP special override function that gives
    // access to the private $data
    public function __get( $name )
    {
        if( array_key_exists( $name, $this->data ) )
            return $this->data[$name];

        return null;
    }

    // Get the size of this cluster, including the center
    public function size()
    {
        return count( $this->data['users'] );
    }

    // Add a user to this cluster
    public function add_user( $user )
    {
        if( is_object( $user ) )
            array_push( $this->data['users'], $user->uid );
        else
            array_push( $this->data['users'], $user );
    }

    // Write this cluster to the DB
    public function save()
    {
        try
        {
            // Save each user
            foreach( $this->data['users'] as $user )
            {
                $save = Database::prepare( self::$SQL_SAVE );
                $save->execute( array( $this->data['center'], $user ) );
            }
            return true;
        }
        catch( PDOException $e )
        {
            echo "Error: " . $e;
            return false;
        }

        return true;
    }

    // Returns num recommendations for this cluster
    public function recommendations( $num = 10 )
    {
        // TODO: Implement this...
    }
}
?>
