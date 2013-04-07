<?php
/**
 * users/show
 * https://dev.twitter.com/docs/api/1.1/get/users/show
 */
 
require '../../twitter-proxy.php';

if( empty($_GET['skip_status']) ){
    Proxy::protected_user_post_check();
}

Proxy::share_cache();

Proxy::relay( 'users/show' );
