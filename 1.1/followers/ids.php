<?php
/**
 * followers/ids
 * https://dev.twitter.com/docs/api/1.1/get/followers/ids
 */
 
require '../../lib/twitter-proxy.php';
require '../../lib/twitter-client.php';
require '../../config.php';

proxy_user_request( 'followers/ids' );
