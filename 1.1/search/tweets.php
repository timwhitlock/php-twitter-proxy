<?php
/**
 * search/tweets
 * https://dev.twitter.com/docs/api/1.1/get/search/tweets
 */
 
require '../../twitter-proxy.php';

Proxy::share_cache();

Proxy::relay( 'search/tweets' );
