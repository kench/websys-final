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
    // Get all user ids that clicked in the past $time
    $date = date( "Y-m-d H:i:s", time() - ( 21 * 24 * 60 * 60 ) );
    $uids = User::getUIDs( $date );

    // Temporary hard-coded threshold
    $threshold = 0.5;

    // Calculate the set of clusters
    $clusters = qt_recurse( $uids, $threshold );

    // Clear out current clusters, TRUNCATE is implicitly
    // commited so it ruins the transaction, thats why its
    // out here
    Database::query( "TRUNCATE centers;" );
    // Initiate a large transaction to the DB
    Database::beginTransaction();
    // Save every cluster to the DB
    foreach( $clusters as $cluster )
    {
        if( !$cluster->save() )
        {
            // Undo all changes to the DB
            Database::rollBack();
            return false;
        }
    }
    // Commit the entire transaction
    Database::commit();

    return true;
}

// The meat of the clustering, this recurses over 
// points finding the largest clusters
function qt_recurse( $uids, $threshold )
{
    // Base Case
    if( count( $uids ) == 0 ) return $uids;

    foreach( $uids as $uid )
    {
        $flag = true;
        $clusters[$uid] = new Cluster( $uid );
        if( count( $uids ) == 1 ) break;

        foreach( $uids as $uid2 )
        {
            if( $uid == $uid2 ) continue;
            $distances[$uid2] = qt_distance( $uid, $uid2 );
        }
        asort( $distances );
        reset( $distances );

        while( $flag && $clusters[$uid]->size() != count( $uids ) )
        {
            $element = each( $distances );
            if( $element['value'] > $threshold )
                $flag = false;
            else
                $clusters[$uid]->addUser( $element['key'] );
        }
        unset( $distances );
    }

    // Pick largest cluster
    $max_size = 0;
    foreach( $clusters as $cluster )
    {
        if( $cluster->size() > $max_size )
        {
            $max_size = $cluster->size();
            $max = $cluster;
        }
    }
    // Remove cluster from uids
    $uids = array_diff( $uids, $max->users );
    // Return all of the clusters
    return array_merge( array( $max ), qt_recurse( $uids, $threshold ) );
}

// Given two user ids, identify the distance between them
// using the QT calculation and Jaccard distance
function qt_distance( $uid1, $uid2 )
{
    // Memoize the users to minimize DB interaction
    static $users = array(); 
    if( !array_key_exists( $uid1, $users ) )
        $users[$uid1] = User::find( $uid1 );
    if( !array_key_exists( $uid2, $users ) )
        $users[$uid2] = User::find( $uid2 );

    // Max and min function identify the smaller of the sets
    $set1 = min( $users[$uid1]->clicks, $users[$uid2]->clicks );
    $set2 = max( $users[$uid1]->clicks, $users[$uid2]->clicks );
    // Number of clicks in common
    $common = count( array_intersect( $set1, $set2 ) );
    // Total number of clicks (min)
    $total = count( $set1 );
    // Return Jaccard Distance
    return 1 - ( $common / $total ); 
}
?>
