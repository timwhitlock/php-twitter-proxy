<?php
/**
 * php-twitter-proxy
 * 
 * http://twproxy.eu
 * https://github.com/timwhitlock/php-twitter-proxy
 * @author Tim Whitlock <@timwhitlock>
 */

 

/**
 * Top-level Proxy class. All methods and properties are static
 */
abstract class Proxy {

    /* content type, currently JSON only */
    const TYPE_JSON = 'application/json; charset=utf-8';

    private static $type;


    /* caching configurations */
    private static $cache_engine = 'apc';
    private static $cache_prefix = 'twproxy_';
    private static $cache_minttl = 60;
    
    /* restricted users */
    private static $user_lock = array();
    
    /**
     * @var TwitterApiClient
     */
    private static $client;


    /**
     * Proxy a Twitter API call as authenticated user.
     * @param string e.g. account/verify_credentials
     * @param int Expiry in seconds
     * @return void
     */
    public static function relay( $path, $ttl = 60 ){
        try {
            
            // Twitter API params supported in GET and POST only
            if( 'POST' === $_SERVER['REQUEST_METHOD'] ){
                $method = 'POST';
                $args  = $_POST;
                $cache = false;
                $ttl   = 0;
            }
            else {
                $method = 'GET';
                $args  = $_GET;
                $cache = self::$cache_engine;
                $ttl   = $cache ? max( $ttl, self::$cache_minttl ) : 0;
            }
            
            // Twitter doesn't complain about unecessary parameters, but removing junk and "cache-busters" will improve caching
            // @todo proper array intersect of all suppoerted Twitter API args across methods
            unset( $args['_'] );
            
            // We want to ensure that the cache is hit for requests even when the JSONP callback is different
            unset( $args['callback'] );
                    
            // Fetch from cache if engine specified. Currently only APC supported
            // @todo use a faster method than md5 for key hash?
            if( $cache ){
                ksort( $args );
                $key  = self::$cache_prefix.'_'.md5( $path.serialize($args) );
                $data = apc_fetch($key) or $data = null;
            }

            if( isset($data) ){
                header('X-Cache: Proxy HIT' );
                // reduce TTL to life of cached data
                if( $ttl ){
                    $age = time() - $data['t'];
                    $ttl = max( 0, $ttl - $age );
                }
            }
            else {
                header('X-Cache: Proxy MISS' );

                // Request via pre-configured Twitter client
                $data = self::$client->raw( $path, $args, $method );

                if( $ttl ){
                    // extend TTL if rate limit has been reached for this request
                    $meta = self::$client->last_rate_limit_data();
                    if( $meta['limit'] && ! $meta['remaining'] ){
                        $ttl = max( $ttl, $meta['reset'] - time() );
                    }
                    // Cache response if successfull
                    if( $cache && 200 === $data['status'] ){
                        $data['t'] = time();
                        apc_store( $key, $data, $ttl );
                    }
                }
            }

            // @todo run security filters on data
            // e.g. strip tweets belonging to protected users

            // success, respond to client.
            self::respond( $data['body'], $data['headers']['content-type'], $data['status'], $ttl );

        }
        catch( Exception $Ex ){
            self::fatal( 500, $Ex->getMessage() );
        }
        
    }



    /**
     * Respond with proxied data and exit
     * @internal
     */
    private static function respond( $body, $type, $status = 200, $ttl = 0 ){
        
        // currently only supporting json
        // @todo support XML formats
        if( $type ){
            $isJSON = 0 === strpos( $type, 'application/json' );
        }
        else {
            $type = self::TYPE_JSON;
            $isJSON = true;
        }
        
        // wrap JSONP callback function as long as response is JSON
        if( ! empty($_REQUEST['callback']) && $isJSON ){
            $type = 'text/javascript; charset=utf-8';
            $body = $_REQUEST['callback'].'('.$body.');';
        }
        
        // handle HTTP status and expiry header
        if( 200 === $status ){
            if( $ttl ){
                $exp = gmdate('D, d M Y H:i:s', $ttl + time() ).' GMT';
                header('Pragma: cache', true );
                header('Cache-Control: public, max-age='.$ttl, true );
                header('Expires: '.$exp, true );
            }
        }
        else {
            header('HTTP/1.1 '.$status.' '._twitter_api_http_status_text($status), true, $status );
        }
        
        header('Content-Type: '.$type, true );
        header('Content-Length: '.strlen($body), true );
        echo $body;
        exit(0);    
    }




