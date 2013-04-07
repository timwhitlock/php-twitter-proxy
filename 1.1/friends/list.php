<?php
/**
 * friends/list
 * https://dev.twitter.com/docs/api/1.1/get/friends/list
 */
 
require '../../twitter-proxy.php';
 
Proxy::protected_user_pre_check();

Proxy::share_cache();

Proxy::relay( 'friends/list' );
