<?php
/**
 * statuses/user_timeline
 * https://dev.twitter.com/docs/api/1.1/get/statuses/user_timeline
 */
 
require '../../twitter-proxy.php';
require '../../config.php';

// prevent others using your proxy to pull their own tweets
Proxy::check_foreign_user( $_GET );

Proxy::relay( 'statuses/user_timeline' );
