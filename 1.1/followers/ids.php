<?php
/**
 * followers/ids
 * https://dev.twitter.com/docs/api/1.1/get/followers/ids
 */
 
require '../../twitter-proxy.php';

Proxy::share_cache();

Proxy::relay( 'followers/ids' );
