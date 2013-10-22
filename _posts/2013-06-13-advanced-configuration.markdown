---
layout: post
title:  "Advanced Configuration"
date:   2013-06-13 13:32:55
categories: docs
---

One requirement when [creating the drest manager]({{site.url}}/docs/getting-started/#configuring_the_drest_manager) is that a configuration object is passed in.
Manipulating this object will allow you to enable a number of custom behaviours for your API.

###Debug Mode 

During development (certainly if your using a browser) it can be more useful to see any exceptions thrown.
By turing on the debug mode ANY exception that's thrown on a request will not be caught and pushed into an error handler. Meaning you can either let it bubble up and catch it yourself, or simply let it display on screen for easier debugging.   
This can be enabled by using the following configuration setting:

{% highlight php %}
$drestConfig->setDebugMode(true);
{% endhighlight %}
<div class="alert alert-error"><strong>Do not</strong> leave this on in a production environment.</div>


### Request / Response Adapters

As mentioned in [configuring the drest manager]({{site.url}}/docs/getting-started/#configuring_the_drest_manager) drest will construct request and response objects which will operate as adapters to their "framework specific" counterparts.
So that these object have no knowledge of the available adapter classes they are registered with the configuration object. 
When passing your framework object into the drest manager's dispatch() method, drest will see if the object matches any of the types registered. 

{% highlight php %}

// register a request adapter class
$drestConfig->registerRequestAdapterClasses(array(
    'Drest\\Request\\Adapter\\ZendFramework2',
    'Drest\\Request\\Adapter\\Symfony2'
));

// unregister request adapter class
$drestConfig->unregisterRequestAdapterClass('Drest\\Request\\Adapter\\ZendFramework2');

// register the default response adapter classes
$drestConfig->registerResponseAdapterClasses(array(
    'Drest\\Response\\Adapter\\ZendFramework2',
    'Drest\\Response\\Adapter\\Symfony2',
    'Drest\\Response\\Adapter\\Guzzle',
));

{% endhighlight %}

<div class="alert alert-info">If you do create any additional framework adapters, please be nice and share them.</div>

###415 No media Match

In the instance of drest being [unable to detect the required representation]({{site.url}}/docs/representations/#content_type_detection) on a pull request \[GET\]. Rather than using a default representation drest can instead send a HTTP 415 "Unsupported Media Type" response back to the client.
This can be enabled by using the following configuration setting:

{% highlight php %}
$drestConfig->set415ForNoMediaMatch(true);
{% endhighlight %} 

This behaviour is actually managed by the (default) error response handler. If you've created a custom one you'll need to manually set the 415 response status in the event of a **Drest\Representation\UnableToMatchRepresentationException**.
 
 
###Allow options requests

"The OPTIONS method represents a request for information about the communication options available on the request/response chain identified by the Request-URI. This method allows the client to determine the options and/or requirements associated with a resource, or the capabilities of a server, without implying a resource action or initiating a resource retrieval." - [http://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html](http://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html)
 
By enabling this feature drest will scan for any routes defined with the same path pattern and send back a response with their verbs, comma delimited in a HTTP 'Allow' header.

{% highlight php %}
 $drestConfig->setAllowOptionsRequest(true);
{% endhighlight %}

Would, for example, enable the following HTTP response for an OPTIONS request to /user/123 
{% highlight php %}
HTTP/1.0 200 OK
Allow: GET, PUT, PATCH
{% endhighlight %} 
<div class="alert alert-info">Any matching <strong>@Drest\Routes</strong> defined using the OPTIONS verb will take precedence over this behaviour.</div>

 
###Route base paths

In the instance that you'd want to add a base path to all the defined **@Drest\Route**'s you can do so via the following configuration:
{% highlight php %}
$drestConfig->addRouteBasePath('/v1');
// Now a "[GET] /v1/user/123" request will match a routePattern="/user/123"
{% endhighlight %}
You can add as many base paths as you like.  
<div class="alert alert-error">I'm not for one second advocating that you version your API using the URI. This is just an example.</div>