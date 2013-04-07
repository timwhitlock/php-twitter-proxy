<?php
/**
 * friends/ids
 * https://dev.twitter.com/docs/api/1.1/get/friends/ids
 */
 
require '../../twitter-proxy.php';

Proxy::share_cache();

Proxy::relay( 'friends/ids' );
