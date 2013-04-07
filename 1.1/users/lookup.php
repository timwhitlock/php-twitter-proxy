<?php
/**
 * users/lookup
 * https://dev.twitter.com/docs/api/1.1/get/users/lookup
 */
 
require '../../twitter-proxy.php';

// @todo implement protected user post-check

Proxy::share_cache();

Proxy::relay( 'users/lookup' );
