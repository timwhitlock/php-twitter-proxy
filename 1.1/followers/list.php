<?php
/**
 * followers/list
 * https://dev.twitter.com/docs/api/1.1/get/followers/list
 */
 
require '../../config.php';
require '../../lib/twitter-client.php';
require '../../lib/twitter-proxy.php';

proxy_user_request( 'followers/list' );
