<?php
/**
 * Recommender Service for Morning Mail
 *
 * FILE: index.php
 * COURSE: Web Systems Development
 * AUTHOR(s): Matthew Perry
 * DESCRIPTION:
 *  Main page, shows relevant links based on QT
 *  clustering algorithm
 */
require_once( "lib/api.php" );
session_start();
?>

<html>
    <head>
        <title>MorningMail Recommendations</title>
        <link rel="stylesheet" href="static/application.css" type="text/css" />
        <script src="static/jquery.min.js" type="text/javascript"></script>
        <script src="static/application.js" type="text/javascript"></script>
    </head>
    <body>
        <div id="container">
            <header>
                <h1>MorningMail: Articles You May Like</h1>
                <div id="info_box">
<?php
if( isset( $_SESSION["cas"] ) && $_SESSION["cas"] )
{
    echo "<p>Welcome, " . $_SESSION["uid"] . "</p>";
    echo "<a href='login.php?logout=true'>Log Out</a>";
}
else 
{
    echo "<a href='login.php'>Log In</a>";
}
?>
                </div>
            </header>
<?php
if( isset( $_SESSION['uid'] ) )
    $u = User::find( $_SESSION['uid'] );
else
{
    if( !isset( $_COOKIE['uid'] ) )
        setcookie( "uid", md5( session_id() . time() . "salt" ), strtotime( "+20 years" ) );
    $u = User::find( $_COOKIE['uid'] );
    $_SESSION['uid'] = $_COOKIE['uid'];
    $_SESSION['cas'] = false;
}
$recent = $u->recent();
if( count( $recent ) != 0 )
{
    echo "<div id='recent' >";
    echo "<h1>Recently Clicked:</h1>";
    echo "<ul>";
    foreach( $recent() as $info )
    {
        $article = Article::find( $info["url"] );
        echo "<li>";
        printf('<a href="%1$s" name="%1$s">%2$s</a>', $article->url, $article->headline);
        echo "</li>";
        echo "<br />";
    }
    echo "</ul>";
    echo "</div>";
}
echo "<div id='content'>";
echo "<ul>";
foreach( $u->recommendations() as $article )
{
    echo "<li>";
    printf('<a href="%1$s" name="%1$s">%2$s</a>', $article->url, $article->headline);
    printf("<p>%s</p>", $article->summary);
    echo "</li>";
    echo "<br /><hr /><br />";
}
echo "</ul>";
echo "</div>";
?>
            <footer>
                <p>Developed as a final project for <a href="http://rpi.edu/~gillw3/websys/">Web Systems Development</a> Spring 2011.</p>
                <p>&copy; Kenley Cheung, Matt Perry, and Sean Lyons.</p>
            </footer>
        </div>
    </body>
</html>
