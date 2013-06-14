---
layout: post
title:  "Service Actions"
date:   2013-06-13 13:32:55
categories: docs
---


###What are they?
Service actions are responsible for handling what behaviours occur during a request.

Drest comes with a number of default service actions that are triggered unless a custom action is specified on the **@Drest\Route** annotation.
The default action is determined on the HTTP verb used, and whether the matched route is a collection or element endpoint (ie user vs users). 

###Default Behaviours

All default behaviours operate directly on doctrine's entity manager. 
Pull requests \[GET\] have their data fetched using doctrine's array hydration for speed. This means that it's the persisted data that is exposed to your endpoint.
So any any tranformations / augmentations you may have written into lifecycle events on your entities will not be executed. If you need to manipulate the data from it's persisted state before sending to the user you may have to create your own custom service action.




Error handling..

####GET Element

- Create a doctrine query builder instance
- Register any expose definitions to the query builder, ensuring we only select the data that's needed
- Use any URL parameters as filters
- Attempt to fetch a single result
- Return the result set

#####Possible errors
- Unable to retrieve a result - returns HTTP status 404

#####Example matching annotation
{% highlight php %}
@Drest\Route(
   name="get_user",
   routePattern="/user/:id",
   verbs={"GET"}
)
{% endhighlight %}

####GET Collection

- Create a doctrine query builder instance
- Register any expose definitions to the query builder, ensuring we only select the data that's needed
- Register any available URL parameters as filters
- Attempt to fetch a collection of results
- Return the result set

#####Possible errors
- Unable to retrieve any results - returns HTTP status 404

#####Example matching annotation
{% highlight php %}
@Drest\Route(
   name="get_users",
   routePattern="/users",
   verbs={"GET"},
   collection=true
)
{% endhighlight %}

####POST Element

- Create a new instance of the entity (the one the matched route was annotated on)
- Run the registered handle method (passing in an array representation)
- Persist the new object
- Return the location of the new entity in both the response body and HTTP header 'Location' and set response status to 201.

#####Possible errors
- Unable to save element - returns HTTP status 500

#####Example matching annotation
{% highlight php %}
@Drest\Route(
    name="post_user", 
    routePattern="/user", 
    verbs={"POST"}
)
{% endhighlight %}

####PUT/PATCH Element

- Create a doctrine query builder instance
- Register any available URL parameters as filters
- Attempt to fetch a single result
- Run the registered handle method (passing in an array representation)
- Flush the entity manager to persist any changes
- Return a status 200 with result set containing location information

#####Possible errors
- Unable to fetch the element - returns HTTP status 404
- Unable to save element changes - returns HTTP status 500

#####Example matching annotation
{% highlight php %}
@Drest\Route(
    name="update_user", 
    routePattern="/user/:id", 
    verbs={"PUT", "PATCH"}
)
{% endhighlight %}

####DELETE Element

- Create a doctrine query builder instance
- Register any available URL parameters as filters
- Attempt to fetch a single result
- Register resulting object to be removed and flush the entity manager
- Return a status 200 with result set containing response: successfully deleted

#####Possible errors
- Unable to fetch the element - returns HTTP status 404
- Unable to execute the delete query - returns HTTP status 500

#####Example matching annotation
{% highlight php %}
@Drest\Route(
    name="delete_user", 
    routePattern="/user/:id", 
    verbs={"DELETE"}
)
{% endhighlight %}

####DELETE Collection

- Create a doctrine query builder instance to delete all types on that entity class name
- Register any available URL parameters as filters
- Execute the query
- Return a status 200 with result set containing response: successfully deleted

#####Possible errors
- Unable to execute the delete query - returns HTTP status 500

#####Example matching annotation
{% highlight php %}
@Drest\Route(
    name="delete_user", 
    routePattern="/users", 
    verbs={"DELETE"},
    collection=true
)
{% endhighlight %}

###Creating your own

You very easily build your own service actions by extending the **\Drest\Service\Action\AbstractAction** class. You must then implement the method **execute()**.             
There are a number of objects at your disposal. You can use the entity manger to start constructing queries, inspect the request, manipulate the response object and more.              
               
{% highlight php %}
class Custom extends \Drest\Service\Action\AbstractAction
{
    public function execute()
    {
        // Get the Doctrine Entity Manager (Doctrine\ORM\EntityManager)
        $this->service->getEntityManager();
        
        // Get Drest Manager (Drest\Manager)
        $this->service->getDrestManager();
        
        // Get the request object (Drest\Request)
        $this->service->getRequest();
        
        // Get the response object (Drest\Response)
        $this->service->getResponse();
        
        // Get the route that was matched (Drest\Mapping\RouteMetaData)
        $this->service->getMatchedRoute();
        
        // Get the representation type that's required - XML / JSON (Drest\Representation\AbstractRepresentation)
        $this->service->getRepresentation();           
        
        // .. execute my own logic, return a custom result set ..
         return ResultSet::create(array('name' => 'lee', 'email' => 'lee@somedomain.com'), 'user');
    }
}        
{% endhighlight %}       
               
Once you've created your class you simply need to register it on the **@Drest\Route** annotation by using the **action** parameter.
Drest will simply attempt to construct an instance of that class so it's important it's either included, or registered on an autoloader.
Even though you could directly manipulate the response object, if the execute method returns an object of type **\Drest\Query\ResultSet** then this is automatically written to the document body in requested representation. Any other return types are ignored.

Note that the **action** parameter should detail the full class name including any namespaces that are relevant.
               
{% highlight php %}      
 @Drest\Resource(
    routes={
        @Drest\Route(
            name="get_user",
            routePattern="/user/:id",
            verbs={"GET"},
            action="Action\Custom"
 )})
 {% endhighlight %} 
 
            
####Using requested expose settings
When creating a custom pull action \[GET\] you may still want to adhere to the expose filtering that you set up, or the client has requested.
On the matched route object will be a pre-determined expose array which you'll need to apply to your query builder instance. 

This can be done using the method **registerExpose($exposeArray, $queryBuilder, $doctrineORMMetaData)**.

{% highlight php %}  
$classMetaData = $this->getMatchedRoute()->getClassMetaData();
$elementName = $classMetaData->getEntityAlias();

$em = $this->getEntityManager();

$qb = $this->registerExpose(
    $this->getMatchedRoute()->getExpose(),
    $em->createQueryBuilder()->from($classMetaData->getClassName(), $elementName),
    $em->getClassMetadata($classMetaData->getClassName())
);  
 {% endhighlight %}

                                

####Triggering a handle
For PUT/PATCH/POST requests that have a handle function registered then you may want to trigger this from your service action. 
to so this simply pass in your entity into the **runHandle($entity)** method. If a handle isn't registered, nothing will be called.

{% highlight php %}  
// Either fetch $object from your entity manager [PUT/PATCH], or instantiate it [POST]  
$this->runHandle($object);   
 {% endhighlight %} 
                
                
                           