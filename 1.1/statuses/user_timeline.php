<?php
/**
 * statuses/user_timeline
 * https://dev.twitter.com/docs/api/1.1/get/statuses/user_timeline
 */
 
require '../../twitter-proxy.php';
require '../../config.php';

// prevent others using your proxy to pull their own tweets
isset($_GET['user_id']) and Proxy::match_user_id( $_GET['user_id'] );
isset($_GET['screen_name']) and Proxy::match_screen_name( $_GET['screen_name'] );

Proxy::relay( 'statuses/user_timeline' );
