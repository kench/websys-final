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
/*
echo "<h1>WORK IN PROGRESS</h1>";
echo "<h2>All User IDs:</h2>";
echo "<ul>";
foreach( User::find_all() as $uid )
    echo "<li>".$uid."</li>";
echo "</ul>";
*/
?>

<html>
<head>
<title>MorningMail Recommendations</title>
<link rel="stylesheet" href="static/application.css" type="text/css" />
<script></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.5.2/jquery.min.js" type="text/javascript"></script>
<script src="application.js" type="text/javascript"></script>
</head>
<body>
<div id="container">
<header>
	<h1>MorningMail: Articles You May Like</h1>
	<div id="info_box">
		<?php
		if ($_SESSION["uid"])
		{
			echo "<p>" . $_SESSION["uid"] . "</p>";
			echo "<a href='login.php?logout=true'>Log Out</a>";
		}
		else 
		{
			echo "<a href='login.php'>Log In</a>";
		}
		?>
	</div>
</header>

<div id="content">
	<ul>
<?php
if($_SESSION['uid'])
    $u = User::find($_SESSION['uid']);
else
    $u = User::find(session_id());

foreach($u->recommendations() as $article)
{
echo "<li>";
printf("<a href='%s'>%s</a>", $article->url, $article->headline);
printf("<p>%s</p>", $article->summary);
echo "</li>";
}
?>

	</ul>
</div>

<footer>
	<p>Developed as a final project for <a href="http://rpi.edu/~gillw3/websys/">Web Systems Development</a> Spring 2011.</p>
	<p>&copy; Kenley Cheung, Matt Perry, and Sean Lyons.</p>
</footer>
</div>
</body>
</html>
