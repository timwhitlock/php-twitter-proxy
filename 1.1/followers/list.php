<?php
/**
 * followers/list
 * https://dev.twitter.com/docs/api/1.1/get/followers/list
 */
 
require '../../lib/twitter-proxy.php';
require '../../lib/twitter-client.php';
require '../../config.php';
 
proxy_user_request( 'followers/list' );
