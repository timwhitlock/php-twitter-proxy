<?php
/**
 * statuses/retweets
 * https://dev.twitter.com/docs/api/1.1/get/statuses/retweets/%3Aid
 */
 
require '../../twitter-proxy.php';

// @todo deny trim_user so we know if user is protected

Proxy::share_cache();

Proxy::relay( 'statuses/retweets' );
