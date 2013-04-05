<?php
/**
 * followers/list
 * https://dev.twitter.com/docs/api/1.1/get/followers/list
 */
 
require '../../twitter-proxy.php';
require '../../config.php';
 
Proxy::relay( 'followers/list' );
