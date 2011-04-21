# ( Yet Another ) Morning Mail Recommender Service

This is the final project for the course "Web Systems Development"

## API-Documentation

### Usage

To use this API, simply include the following at the top of your source files:

    require_once( "lib/api.php" );

### Article Class

The Article class provides an interface to interact with cached rss articles from Morning Mail.

#### Static Functions

 * `Article::mm_update( void )`
   * **Description**:
      This function will simply pull all of the RSS feed items from "http://morningmail.rpi.edu/rss" and save them to the database.
   * **Parameters**:
   * **Return**:
 * `Article::cleanup( [date $before] )`
   * **Description**:
   * **Parameters**:
   * **Return**:
 * `Article::find( string $url )`
   * **Description**:
   * **Parameters**:
   * **Return**:
 * `Article::find_by_array( array $urls )` 
   * **Description**:
   * **Parameters**:
   * **Return**:
 * `Article::find_all( void )`
   * **Description**:
   * **Parameters**:
   * **Return**:

#### Member Functions

 * `Article->__construct()`
   * **Description**:
   * **Parameters**:
   * **Return**:
 * `Article->__get( string $name )`
   * **Description**:
   * **Parameters**:
   * **Return**:
 * `Article->save( void )`
   * **Description**:
   * **Parameters**:
   * **Return**:

### User Class

The User class provides an interface to interact with the users of our service.

#### Static Functions

#### Member Functions

### Cluster Class

The Cluster class provides an interface to interact with the clusters used by our recommendation system.

#### Static Functions

#### Member Functions

### Quality Threshold Clustering

The Quality Threshold class provides an interface to perform fast quality threshold clustering.

#### Static Functions

#### Member Functions
