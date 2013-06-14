---
layout: post
title:  "Getting Started"
date:   2013-06-08 20:24:55
categories: docs
---
### Autoloading your entities
Drest doesn't handle the loading of your Entity classes so it's important this is set up before hand.
It's very likely that if you're already running Doctrine ORM then you've taken care of registering your Entity classes on an autoloader (along with your Proxy or Repository classes).
If you're using composer you can leverage it's autoloader and register your Entities on it. Like so:

{% highlight php %}
// Register your entities on an autoloader (you may have already taken care of this)
$loader = require '/path/to/vendor/autoload.php';

// Add the entities namespace to the loader (namespace, path)
$loader->add('Entities', __DIR__.'/../');
{% endhighlight %}


### Setting up your ORM entity manager
**You may have already done this, and can skip this part**. 

To instantiate the drest manager object you must pass it an instance of doctrine ORM's EntityManager. 
If your application is already using the ORM tool this this will be set up. If not, here's an example configuration to help.

Please refer to the [Doctrine ORM manual](http://docs.doctrine-project.org/en/latest/) for more information.

{% highlight php %}
$ormConfig = new \Doctrine\ORM\Configuration();

$pathToEntities = array(__DIR__ . '/../Entities');
$ORMDriver = $ormConfig->newDefaultAnnotationDriver($pathToEntities, false);

$ormConfig->setMetadataDriverImpl($ORMDriver);

// Other various configuration options..
$ormConfig->setProxyDir(__DIR__ . '/Entities/Proxies');
$ormConfig->setProxyNamespace('Entities\Proxies');
$ormConfig->setAutoGenerateProxyClasses(true);

// Creation of the entity manager
$em = \Doctrine\ORM\EntityManager::create(array(
    'host' => 'localhost',
    'user' => 'username',
    'password' => 'password',
    'dbname' => 'drest',
    'driver' => 'pdo_mysql'
), $ormConfig);
{% endhighlight %}  

When setting up your doctrine ORM entity manager ensure that your **not using the SimpleAnnotationReader class**. 
This is used to allow the ORM to read annotations without requiring a namespace declaration on the docblock. 
So for example this would allow **@Column** instead of the more explicit **@ORM\Column**. The former example would obviously cause a clash when using multiple drivers to read annotations, so make sure you use the fully namespaced example.

If you use the ORM's convenience method newDefaultAnnotationDriver(), ensure you pass the second parameter as *false*. Like so:

{% highlight php %}
$driver = $ORMConfig->newDefaultAnnotationDriver($pathToEntities, false);
{% endhighlight %}


### Configuring the drest manager
Once you have your entity manger set up you can now create the drest manager object. This object is responsible for dispatching your application requests.
It has an internal router loaded up with any @Route definitions you've specified on your entities. Once the dispatch() method is called any requests to that location are routed accordingly.

One requirement when creating the drest manager is that you provide your configuration object with an array of paths to where your entities are located.  
It's also advised that you provide a caching mechanism for the annotations reader. 
In the same fashion as Doctrine's ORM tool drest interacts with data objects generated from reading the annotations you've supplied. 
Parsing these are computationally expensive, and so a caching mechanism is imperative for a production environment. You can use any of the cache adapters provided by doctrine.
        

{% highlight php %}
$drestConfig = new \Drest\Configuration();
$drestConfig->addPathsToConfigFiles($pathToEntities);
$drestConfig->setMetadataCacheImpl(new \Doctrine\Common\Cache\ArrayCache());   // use a different adapter for production

$drestManager = \Drest\Manager::create($em, $drestConfig);

echo $drestManager->dispatch();    
{% endhighlight %}    

####Request object
To be able to effectively route requests drest requires the use of a Request object. 
These are typically immutable objects that provide information on the HTTP request.
When calling the *dispatch()* method from drest manager object, you can optionally pass in your framework request object *(if an adapter has been created for it)*. 
The drest manager will the maintain an instance of **Drest\Request** which will act as a proxy to your actual request object.

{% highlight php %}
echo $drestManager->dispatch($myRequestObj);    
{% endhighlight %} 

If you don't pass in a request object then **Drest\Request** will default to creating an adapted instance of **Symfony\Component\HttpFoundation\Request**. 
In this instance its required that you have the **symfony/http-foundation** component installed.

####Response object
Once drest has determined what content is to be written back to the user after an API request, a response object is required to do so.
As with the request object you can pass in your own response object on the *dispatch()* call to be populated *(if an adapter has been created for it)*.

{% highlight php %}
$response = $drestManager->dispatch($myRequestObj, $myResponseObj);
// echo the response 
echo $response;
// Fetch back my original object
$myResponseObj = $response->getResponse();    
{% endhighlight %} 
 
The drest manager will then maintain an instance of **Drest\Response** which will act as a proxy to your actual response object.
The *dispatch()* call will return this **Drest\Response** instance which you can either echo directly (to send the headers and display the content),
or manipulate further. You can even retrieve your original request object, updated with respective status, header and content information by calling *getResponse()*.

Again, if no object is passed drest will attempt to create an instance of **Symfony\Component\HttpFoundation\Response** for use. This will require the installation of the the **symfony/http-foundation** component.

 

        
####@todo Using the Drest Namespace
 
####@todo Configuring the cache
    - Injecting doctrine ORM entity manager
* see "Configuration" object section


####@Already using a framework
   #####@todo Creating your own response / request adapters

### Create Routes
To begin leveraging drest you need to include the annotation namespace into your entities. It's likely you've already done something similar for the ORM.
Once declared you can enable an entity as a resource by using the **@Drest\Resource** annotation.


{% highlight php %}
namespace Entities;

use Drest\Mapping\Annotation as Drest;
use Doctrine\ORM\Mapping as ORM;

/**
 * @Drest\Resource(
 *      representations={"Json", "Xml"},
 *      routes={
 *          @Drest\Route(
 *              name="get_user",
 *              routePattern="/user/:id",
 *              verbs={"GET"}
 *          ),
 *          @Drest\Route(...)
 *      }
 * )
 *
 * @ORM\Table(name="user")
 * @ORM\Entity
 */
class User
{
    // user properties

}
{% endhighlight %} 


#####@Drest\Resource properties

- (array) **routes** *required* - an array of routes you want exposed on your API endpoint.
- (array) **representations** *optional* - the representations you would like to use to expose / fetch your data.
 Note: this can instead be set globally on your **Drest\Configuration** object.
 
 
However the bulk of your configuration will happen on the **@Drest\Route** annotations. As a minimum these provide mapping information to match client's request to a respective action.
But there are a number of other configuration options available.
 
{% highlight php %}

 @Drest\Route(
    name="update_user",
    routePattern="/user/:id",
    routeConditions={"id": "\d+"},
    verbs={"PUT", "PATCH"},
    expose={"email_address", "profile" : {"title", "firstname", "lastname"}},
    action="MyNamespace\CustomServiceClass",
    origin="get_user"
 ),
    
 @Drest\Route(
    name="get_users",
    routePattern="/users",
    verbs={"GET"},
    collection=true,
    allowOptions=false
 )
{% endhighlight %} 

#####@Drest\Route properties

* (string) **name** *required* - This is used as a unique identifier for the route. When attempting to dispatch a named route, this is the name you'll used.
* (array) **verbs** *required* - What verbs should be used to match this route. Can be any value thats available on **Drest\Request::METHOD_\*** constants
* (string) **routePattern** *required* - The pattern to be used for matching a request. For more information see the [routing section]({{site.url}}/docs/configuring-resources/#routing).
* (array) **expose** *optional*
   * For a **PULL** \[GET\] request this'll be an array of information you want to expose to the client.
   * For a **PUSH** \[POST/PUT/PATCH\] request any data not set for exposure will be stripped off when sent from the client.
   
* (boolean) **collection** *optional* - By default there are two types ways to fetch data, as a single entity (as given in the example below) or as a collection of data.
If you wanted to allow the user access to *all* entities of a certain type the add *collection=true* to the route configuration.
* (array) **routeConditions** *optional* - You can provide an array of conditions to be provided on route variables. For example routeConditions={"id": "\d+"} 
would ensure that the *id* variable passed in the url was a decimal before this route was deemed a match.

* (string) **action** *optional* - Drest comes shipped with a number of default behaviors that occur based on the HTTP verb used in the request. 
This however might not quite be the behaviour your after. You can instead use your own service action class. 
These must extend **\Drest\Service\Action\AbstractAction**

* (boolean) **allowOptions** *optional* - When a client performs an options request on route drest will collect all matching routes (excluding a verb check)
and return them on the *Allow* HTTP header. 
<br><br>Any mathing route configured with verb={"OPTIONS"} will take precedence over this. 
Instead of sepcifying this behaviour per route, it can instead be defined across all routes with **Drest\Configuration::setAllowOptionsRequest({boolean})**.
<br><br>So for example if you had routes \[PUT, PATCH\] **/user/:id**, \[POST\] **/users** and \[GET\] **/user:/id** and an OPTIONS request was
made to **/user/:id**, then a response would be sent back to the client with an *Allow* header containing "PATCH, PUT, GET". 
**This feature is on by default**. 

* (boolean) **origin** *optional* - An origin route is the inital path that details the resource your exposing. 
Whenever you update, or create an entity via a PUT/POST/PATCH route then it's always useful (and informative) to let the 
client know where they can view that created/updated entity. Now it maybe that it's not exposed at all, but if there is a means to view it, drest will attempt
to pass that information back to the client. These will typically be exposed as {entity name}/{primary key} but can be in any form. 
So by default drest will scan all route definitions within the resource group and try to find a GET route that contains
the primary key variable (eg (.*)?\/:id). By using the origin variable you can override this default behavior and mark a route to be forced as the origin. 
You can only mark one route within the a resource group. 

 
 
 -@todo Push handles
 
 
 -@todo System Errors
    Exception Handling
        - Debug mode
 
    

