<?php
/**
 * statuses/user_timeline
 * https://dev.twitter.com/docs/api/1.1/get/statuses/user_timeline
 */
 
require '../../twitter-proxy.php';

// prevent others using your proxy to pull their own tweets
$has_id = isset($_GET['user_id']) and Proxy::match_user_id( $_GET['user_id'] );
$has_name = isset($_GET['screen_name']) and Proxy::match_screen_name( $_GET['screen_name'] );

// note that there are no protected account checks due to above security feature.

// only share cache if target user is specified
if( $has_id || $has_name ){
    Proxy::share_cache();
}

Proxy::relay( 'statuses/user_timeline' );
