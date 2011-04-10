<?php
/**
 * Recommender Service for Morning Mail
 *
 * FILE: qt_cluster.func.php
 * COURSE: Web Systems Development
 * AUTHOR(s): Matthew Perry
 * DESCRIPTION:
 *  The meat of the assignment; A function
 *  which clusters user clicks based on similarity
 */
require_once( "api.php" );

// Perform the Quality Threshold clustering
function qt_cluster()
{
    // Super hackish memory expansion, reconsider PHP
    ini_set( "memory_limit", "192M" );

    // Get all user ids that clicked in the past $time
    $date = date( "Y-m-d H:i:s", time() - ( 6 * 24 * 60 * 60 ) );
    $uids = User::getUIDs( $date );

    // Temporary hard-coded threshold
    $threshold = 0.5;

    // Initiate a large transaction to the DB
    Database::beginTransaction();

    // Clear out current clusters
    Database::query( "TRUNCATE centers;" );

    // Calculate the set of clusters
    $clusters = qt_recurse( $uids, $threshold );

    // Save every cluster to the DB, this takes forever
    foreach( $clusters as $cluster )
        $cluster->save();

    // Commit the entire transaction
    Database::commit();
}

// The meat of the clustering, this recurses over 
// points finding the largest clusters
function qt_recurse( $uids, $threshold )
{
    // Base Case
    if( count( $uids ) == 0 ) return null;

    foreach( $uids as $uid )
    {
        $flag = true;
        $clusters[$uid] = new Cluster( $uid );

        while( $flag && $clusters[$uid].size() != count( $uids ) )
        {
            // TODO: Find closest uid
            if( $dist > $threshold )
                $flag = false;
            else
                $clusters[$uid].addUser( $ );
        }
    }

    // Pick largest cluster
    $max = max( $clusters );
    $output = array( $max );
    // Remove cluster from uids
    qt_remove( $uids, $max );
    array_push( $output, qt_recurse( $uids, $threshold ) );
    // Return all of the clusters
    return $output;
}

// Given two sets, identify the distance between them
// using the QT calculation and Jaccard distance
//
// count( $set1 ) <= count( $set2 ) NECESSARY
function qt_distance( $set1, $set2 )
{
    // Memoize the users to minimize DB interaction
    static $users = array(); 

    // Number of clicks in common
    $common = count( array_intersect( $set1, $set2 ) );
    // Total number of clicks (min)
    $total = count( $set1 );
    // Return Jaccard Distance
    return 1 - ( $common / $total ); 
}

// Remove the given cluster from the set of uids
function qt_remove( $uids, $cluster )
{
    // TODO: Implement this...
}
?>
