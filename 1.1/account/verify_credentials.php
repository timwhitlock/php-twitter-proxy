<?php
/**
 * account/verify_credentials
 * https://dev.twitter.com/docs/api/1.1/get/account/verify_credentials
 */
 
require '../../lib/twitter-proxy.php';
require '../../lib/twitter-client.php';
require '../../config.php';

proxy_user_request( 'account/verify_credentials' );
