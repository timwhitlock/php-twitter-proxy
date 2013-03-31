<?php
/**
 * statuses/user_timeline
 * https://dev.twitter.com/docs/api/1.1/get/statuses/user_timeline
 */
 
require '../../config.php';
require '../../lib/twitter-client.php';
require '../../lib/twitter-proxy.php';

// prevent others using your proxy to pull their own tweets
proxy_user_restrict( $_GET );

proxy_user_request( 'statuses/user_timeline' );
