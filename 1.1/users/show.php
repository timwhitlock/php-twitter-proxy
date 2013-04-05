<?php
/**
 * users/show
 * https://dev.twitter.com/docs/api/1.1/get/users/show
 */
 
require '../../twitter-proxy.php';
require '../../config.php';

// prevent exposing of protected user accounts
Proxy::strip_private();

Proxy::relay( 'statuses/user_timeline' );
