<?php
/**
 * php-twitter-proxy
 * http://twproxy.eu
 * https://github.com/timwhitlock/php-twitter-proxy
 * 
 * @author Tim Whitlock, http://timwhitlock.info
 * @license MIT
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
    private static $cache_prefix = 'twproxy';
    private static $cache_minttl = 60;
    private static $cache_shared = false;
    
    /* restricted users */
    private static $user_lock = array();
    
    /**
     * @var TwitterApiClient
     */
    private static $client;
    
    /**
     * user alias for private caching, etc..
     * @var string
     */    
    private static $alias;     
    
    /**
     * post-request filters
     */     
    private static $filters = array();
    
    /**
     * Allowed referrer/origin for CORS
     */
    private static $origin;    
    
    /**
     * Restricted end point list.
     * All allowed by default
     * @param array
     */    
    private static $endpoints = array();    

    /**
     * Proxy a Twitter API call as authenticated user.
     * @param string e.g. account/verify_credentials
     * @param int Expiry in seconds
     * @return void
     */
    public static function relay( $path, $ttl = 60 ){
        try {
            
            // restrict endpoints
            while( self::$endpoints ){
                foreach( self::$endpoints as $starts ){
                    if( 0 === strpos($path,$starts) ){
                        break 2;
                    }
                }
                self::fatal( 403, 'End point not permitted' );
            }
            
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
            
            // never trim user records when we're running post filters
            if( self::$filters && ! empty($args['trim_user']) ){
                self::$filters[] = array( 'Proxy', 'filter_trim_user' );
                unset($args['trim_user']);
            }
                    
            // Fetch from cache if engine specified. Currently only APC supported
            if( $cache ){
                $key = self::$cache_prefix;
                if( self::$cache_shared ){
                    $key .= '_$shared'; // <- ensure no collision with twitter names
                }
                else {
                    $key .= '_@'.self::$alias;
                }
                $key .= '_'.str_replace('/','_',$path).'_'.self::hash_args($args);
                $data = self::cache_fetch($key) or $data = null;
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
                $ok = 200 === $data['status'];

                // run security filters on data - requires deserialization
                if( $ok && self::$filters ){
                    $struct = json_decode( $data['body'], true );
                    foreach( self::$filters as $callee ){
                        $struct = call_user_func( $callee, $struct );
                    }
                    $data['body'] = json_encode($struct);
                }

                if( $ttl ){
                    // extend TTL if rate limit has been reached for this request
                    $meta = self::$client->last_rate_limit_data();
                    if( $meta['limit'] && ! $meta['remaining'] ){
                        $ttl = max( $ttl, $meta['reset'] - time() );
                    }
                    // Cache response if successfull
                    if( $cache && $ok ){
                        $data['t'] = time();
                        self::cache_store( $key, $data, $ttl );
                    }
                }
            }

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
        
        // CORS:
        // note that current default is open. restrict with match_origin if this is not desirable.
        if( self::$origin ){
            header('Access-Control-Allow-Origin: '.self::$origin );
        }
        else {
            header('Access-Control-Allow-Origin: *');
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
     * Check Origin header for Ajax applications
     * @param string regexp pattern to match against HTTP Origin header
     */
    public static function match_origin( $pattern ){
        if( empty($_SERVER['HTTP_ORIGIN']) ){
            self::fatal( 400 , 'Empty origin' );
        }
        if( ! preg_match( $pattern, $_SERVER['HTTP_ORIGIN'] ) ){
            self::fatal( 403, 'Illegal origin');
        }
        self::$origin = $_SERVER['HTTP_ORIGIN'];
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
    public static function enable_cache( $engine = 'apc', $prefix = 'twproxy', $minTTL = 60 ){
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
     * Allow the cached response to be shared by all users.
     * Caching is private to the authenticated user by default
     */
    public static function share_cache(){
        self::$cache_shared = true;
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
     * @param string optional alias for authed user, defaults to user_id found in key prefix
     * @return void
     */
    public static function auth_client( $access_key, $access_sec, $alias = '' ){
        self::$client or self::fatal( 500, 'No API client configured' );
        self::$client->set_oauth_access( new TwitterOAuthToken( $access_key, $access_sec ) );
        self::$alias = $alias or self::$alias = current( explode('-',$access_key,2) );
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
    
    
    
    /**
     * Pre-check a user for protected status.
     * This has a performance overhead, so only use for methods that can't establish private status on data
     */    
    public static function protected_user_pre_check(){
        $args = array_intersect_key( $_REQUEST, array( 'user_id' => '', 'screen_name' => '' ) );
        $user = self::get_user($args) or self::fatal( 404, 'User not found' );
        if( empty($user['protected']) ){
            return true;
        }
        // Twitter would return 401 "Not authorized", but we are *authenticated*, so I'm returning 403 
        self::fatal( 403, 'Protected Twitter account' );
    }   
    
    
    
    /**
     * Flag request for a post-check for protected user data
     */
    public static function protected_user_post_check(){
        self::$filters[] = array( 'Proxy', 'filter_protected_users' );
    }    
    
    
    
    /**
     * Strip protected data from deserialized data
     * @internal
     */
    private static function filter_protected_users( array $data, $depth = 0 ){
        // data could be: 
        // 1. a status with a user property
        if( isset($data['text']) ){
            if( isset($data['user']) && ! empty($data['user']['protected']) ){
                if( ! $depth ){
                    self::fatal( 403, 'Protected Twitter account' );
                }
                $data = array_intersect_key( $data, array('user'=>1,'id'=>1,'id_str'=>1) );
                $data['proxy_removed'] = true;
            }
        }
        // 2. a user with a status property
        else if( isset($data['screen_name']) ){
            if( ! empty($data['protected']) ){
                // protected user must have status removed
                // @todo any other sensitive properties?
                $data['status'] = array( 'proxy_removed' => true );
            }
        }
        // 3. a list of statuses, or users
        else {
            $depth++;
            foreach( $data as $i => $struct ){
                $data[$i] = self::filter_protected_users( $struct, $depth );
            }
        }
        return $data;
    }    
    
    
    
    /**
     * Trim user if we forcefully un-trimmed earlier
     */
    private static function filter_trim_user( array $data, $depth = 0 ){
        // data could be: 
        // 1. a user object
        if( isset($data['screen_name']) ){
            // not actually supported by the Twitter API, but we may as well
            $data = array_intersect_key( $data, array( 'id'=>1,'id_str'=>1) );
        }
        // 2. a status with a user object
        else if( isset($data['user']) ){
            $data['user'] = self::filter_trim_user( $data['user'], $depth+1 );
        }
        // 3. a list of statuses, or users
        else {
            $depth++;
            foreach( $data as $i => $struct ){
                $data[$i] = self::filter_trim_user( $struct, $depth );
            }
        }
        return $data;
    }    
    
    
    
    /**
     * Internal user lookup
     * @internal
     */
    private static function get_user( array $args ){
        $args['skip_status'] = true;
        if( self::$cache_engine ){
            $key  = self::$cache_prefix.'_$internal_users_show_'.self::hash_args($args);
            $user = self::cache_fetch($key) or $user = null;
        }
        if( ! isset($user) ){
            try {
                $user = self::$client->call( 'users/show', $args );
            }
            catch( TwitterApiException $Ex ){
                self::fatal( $Ex->getStatus(), $Ex->getMessage() );
            }
            catch( Exception $Ex ){
                self::fatal( 500, $Ex->getMessage() );
            }
            // cache user lookup for longer
            if( self::$cache_engine ){
                self::cache_store( $key, $user, 86400 );
            }
        }
        return $user;
    }
    
    
    
    /**
     * Abstraction of cache fetch
     * @internal
     */
    private static function cache_fetch( $key ){
        return apc_fetch( $key );
    }    
    
    
    
    /**
     * Abstraction of cache set
     * @internal
     */
    private static function cache_store( $key, $data, $ttl ){
        return apc_store( $key, $data, $ttl );
    }    



    /**
     * Abstraction of argument hash
     * @todo establish better/faster method for this
     */
    private static function hash_args( array $args ){
         ksort( $args );
         return md5( serialize($args) );
    }
    
    
    /**
     * 
     */
    public static function allow_endpoint( $path ){
        self::$endpoints[] = $path;
    }


}



require __DIR__.'/lib/twitter-client.php';
require __DIR__.'/config.php';


