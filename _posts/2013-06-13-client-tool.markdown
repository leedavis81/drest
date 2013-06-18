---
layout: post
title:  "Client Tool"
date:   2013-06-13 13:32:55
categories: docs
---

Drest provides a client tool which is wrapped around [guzzle](http://guzzlephp.org/) that allows your API users to operate solely using PHP data objects.
This completely removes the hassle of dealing with syntactical errors when building a document to be pushed to your API endpoint.

Drest uses the [zend/code](https://github.com/zendframework/Component_ZendCode) component to generate data classes from the expose settings you have on your **@Drest\Route's**.
For example if you had an Entity where you only allowed exposure for a single attribute, then a data object will be created for that entity with only that attribute available.

These classes can then be used to easily send data to your drest api endpoint. The data objects are converted into a [representation]({{site.url}}/docs/representation) internally. 

###Code generator

In the /bin directory of the Drest repository you'll find a drest-client.php file. This is what the API consumers will need to execute to generate data objects.
It's required that the client supply the endpoint to your API when executing this command. An OPTIONS request will be performed on that endpoint with a custom HTTP header **X-DrestCG** to tell the server to pass back code generation information.

{% highlight php %}
php drest-client.php classes:generate http://yourapi.endpoint
{% endhighlight %} 

There a a few additional options you can pass when running the classes:generate command;
- --dest-path   - A path to where you would like to the classes to be written (default to current directory)
- --namespace   - A namespace to be added to the generated classes (none used by default)        
       
{% highlight php %}       
// Generate data classes to interact with
php drest-client.php classes:generate --dest-path="/path/for/SomeApi" --namespace="SomeApi" http://yourapi.endpoint

Generating client classes....
Successfully wrote client class "/path/for/SomeApi/Entities/Address.php"
Successfully wrote client class "/path/for/SomeApi/Entities/Profile.php"
Successfully wrote client class "/path/for/SomeApi/Entities/PhoneNumber.php"
Successfully wrote client class "/path/for/SomeApi/Entities/User.php"

Client classes have been sucessfully generated at "/path/for/SomeApi"       
{% endhighlight %}        
The classes will automatically be provided with getters and setters for both attributes and relations. They'll also all have the static method **create** to allow for quick /simple construction.
        
###Sending Requests

Once the classes have been generated they're ready to be used with the **Drest\Client** tool. 
You must first instantiate a **Drest\Client** instance and pass in the API endpoint to be used (not the URI) and the representation class you would like to operate with.
For example:
{% highlight php %}
$client = new Drest\Client('http://yourapi.endpoint', 'Json');
{% endhighlight %} 
This tells the client to pass a Json representation to the server for any push \[POST/PUT/PATCH\] requests, and to receive that representation for pull \[GET\] requests.

#####POST example 
In this example the a Json representation object is automatically created when the **post** method is called.  
{% highlight php %}
$client = new Drest\Client('http://yourapi.endpoint', 'Json');
$user = SomeApi\Entities\User::create()
        ->setEmailAddress('hello@somewhere.com')
        ->setUsername('leedavis81')
        ->setProfile(SomeApi\Entities\Profile::create()
            ->setTitle('mr')
            ->setFirstname('lee')
            ->setLastname('davis'))
        ->addPhoneNumbers(array(
            SomeApi\Entities\PhoneNumber::create()->setNumber('02087856589'),
            SomeApi\Entities\PhoneNumber::create()->setNumber('07584565445')));

try
{
    // Return an instance of Drest\Client\Response
    $response = $client->post('/user', $user);
    // Get the representation that was created for the $user data object (if you want to)
    $representation = $response->getRepresentation();
} catch (\Drest\Error\ErrorException $e)
{
    echo $e->getErrorDocument()->getMessage();
}
{% endhighlight %} 

You can optionally include an array of $headers as a second parameter when performing a POST. This will be common amongst all request types. If you want to include post variables you can simply add them to the URI string. For example:
{% highlight php %}
$response = $client->post('/user?foo=bar', $user, array('X-Custom-Header' => 'Custom Value'));
{% endhighlight %} 



#####GET example 
An **Accept** HTTP header will already be set with the content type of the representation class your using. eg "application/json"
{% highlight php %}
$client = new Drest\Client('http://yourapi.endpoint', 'Json');
try {
    $response = $client->get('user/85');
    // Get the representation instance
    $representation = $response->getRepresentation();
    var_dump(representation->toArray());
} catch (\Drest\Error\ErrorException $e)
{
    ..
}
{% endhighlight %} 

#####PUT example 
{% highlight php %}
$client = new Drest\Client('http://yourapi.endpoint', 'Json');
$user = SomeApi\Entities\User::create()
        ->setEmailAddress('newemail@somewhere2.com');
try
{
    $response = $client->put('/user/105', $user);
    if ($response->getStatusCode() == 200)
    {
        // all was ok
    }
} catch (\Drest\Error\ErrorException $e)
{
    ..
}
{% endhighlight %} 


#####PATCH example 
{% highlight php %}
$client = new Drest\Client('http://yourapi.endpoint', 'Json');
$user = Client\Entities\User::create()
        ->setEmailAddress('newemail@somewhere2.com');
try
{
    $response = $client->patch('/user/106', $user);
    if ($response->getStatusCode() == 200)
    {
        // all was ok
    }
} catch (\Drest\Error\ErrorException $e)
{
    ..
}
{% endhighlight %} 

#####DELETE example
{% highlight php %}
$client = new Drest\Client('http://yourapi.endpoint', 'Json');
try
{
    $response = $client->delete('/user/123');
    if ($response->getStatusCode() == 200)
    {
        // all was ok
    }
} catch (\Drest\Error\ErrorException $e)
{
    ..
}
{% endhighlight %} 



