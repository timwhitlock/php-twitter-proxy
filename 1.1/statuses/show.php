<?php
/**
 * statuses/show
 * https://dev.twitter.com/docs/api/1.1/get/statuses/show/%3Aid
 */
 
require '../../twitter-proxy.php';

Proxy::protected_user_post_check();

Proxy::share_cache();

Proxy::relay( 'statuses/show' );
