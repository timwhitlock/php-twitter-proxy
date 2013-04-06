<?php
/**
 * statuses/show
 * https://dev.twitter.com/docs/api/1.1/get/statuses/show/%3Aid
 */
 
require '../../twitter-proxy.php';

Proxy::relay( 'statuses/show' );
