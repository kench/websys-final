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
foreach(Article::find_all() as $article_id)
{
echo "<li>";
printf("<a href='%s'>%s</a>", $article_url, $article_name);
printf("<p>%s</p>", $article_description);
echo "</li>";
}
?>
		<li>
			<a href="http://rpi.edu">Sample Article 1</a>
			<p>Description of news item goes here and everything.</p>
		</li>
	</ul>
</div>

<footer>
	<p>Developed as a final project for <a href="http://rpi.edu/~gillw3/websys/">Web Systems Development</a> Spring 2011.</p>
	<p>&copy; Kenley Cheung, Matthew Perry, and Sean Lyons.</p>
</footer>
</div>
</body>
</html>