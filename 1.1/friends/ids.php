<?php
/**
 * friends/ids
 * https://dev.twitter.com/docs/api/1.1/get/friends/ids
 */
 
require '../../twitter-proxy.php';

Proxy::relay( 'friends/ids' );
