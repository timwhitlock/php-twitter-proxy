<?php
/**
 * account/verify_credentials
 * https://dev.twitter.com/docs/api/1.1/get/account/verify_credentials
 */
 
require '../../twitter-proxy.php';

Proxy::relay( 'account/verify_credentials' );
