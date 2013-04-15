## API 1.1 Endpoints

This directory structure mimics the Twitter API endpoints.

In the simplest case you can request the PHP files directly. 
In more complex cases you may want to perform server rewrites to request the endpoints.

### Warning

Exposing authenticated calls to the Twitter API via Ajax web services is potentially idiotic.
Avoid exposing private data or providing privileged access to the Twitter API via your authenticated keys.

This library has various features to protect against security and privacy problems, but ultimately the responsibility lies with you to ensure against security and privacy problems. 


Flamingo is a hosted service using this library that only exposes safe methods. 
Sign up at [twproxy.eu](http://twproxy.eu) for a free trial.

