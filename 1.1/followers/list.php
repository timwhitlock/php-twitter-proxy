<?php
/**
 * followers/list
 * https://dev.twitter.com/docs/api/1.1/get/followers/list
 */
 
require '../../twitter-proxy.php';
 
Proxy::share_cache();

Proxy::relay( 'followers/list' );
