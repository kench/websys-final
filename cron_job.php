<?php
/**
 * Recommender Service for Morning Mail
 *
 * FILE: cron_job.php
 * COURSE: Web Systems Development
 * AUTHOR(s): Matt Perry
 * DESCRIPTION:
 *  This script is meant to be run from a cron
 *  job every 24 hours
 */
require_once( "lib/api.php" );

$qt_days = 30;
$qt_threshold = 0.5;

$cleanup_count = Article::cleanup();
$new_article_count = Article::mm_update();
$cluster_start = time();
$clusters_made = count( QualityThreshold::cluster( $qt_days, $qt_threshold ) );
$cluster_runtime = time() - $cluster_start;
$cluster_write = QualityThreshold::save() ? 'true' : 'false';

$log_file = "log/cron_job.log";
$fh = fopen( $log_file, "a" ) or die( "Cannot open log file" );

$today = date( "Y-m-d H:i:s" );
fwrite( $fh, "cron_job.php: $today\n" );
fwrite( $fh, "Articles removed: $cleanup_count\n" );
fwrite( $fh, "Articles added: $new_article_count\n" );
fwrite( $fh, "Cluster runtime: $cluster_runtime seconds\n" );
fwrite( $fh, "Clusters made: $clusters_made\n" );
fwrite( $fh, "Cluster write successful: $cluster_write\n\n" );
?>
