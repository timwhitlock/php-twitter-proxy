<?php
/**
 * users/show
 * https://dev.twitter.com/docs/api/1.1/get/users/show
 */
 
require '../../twitter-proxy.php';

if( empty($_GET['skip_status']) ){
    // @todo swap for a post check and handle trim_user
    Proxy::protected_user_pre_check();
}

Proxy::share_cache();

Proxy::relay( 'users/show' );
