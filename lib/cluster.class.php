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

    // Find cluster by its center id
    public static function find( $cid )
    {
        try
        {
            // Prepare the necessary queries
            $cluster = Database::prepare( self::$SQL_FIND );

            // Execute the query and throw an exception if it fails
            if( !$cluster->execute( array( $cid ) ) )
                throw new PDOException( "Could not execute find query" );

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
    public function Cluster( $cid, $users )
    {
        $this->data['center'] = $cid;
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

    // Returns num recommendations for this cluster
    public function recommendations( $num = 10 )
    {
        // TODO: Implement this...
    }
}
?>
