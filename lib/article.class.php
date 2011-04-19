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
    private static $SQL_FIND_ALL = "SELECT * FROM articles;";
    private static $SQL_SAVE = "INSERT INTO articles VALUES( ?, ?, ?, ? );";

    // Find article by its url
    public static function find( $url )
    {
        try
        {
            // Prepare the necessary queries
            $article = Database::prepare( self::$SQL_FIND );

            // Execute the query and throw an exception if it fails
            if( !$article->execute( array( $url ) ) )
                throw new PDOException( "Could not execute find query" );

            // Fetch according to the symantics of the database
            // and return a new article with this information
            if( $article->rowCount() == 0 ) return false;
            return new Article( $cluster->fetchAll() );
        }
        catch( PDOException $e )
        {
            echo "Error: " . $e;
            return false;
        }
    }

	// Return all articles
	public static function find_all()
	{
		try
        {
            // Prepare the necessary queries
            $articles = Database::prepare( self::$SQL_FIND );

			if( !$article->execute() )
                throw new PDOException( "Could not execute find query" );

			// Return all the rows according to the symantics
            // of the database
            $all = array();
            foreach( $articles->fetchAll() as $article )
                array_push( $all, $article );                
            return $all;
		}
		catch( PDOException $e )
		{
			echo "Error: " . $e;
			return false;
		}
	}

    private $data;

    // Article constructor 1
    public function Article( $data )
    {
        $this->data = $data;
    }

    // Article constructor 2
    public function Article( $headline, $summary, $date, $url )
    {
        $this->data['headline'] = $headline;
        $this->data['summary'] = $summary;
        $this->data['date'] = $date;
        $this->data['url'] = $url;
    }

    // PHP special override function that gives
    // access to the private $data
    public function __get( $name )
    {
        if( array_key_exists( $name, $this->data ) )
            return $this->data[$name];

        return null;
    }

    public function save()
    {
        try
        {
            // Prepare save query
            $save = Database::prepare( self::$SQL_SAVE );
            // This assumes that the $data variable has its
            // keys in the correct order (we may need named parameters
            // in the query here)
            if( !$save->execute( array_values( $data ) ); 
        }
        catch( PDOException $e )
        {
            echo "Error: " . $e;
            return false;
        }
    }
}
?>
