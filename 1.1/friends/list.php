<?php
/**
 * friends/list
 * https://dev.twitter.com/docs/api/1.1/get/friends/list
 */
 
require '../../twitter-proxy.php';
 
Proxy::relay( 'friends/list' );
