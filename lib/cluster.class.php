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
    public static $SQL_REC = <<<EOD
SELECT url, COUNT( url ) AS num_clicks
FROM clicks 
WHERE uid IN ( %list ) AND clicked_at >= :today
GROUP BY url
ORDER BY num_clicks DESC;
EOD;

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
            return self::find( $user->pid );
        else
            return self::find( User::find( $user )->pid );
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
            $this->data['users'][] = $user->uid;
        else
            $this->data['users'][] = $user;
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
        if( $num == 0 ) return array();

        // Append special chars to query string for variable
        // length inputs=
        $var_list = ":0";
        for( $i = 1; $i < $this->size(); $i++ )
            $var_list .= ", :" . $i;
        try
        {
            // Prepare the necessary queries
            $clicks = Database::prepare( str_replace( "%list", $var_list, self::$SQL_REC ) );

            // Execute the query
            for( $i = 0; $i < $this->size(); $i++ )
                $clicks->bindValue( ":" . $i, $this->data["users"][$i], PDO::PARAM_STR );
            $today = date( "Y-m-d 00:00:00" );
            $clicks->bindValue( ":today", $today, PDO::PARAM_STR );
            $clicks->execute();

            // Find all the articles associated with the recommended
            // urls
            $clicks = $clicks->fetchAll( PDO::FETCH_ASSOC );
            foreach( $clicks as $url => $count )
                $recommendations[] = Article::find( $url );
            if( empty( $clicks ) )
                $recommendations = array();

            // If there are less than $num recommended urls, fill the
            // remainder of the request with the newest articles
            $num_normal = $num - count( $clicks );
            return array_merge( $recommendations, Article::find_all( $num_normal ) );
        }
        catch( PDOException $e )
        {
            echo "Error: " . $e;
            return false;
        }
    }
}
?>
