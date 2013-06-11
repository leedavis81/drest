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


### Configuring the drest manager
Once you have your entity manger set up you can now create the drest manager object. This object is responsible for dispatching your application requests.
It has an internal router loaded up with any @Route definitions you've specified on your entities. Once the dispatch() method is called any requests to that location are routed accordingly. 
        

{% highlight php %}
$drestConfig = new \Drest\Configuration();
$drestConfig->addPathsToConfigFiles($pathToEntities);

$drestManager = \Drest\Manager::create($em, $drestConfig);

echo $drestManager->dispatch();    
{% endhighlight %}    

####Request object
To be able to effectively route requests drest requires the use of a Request object. These are typically immutable objects that provide request information for inpection
####Response object

$request = null, $response = null, $namedRoute = null, array $routeParams = array()

When dispatch() is called without any parameters the the manager will internally attempt to create both a Request and Response object for use.
These will contain adapters for use with **symfony/http-foundation** component. 

If you're using a framework and you already have a request and response object available and there is an adapter
In this call above will require the ability to load the **symfony/http-foundation** component.

        - Adding paths to your configuration files
        
        - Using the Drest Namespace
        
         
            * newDefaultAnnotationDriver($pathToEntities, __false__);
        #### Configuring the cache
            - Injecting doctrine ORM entity manager
        * see "Configuration" object section

### Exposing entities

