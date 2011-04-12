<?php
/**
 * Recommender Service for Morning Mail
 *
 * FILE: qt_cluster.func.php
 * COURSE: Web Systems Development
 * AUTHOR(s): Matthew Perry
 * DESCRIPTION:
 *  The meat of the assignment; A class
 *  which clusters user clicks based on similarity
 */
require_once( "api.php" );

class QualityThreshold
{
    private static $users;
    private static $date;
    private static $threshold;
    private static $clusters;

    // Perform the Quality Threshold clustering
    public static function cluster( $days, $threshold = 0.5 )
    {
        // Set the threshold
        self::$threshold = $threshold;
        // Get all user ids that clicked in the past $time
        self::$date = date( "Y-m-d H:i:s", time() - ( $days * 24 * 60 * 60 ) );
        self::$users = User::find_all( array( 'time' => self::$date, 'clicks' => true ) );

        // Calculate the set of clusters
        return self::$clusters = self::recurse( array_keys( self::$users ) );
    }

    // Return the clusters
    public static function clusters()
    {
        return self::$clusters;
    }

    // Save the clusters to the database
    public static function save()
    {
        // Clear out current clusters, TRUNCATE is implicitly
        // commited so it ruins the transaction, thats why its
        // out here
        Database::query( "TRUNCATE centers;" );
        // Initiate a large transaction to the DB
        Database::beginTransaction();
        // Save every cluster to the DB
        foreach( self::$clusters as $cluster )
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
    private static function recurse( $uids )
    {
        // Base Case
        if( empty( $uids ) ) return $uids;

        // Loop over each user id
        foreach( $uids as $uid )
        {
            // Seed this cluster
            $cluster = new Cluster( $uid );
            // Loop over each user id again
            foreach( $uids as $uid2 )
            {
                // Add the user id if it is within the threshold
                if( $uid == $uid2 ) continue;
                $dist = self::distance( $uid, $uid2 );
                if( $dist <= self::$threshold )
                    $cluster->addUser( $uid2 );
            }

            // Only save this cluster if it is the largest one
            if( !isset( $max ) || $cluster->size() > $max->size() )
                $max = $cluster;
            unset( $cluster );
        }

        // Remove cluster from uids
        $uids = array_diff( $uids, $max->users );
        // Return all of the clusters
        return array_merge( array( $max ), self::recurse( $uids ) );
    }

    // Given two user ids, identify the distance between them
    // using the QT calculation and Jaccard distance
    private static function distance( $uid1, $uid2 )
    {
        // Max and min function identify the smaller of the sets
        $set1 = min( self::$users[$uid1], self::$users[$uid2] );
        $set2 = max( self::$users[$uid1], self::$users[$uid2] );
        // Number of clicks in common
        $common = count( array_intersect( $set1, $set2 ) );
        // Total number of clicks (min)
        $total = count( $set1 );
        // Return Jaccard Distance
        return 1 - ( $common / $total ); 
    }
}
?>
