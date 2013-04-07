<?php
/**
 * friends/ids
 * https://dev.twitter.com/docs/api/1.1/get/friends/ids
 */
 
require '../../twitter-proxy.php';

Proxy::protected_user_pre_check();

Proxy::share_cache();

Proxy::relay( 'friends/ids' );
