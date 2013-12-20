---
layout: post
title:  "Error Handling"
date:   2013-06-18 09:24:55
categories: docs
---

In the same way that a resource representation can be exchanged between client and server without any knowledge of the underlying data structure, so can error documents.

Drest comes shipped with a number of error response documents. Which document is used will be determined by a client's Accept header when they make a request. 

All [representation]({{site.url}}/docs/representations/) classes will have a corresponding error response document. 
So if a client performed a \[GET\] request to an unavailable resource with an **Allow: application/json** HTTP header, they're likely to receive a **Drest\Error\Response\Json** document that'll produce something like:
{% highlight php %}
HTTP/1.1 404 Not Found
Content-Type: application/json

{
  "message": "No resource available"
}
{% endhighlight %}   

If your users are using the drest [client tool]({{site.url}}/docs/client-tool) then they can catch an exception and get a hook onto the error document that was created on the server. They can also get hold of a **Drest\Client\Response** instance. 

{% highlight php %}
$user = ...user object creation..;
try
{
    $response = $client->post('/users', $user);
} catch (\Drest\Error\ErrorException $e)
{
    // Echo the error message: "Server Error"
    echo $e->getErrorDocument()->getMessage();
    // Echo the full error document: {"message": "Server Error"}
    echo $e->getErrorDocument()->render();
    // Get a hook on the Drest\Response object
    $response = $e->getResponse();
    switch ($response->getStatusCode())
    {
       ....Do something based on the status code
    }
}
{% endhighlight %}
For more information on how this work go to the [client tool]({{site.url}}/docs/client-tool) section.

If the server is in debug mode then no error document is created and the thrown exception will bubble up. See [advanced configuration]({{site.url}}/docs/advanced-configuration/) for more details.

###Hander Class

The error handler object is responsible for interpreting an error (exception) and deciding how to handle a response for it. It's job is to determine a suitable error message, and an HTTP status code to return back to the client.

A number of different exception types are passed into this object (including Doctrine ORM/DBAL, SQL Exceptions).

These documents can then be easily converted back into it's error document form on the client side. 

####Default handler

By default drest will use an instance of **Drest\Error\Handler\DefaultHandler** to handle errors. An example of a few of its behaviours are:

{% highlight php %}
    case 'Doctrine\ORM\NonUniqueResultException':
        $this->response_code = Response::STATUS_CODE_300;
        $error_message = 'Multiple resources available';
        break;
    case 'Doctrine\ORM\NoResultException':
        $this->response_code = Response::STATUS_CODE_404;
        $error_message = 'No resource available';
        break;
{% endhighlight %}                 
Of course these can all be overridden by creating and registering your own error handler.

####Create your own handler

To customise error messages and to be able to determine which HTTP statuses messages to use; based on the exception thrown you must create and register you own error handler.  

This class must extend **Drest\Error\Handler\AbstractHandler** and implement an **error** method with the following signature:
{% highlight php %}
public function error(\Exception $e, $defaultResponseCode = 500, ResponseInterface &$errorDocument)
{% endhighlight %} 

The exception is passed into the error call with a;
- $defaultResponseCode - This is an http status code that's been "suggested" from the service action.
- &$errorDocument - The error document object to be populated with an error message. 


Once this is created you must instantiate it and pass it into the drest manager **before** the dispatch() method is called. 
{% highlight php %}
// ..creation of the drest manager
$error_handler = new My\Error\Handler();
$dm->setErrorHandler($error_handler);
$dm->dispatch();
{% endhighlight %} 
All errors will now be passed through your custom error handler. Currently you can only have one error handler registered. 
