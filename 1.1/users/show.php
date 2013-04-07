<?php
/**
 * users/show
 * https://dev.twitter.com/docs/api/1.1/get/users/show
 */
 
require '../../twitter-proxy.php';

// @todo prevent exposing of protected user accounts
// Proxy::strip_private();

Proxy::share_cache();

Proxy::relay( 'users/show' );
