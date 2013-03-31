<?php
/**
 * statuses/mentions_timeline
 * https://dev.twitter.com/docs/api/1.1/get/statuses/mentions_timeline
 */
 
require '../../lib/twitter-proxy.php';
require '../../lib/twitter-client.php';
require '../../config.php';

proxy_user_request( 'statuses/mentions_timeline' );
