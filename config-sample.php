<?php
/**
 * Example configuration for php-twitter-proxy
 * Copy and rename this file to config.php
 */


// The following perform security checks for ALL proxy requests.
// Failures result in immediate HTTP error response.


// Restrict permitted HTTP methods.
// It's recommended to remove POST support if your proxy is public.
Proxy::match_methods('GET,POST' );


// Restrict permitted HTTP Referrers.
// This is simply designed to prevent others using your proxy from JavaScript. The referrer is easily forged via other means.
Proxy::match_referrer('!^https?://(?:localhost|mydomain\.com)/!');


// Restrict permitted remote IP addresses
// This is pointless if using with JavaScript.
Proxy::match_remote_addr('/^(127/.0/.0/.1|192/.168/.0/.\d+)$/');




/**
 * Global security checks are passed, configure API client and return control to the end point.
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



// Lock screen_name and user_id parameters in all API calls to autheticated user
// This prevents other people using your endpoints for their own Twitter feeds
define('TW_LOCK_USER_ID',   '');
define('TW_LOCK_USER_NAME', '');


// caching engine - currently only APC supported and is enabled by default
// Proxy::disable_cache();
// Proxy::enable_cache( 'apc', 'twproxy_', 60 );
