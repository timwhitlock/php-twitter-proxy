<?php
/**
 * statuses/mentions_timeline
 * https://dev.twitter.com/docs/api/1.1/get/statuses/mentions_timeline
 */
 
require '../../twitter-proxy.php';

Proxy::relay( 'statuses/mentions_timeline' );
