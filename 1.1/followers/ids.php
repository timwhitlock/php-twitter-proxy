<?php
/**
 * followers/ids
 * https://dev.twitter.com/docs/api/1.1/get/followers/ids
 */
 
require '../../twitter-proxy.php';

Proxy::protected_user_pre_check();

Proxy::share_cache();

Proxy::relay( 'followers/ids' );
