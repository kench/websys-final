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

echo "<h1>WORK IN PROGRESS</h1>";
echo "<h2>All User IDs:</h2>";
echo "<ul>";
foreach( User::getUIDs() as $uid )
    echo "<li>".$uid."</li>";
echo "</ul>";
?>
