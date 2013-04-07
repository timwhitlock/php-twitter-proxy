<?php
/**
 * statuses/show
 * https://dev.twitter.com/docs/api/1.1/get/statuses/show/%3Aid
 */
 
require '../../twitter-proxy.php';

// @todo deny trim_user so we know if user is protected

Proxy::share_cache();

Proxy::relay( 'statuses/show' );
