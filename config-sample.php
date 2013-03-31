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
