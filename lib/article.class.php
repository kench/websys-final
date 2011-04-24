<?php
/**
 * Recommender Service for Morning Mail
 *
 * FILE: article.class.php
 * COURSE: Web Systems Development
 * AUTHOR(s): Matthew Perry, Kenley Cheung
 * DESCRIPTION:
 *  Class abstraction for manipulating
 *  cached RSS feed articles
 */
require_once( "api.php" );

class Article
{
    // Static strings which represent the queries that this class performs
    // on a regular basis.
    //
    // This kind of definition allows for easier editing if a db scheme change
    // occurs.
    //
    // NOTE: In this context the URL field MUST be unique because the user table
    // is using it as a foreign key to the articles.
    private static $SQL_FIND = "SELECT * FROM articles WHERE url = ?;";
    private static $SQL_FIND_ALL = "SELECT * FROM articles ORDER BY date DESC LIMIT :limit;";
    private static $SQL_FIND_IN = "SELECT * FROM articles WHERE url IN ( %list ) ORDER BY date DESC;";

    private static $SQL_SAVE = "INSERT INTO articles VALUES( :h, :s, :d, :u );";
    private static $SQL_CLEAN = "DELETE FROM articles WHERE date < ?;";

    // Special function to update the db from RSS feed
    public static function mm_update()
    {
        // Get the RSS feed from Morning Mail
        $xml = simplexml_load_file( 'http://morningmail.rpi.edu/rss' );
        
        // Begin the transaction
        Database::beginTransaction();

        $count = 0;
        foreach( $xml->channel->item as $item )
        {
            // Check for duplicates (no DB-agnostic way
            // to ignore duplicate errors)
            if( self::find( $item->link ) ) continue;

            // Parse data and construct Article objects,
            // save them to the DB
            $date = date_create( $item->pubDate );
            $a = new Article( $item->title, strip_tags( $item->description ), 
                              $date->format( 'Y-m-d H:i:s' ), $item->link );
            // Increment row count
            $count++;
            if( !$a->save() )
            {
                Database::rollBack();
                return false;
            }
        }

        // Commit transaction
        Database::commit();
        return $count;
    }

    // Special function to cleanup the RSS feed data
    public static function cleanup( $date = null )
    {
        // Set default time to a month ago
        if( $date == null ) $date = date( "Y-m-d H:i:s", strtotime( "-1 month" ) ); 

        try
        {
            // Prepare the necessary query
            $delete = Database::prepare( self::$SQL_CLEAN );

            // Execute the query
            $delete->execute( array( $date ) );

            // Return rows deleted
            return $delete->rowCount();
        }
        catch( PDOException $e )
        {
            echo "Error: " . $e;
            return false;
        }
    }

    // Find article by its url
    public static function find( $url )
    {
        try
        {
            // Prepare the necessary queries
            $article = Database::prepare( self::$SQL_FIND );

            // Execute the query
            $article->execute( array( $url ) );

            // Fetch according to the symantics of the database
            // and return a new article with this information
            if( $article->rowCount() == 0 ) return false;
            return new Article( $article->fetchAll( PDO::FETCH_ASSOC ) );
        }
        catch( PDOException $e )
        {
            echo "Error: " . $e;
            return false;
        }
    }

    // Return all articles that have a url in the array
    public static function find_by_array( $urls )
    {
        if( empty( $urls ) ) return array();

        // Append special chars to query string for variable
        // length inputs
        $var_list = "?";
        for( $i = 1; $i < count( $urls ); $i++ )
            $var_list .= ", ?";
        try
        {
            // Prepare the necessary queries
            $articles = Database::prepare( str_replace( "%list", $var_list, self::$SQL_FIND_IN ) );

            // Execute the query
            $articles->execute( $urls );

            // Fetch according to the symantics of the database
            // and return a new array of articles
            if( $articles->rowCount() == 0 ) return false;
            foreach( $articles->fetchAll( PDO::FETCH_ASSOC ) as $article )
                $all[] = new Article( $article );
            return $all;
        }
        catch( PDOException $e )
        {
            echo "Error: " . $e;
            return false;
        }
    }

	// Return num articles
	public static function find_all( $num = 10 )
	{
		try
        {
            // Prepare the necessary queries
            $articles = Database::prepare( self::$SQL_FIND_ALL );

            // Execute query
            $articles->bindValue( ":limit", $num, PDO::PARAM_INT );
			$articles->execute();

			// Return all the rows according to the symantics
            // of the database
            if( $articles->rowCount() == 0 ) return false;
            foreach( $articles->fetchAll( PDO::FETCH_ASSOC ) as $article )
                $all[] = new Article( $article );
            return $all;
		}
		catch( PDOException $e )
		{
			echo "Error: " . $e;
			return false;
		}
	}

    private $data;

    // Article constructor
    public function __construct()
    {
        // Use PHP special functions to perform
        // a psuedo overloading of this constructor
        $num_args = func_num_args();
        if( $num_args == 1 )
        {
            $this->data = func_get_arg( 0 );
        }
        elseif( $num_args == 4 )
        {
            $this->data['headline'] = func_get_arg( 0 );
            $this->data['summary'] = func_get_arg( 1 );
            $this->data['date'] = func_get_arg( 2 );
            $this->data['url'] = func_get_arg( 3 );
        }
    }

    // PHP special override function that gives
    // access to the private $data
    public function __get( $name )
    {
        if( array_key_exists( $name, $this->data ) )
            return $this->data[$name];

        return null;
    }

    private function save()
    {
        try
        {
            // Prepare save query
            $save = Database::prepare( self::$SQL_SAVE );

            // Set the parameters of the query
            foreach( $this->data as $k => $v )
                $save->bindValue( ":" . $k[0], $v, PDO::PARAM_STR );

            // Execute query
            $save->execute();

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
