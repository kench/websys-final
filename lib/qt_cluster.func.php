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
ini_set( "memory_limit", "1G" );

// Perform the Quality Threshold clustering
function qt_cluster()
{
    // Initiate a large transaction to the DB
    Database::beginTransaction();

    // Clear out current clusters
    Database::query( "TRUNCATE centers;" );

    // Get all user ids that clicked in the past $time
    $time = time() - ( 14 * 24 * 60 * 60 ); // 1 weeks ago
    $date = date( "Y-m-d H:i:s", $time );
    $uids = User::getUIDs( $date );

    // Calculate distances for each pair
    for( $i = 0; $i < count( $uids ); ++$i )
    {
        for( $j = 0; $j <= $i; ++$j )
        {
            // Lazily evaluate each user ID to save some semblance
            // of running time
            if( !is_object( $uids[$i] ) )
                $uids[$i] = User::find( $uids[$i] );
            if( !is_object( $uids[$j] ) ) 
                $uids[$j] = User::find( $uids[$j] );

            // Calculate the similarity index
            if( count( $uids[$i]->clicks ) < count( $uids[$j]->clicks ) )
                $sim_ndx[$i][$j] = qt_distance( $uids[$i]->clicks, $uids[$j]->clicks );
            else
                $sim_ndx[$i][$j] = qt_distance( $uids[$j]->clicks, $uids[$i]->clicks );
        }
    }

    // Continue until every cell is 0
    while( !zeros( $sim_ndx ) )
    {
        //TODO: Implement the remainder of this...
        break;
    }

    // Commit the entire transaction
    Database::commit();
}

// Given two sets, identify the distance between them
// using the QT calculation and Jaccard distance
//
// count( $set1 ) <= count( $set2 ) NECESSARY
function qt_distance( $set1, $set2 )
{
    // Number of clicks in common
    $common = count( array_intersect( $set1, $set2 ) );
    // Total number of clicks (min)
    $total = count( $set1 );
    // Return Jaccard Distance
    return 1 - ( $common / $total ); 
}

// Return true if the entire matrix contains only 0s
function zeros( $mat )
{
    foreach( $mat as $k => $v )
        foreach( $v as $p => $q )
            if( $q != 0 ) return false;
    return true;
}
?>
