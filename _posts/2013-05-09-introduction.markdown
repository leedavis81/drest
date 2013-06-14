---
layout: post
title:  "Introduction"
date:   2013-05-09 13:32:55
categories: docs
---
Already using [Doctrine's Object Relational Mapper](http://www.doctrine-project.org/projects/orm.html)? Want to quickly and easily create a RESTful API? Drest could help you get it up and running in a matter of minutes.
With some really simple configurations you can turn any one of your entities into a functioning REST endpoint. 

### Why do I want to use it?

When building an API you'd be surprised at just how much common ground you'll cover over and over. If you want to create public exposure points for the data your already persisting with Doctrine then this tool will take away a lot of the repetitive boiler plate work.

It can be as simple as adding the following annotation to an Entity class to expose it as a GET'able REST endpoint.

{% highlight php %}
/* @Drest\Resource(
 *      routes={
 *          @Drest\Route(
 *              name="get_user",
 *              routePattern="/user/:id",
 *              verbs={"GET"}
 * )});
{% endhighlight %}

Drest comes with a number of [default behaviours]({{site.url}}/docs/service-actions) to handle each request. . 
These defaults will operate on your ORM entity manager to fetch / persist / update entities depending on the HTTP verb and configurations used.
However these behaviours are not set in stone, drest is extensible so you can easily create and inject your own custom behaviour.

Data can be represented in any number of ways, you get to control how your data is handled by enabling the [representations]({{site.url}}/docs/representations) you would like to use.
Drest currently comes shipped with JSON and XML representations, with the future objective of leveraging further "standards conforming" data types such as ([JSON/XML-HAL](http://stateless.co/hal_specification.html)).

Drest also comes with a handy client tool (wrapped around [guzzle](http://guzzlephp.org/)) that allows your users to operate solely on PHP data objects. 
Classes tailored to the data you want to expose (or allow for update) from your drest routes are generated via a CLI tool. 
Users can this operate directly on these, taking away the possibility of getting their XML or JSON syntax incorrect. 
They simply create a data object, and send it.  

{% highlight php %}
$user = SomeApi\Entities\User::create()
        ->setUsername('leedavis81');

$client->post('/user', $user);
{% endhighlight %}
They don't ever have to worry about building parsers for your data, the representations handle that for them.
{% highlight php %}
$response = $client->get('user/85');

// echo the Json or Xml response
echo $response->getRepresentation();

// get it all in a nice tidy array
$data = $response->getRepresentation()->toArray();
{% endhighlight %}

So what are you waiting for? Start [installing]({{site.url}}/docs/installation).