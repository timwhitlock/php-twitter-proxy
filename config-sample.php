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


// Restrict permitted HTTP Origin headers.
// Similar to above, but specifically for Ajax requests.
Proxy::match_origin('!^https?://(?:localhost|mydomain\.com)!');


// Restrict permitted remote IP addresses
// This is pointless if using with JavaScript.
Proxy::match_remote_addr('/^(127/.0/.0/.1|192/.168/.0/.\d+)$/');




// Global security checks have passed.
// The following configures the Twitter client for proxying request


// Twitter application key and secret
// See: https://dev.twitter.com/apps 
Proxy::init_client( 'Your application consumer token', 'Your consumer token secret' );


// Authenticated user access token.
// See: https://dev.twitter.com/docs/auth/obtaining-access-tokens
// Obtaining an access token is beyond the scope of this library.
// You could pull them from a database, or send the user through an OAuth flow, or just hard code them.
Proxy::auth_client( 'Your authenticated access token', 'Your access token secret' );


// Lock screen_name and user_id parameters in some API calls.
// This prevents other people using some endpoints for their own Twitter feeds
Proxy::lock_users( array( 'your screen_name' => 'your user_id' ) );


// caching engine - currently only APC supported and is enabled by default
Proxy::enable_cache( 'apc', 'your_prefix' );
// Proxy::disable_cache();
