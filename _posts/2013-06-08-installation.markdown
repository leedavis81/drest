---
layout: post
title:  "Installation"
date:   2013-06-08 20:24:55
categories: docs
---
The recommended way to install drest is by using composer.

{% highlight php %}
// install directly
composer require leedavis81/drest

// or add the following directly to your composer.json file
require: {
    "leedavis81/drest": "dev-master"
}
{% endhighlight %}

 Alternatively you can download or clone it directly from github.
 
 <a class="btn btn-medium btn-primary" href="https://github.com/leedavis81/drest/archive/master.zip">Github Download</a> 

### Configuration

For convenience an [example application configuration](https://github.com/leedavis81/drest/blob/master/examples/application1/public/index.php) is available in the project's source.

To configure drest you simply have to create a manager object instance. For this to be constructed you're required to pass in a doctrine ORM entity manager object, along with your drest configuration.

The paths to your entity classes must be provided to configuration object so drest knows where to read annotations from. It's likely you've already configured this for your ORM entity manager.

There are a number of conventional behaviours that are set when creating the Configuration object. You can see these by reading the **Drest\Configuration** \__construct() method.
{% highlight php %}
$config = new Drest\Configuration();
$config->addPathsToConfigFiles($path_to_entities_array);

$dm = Drest\Manager::create($ORMEntityManager, $config);
$dm->dispatch();
{% endhighlight %}

### Usage

Once you have your drest manager all set up, you're ready to start annotating your entities. 
To enable an entity as a resource you simple need to add a **@Drest\\Resource** annotation to the top of your entity class. 
You can then start exposing routes by providing a route pattern, a name (must be unique) and a set of verbs to match on that route.

So for example the following annotation will enable you to select that entity by using the route /user/{identifier}. 
Any parameter used on the route pattern will automatically be used as a filter.

{% highlight php %}
@Drest\Resource(
    routes={
        @Drest\Route(
            name="get_user",
            routePattern="/user/:id",
            verbs={"GET"}
        )
    }
)
{% endhighlight %}
The behaviours used to fetch this data are encapsulated in a "Service\Action" class. 
There are a number of default actions in place that will be executed based on the verbs used in the request, and whether it's dealing with a single entity or a collection. 
You can easily create your own actions, register them into the Configuration object and reference them in your annotations.

### Dependencies
There are a few required dependencies that drest needs to get going. 

- (server) **doctrine/common** - used for reading annotations, setting up caching, using inflection and more.
- (server) **zendframework/zend-code** - by reading what data you want exposed on your server, drest will create data objects to be used on client calls.
- (client) **guzzle/guzzle** - guzzle is a very powerful HTTP client. This is used as a transport mechanism on the Drest client tool.
- (optional) **symfony/http-foundation** - If you plan to use drest with an existing framework (that adapters have been created for) then you don't need to include this dependency.
If you're not using a framework the drest will require this component.

For further information on drest's dependencies, see the project [composer.json](https://github.com/leedavis81/drest/blob/master/composer.json) file

