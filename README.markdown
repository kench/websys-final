# ( Yet Another ) Morning Mail Recommender Service

This is the final project for the course "Web Systems Development"

## API-Documentation

### Usage

To use this API, simply include the following at the top of your source files:

    require_once( "lib/api.php" );

### Article Class

The Article class provides an interface to interact with cached rss articles from Morning Mail.

#### Static Functions

 * [Article::mm_update][mm_update]
 * [Article::cleanup][cleanup]
 * [Article::find][find]
 * [Article::find_by_array][find_by_array] 
 * [Article::find_all][find_all]

#### Member Functions

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