    /**
     * Fatal exit for proxy in similar format to Twitter API
     * @internal
     */
    public static function fatal( $status, $message = '' ){
        if( ! $message ){
            $message = _twitter_api_http_status_text( $status );
        }
        $errors[]= array (
            'code'    => -1, 
            'message' => $message,
        );
        // @todo serialize errors for non-json formats
        $body = json_encode( compact('errors') );
        self::respond( $body, self::$type, $status );
    }



    /**
     * Check a screen_name params for security purposes
     * @param string 
     */
    public static function match_screen_name( $screen_name ){
        if( self::$user_lock && ! isset(self::$user_lock[strtolower($screen_name)]) ){
            self::fatal( 403, 'Proxy disallows user @'.$screen_name );
        }
        return true;
    }



    /**
     * Check a user_id params for security purposes
     * @param string 
     */
    public static function match_user_id( $user_id ){
        if( self::$user_lock && ! in_array($user_id, self::$user_lock,true) ){
            self::fatal( 403, 'Proxy disallows user #'.$user_id );
        }
        return true;
    }



    /**
     * Check referrer header for JavaScript applications
     * @param string regexp pattern to match against HTTP Referer header
     */
    public static function match_referrer( $pattern ){
        if( empty($_SERVER['HTTP_REFERER']) ){
            self::fatal( 400 , 'Empty referrer' );
        }
        if( ! preg_match( $pattern, $_SERVER['HTTP_REFERER'] ) ){
            self::fatal( 403, 'Illegal referrer');
        }
        return true;
    }



    /**
     * Check remote IP address for whitelisting.
     */
    public static function match_remote_addr( $pattern ){
        $ips[] = $_SERVER['REMOTE_ADDR'];
        //isset($_SERVER['HTTP_CLIENT_IP']) and $ips[] = $_SERVER['HTTP_CLIENT_IP'];
        //isset($_SERVER['HTTP_X_FORWARDED_FOR']) and $ips[] = $_SERVER['HTTP_X_FORWARDED_FOR'];
        foreach( $ips as $ip ){
            if( ! preg_match( $pattern, $ip ) ){
                self::fatal( 403, 'Illegal IP address: '.$ip );
            }
        }
        return true;
    }



    /**
     * Check HTTP request method
     * @param comma-separated list of permitted HTTP methods
     * @return string method set
     */
    public static function match_methods( $allowed = 'GET' ){
        $method = $_SERVER['REQUEST_METHOD'];
        if( false === stripos( $allowed, $method ) ){
            self::fatal( 405, 'Proxy forbids '.$method );
        }
        return $method;
    }



    /**
     * Set a cache engine and minimum TTL of request cachees
     * @param string Caching engine, currently only APC
     * @param string key prefix for cache entries
     * @param int optional minimum TTL for all requests
     * @return void
     */
    public static function enable_cache( $engine = 'apc', $prefix = 'twproxy_', $minTTL = 60 ){
        self::$cache_engine = $engine;
        self::$cache_prefix = $prefix;
        self::$cache_minttl = $minTTL;
    }



    /**
     * Disable caching of requests
     * @return void
     */
    public static function disable_cache(){
        self::$cache_engine = '';
        self::$cache_minttl = 0;
    }
    
    
    
    /**
     * configure application consumer
     * @param string key
     * @param string secret
     * @return void
     */
    public static function init_client( $consumer_key, $consumer_sec ){
        self::$client = new TwitterApiClient;
        self::$client->set_oauth_consumer( new TwitterOAuthToken($consumer_key, $consumer_sec) );
    }
    
    
    
    /**
     * configure application access
     * @param string key
     * @param string secret
     * @return void
     */
    public static function auth_client( $access_key, $access_sec ){
        self::$client or self::fatal( 500, 'No API client configured' );
        self::$client->set_oauth_access( new TwitterOAuthToken( $access_key, $access_sec ) );
    }
    
    

    /**
     * Lock request to certain users. Only checked for certain API calls.
     * @param array locked users in format { screen_name : user_id, .. }
     * @return void
     */
    public static function lock_users( array $allowed ){
        foreach( $allowed as $screen_name => $user_id ){
            self::$user_lock[ strtolower($screen_name) ] = (string) $user_id;
        }
    }
    


}



require __DIR__.'/lib/twitter-client.php';
require __DIR__.'/config.php';


