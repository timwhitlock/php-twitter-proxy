<?php
/**
 * statuses/retweets
 * https://dev.twitter.com/docs/api/1.1/get/statuses/retweets/%3Aid
 */
 
require '../../twitter-proxy.php';

// @todo do retweets need protecting?
//Proxy::protected_user_post_check();

Proxy::share_cache();

Proxy::relay( 'statuses/retweets' );
