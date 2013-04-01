<?php
/**
 * Example configuration for php-twitter-proxy
 * Copy and rename this file to config.php
 * 
 * Obtaining these credentials is decoupled from the proxy library.
 * You could pull them from a database, or send the user through an OAuth flow, or hard code them.
 */

  

// Twitter application key and secret
// See: https://dev.twitter.com/apps 
define('TW_CONSUMER_KEY', '');
define('TW_CONSUMER_SEC', '');



// Authenticated user access token, obtained from your own user flow
// See: https://dev.twitter.com/docs/auth/obtaining-access-tokens
define('TW_ACCESS_KEY', '');
define('TW_ACCESS_SEC', '');



// Authenticated bearer token for "application only" methods
// See: https://dev.twitter.com/docs/auth/application-only-auth
define('TW_BEARER_TOK', '');



// Lock screen_name and user_id parameters in all API calls to autheticated user
// This prevents other people using your endpoints for their own Twitter feeds
define('TW_LOCK_USER_ID',   '');
define('TW_LOCK_USER_NAME', '');



// response format - currently only JSON supported
define('TW_CONTENT_TYPE', 'application/json; charset=utf-8' );


// caching engine, defaults to APC
define('TW_CACHE_ENGINE', 'apc' );


// cache key namespace
define('TW_CACHE_PREFIX', 'twproxy_' );


// Comma separated list of supported HTTP methods.
// It's recommended to remove POST support.
define('TW_ALLOW_METHODS', 'GET,POST' );


// RegExp to limit permitted HTTP Referrers
// This is simply designed to prevent others using your proxy from JavaScript. The referrer is easily forged via other means.
define('TW_MATCH_REFERRER', '!^https?://(?:localhost|mydomain\.com)/!' );


