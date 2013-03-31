This directory structure mimics the Twitter API endpoints.

In the simplest case you can request the PHP files directly. 
In more complex cases you may want to perform server rewrites to request the endpoints.

## Warning

Exposing authenticated calls to the Twitter API via Ajax web services is potentially idiotic.
Avoid exposing private data or providing privileged access to the Twitter API via your authenticated keys.

This library makes some attempt to protect against this, but in the interests of performance it mainly acts as a dumb proxy.

Put your own safety measures in place to ensure against security and privacy problems. 
