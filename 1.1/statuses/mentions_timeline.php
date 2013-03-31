<?php
/**
 * statuses/mentions_timeline
 * https://dev.twitter.com/docs/api/1.1/get/statuses/mentions_timeline
 */
 
require '../../config.php';
require '../../lib/twitter-client.php';
require '../../lib/twitter-proxy.php';

proxy_user_request( 'statuses/mentions_timeline' );
