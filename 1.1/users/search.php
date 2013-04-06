<?php
/**
 * users/search
 * https://dev.twitter.com/docs/api/1.1/get/users/search
 */
 
require '../../twitter-proxy.php';

Proxy::relay( 'users/search' );
