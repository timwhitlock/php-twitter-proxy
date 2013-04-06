<?php
/**
 * users/lookup
 * https://dev.twitter.com/docs/api/1.1/get/users/lookup
 */
 
require '../../twitter-proxy.php';

// @todo prevent exposing of protected user accounts
// Proxy::strip_private();

Proxy::relay( 'users/lookup' );
