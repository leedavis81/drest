---
layout: post
title:  "Representations"
date:   2013-06-13 13:32:55
categories: docs
---

> "REST components perform actions on a resource by using a representation to capture the current or intended state of that resource and transferring that representation between components" - Roy Thomas Fielding

Whenever a user interacts with your API the resources made available need to be operable in a consistent data format. This is so both the server and the client can interpret either the state, or intended state of that resource.
This is where representations come in. They're a way of describing a resource in a commonly understood format. There are a number of representation types typically used over RESTful API's such as CSV, JSON, XML or even plain text.

Drest has both XML and JSON representation classes for use, with a future goal to add additional (more descriptive) representation types such as ([JSON/XML-HAL](http://stateless.co/hal_specification.html)).  

###Enabling a representation

When you enable a drest representation for your API, your allowing the server and client to be able to communicate in that specific representation. This applies to pull \[GET\], push \[POST/PUT/PATCH\] and possibly even operational requests \[DELETE, OPTIONS, HEAD\].
 
There are two ways to enable representation types. Either via the default representation setting in your **Drest\Configuration** instance;
{% highlight php %}
// Only allow JSON communication (by default) across your API
$drestConfig->setDefaultRepresentations(array('Json'));
{% endhighlight %}

or directly via the **@Drest\Resource** annotation: 

{% highlight php %}
 @Drest\Resource(
    representations={"Json", "Xml"},
    routes={
        ....
    }
)
{% endhighlight %}
<div class="alert alert-info">
Anything set on the resource annotation will override the default configuration.
</div>

Once this has been configured your server is ready to start communicating in that representation. If you were to enable the JSON representation on a resource with a POST route, your server may accept requests such as: 
{% highlight php %}
POST /users HTTP/1.1
Content-Type: application/json

{
  "user": {
    "username": "leedavis81",
    "email_address": "hello@somewhere.com",
    "profile": {
      "title": "mr",
      "firstname": "lee",
      "lastname": "davis"
    },
    "phone_numbers": [
      {
        "number": "02087856589"
      }
    ]
  }
}
{% endhighlight %}

<div class="alert alert-info">
If your API users are using the drest client tools they'll never have to manually build these requests. The representation classes will take care of that for them. See the <a href="{{site.url}}/docs/client-tool">client tool</a> section for more information.
</div>


###Content type detection
Each representation class will have an encapsulated "Content Type" string (which can be fetched using the getContentType() method). In the example of the JSON representation this would be "application/json". This is in place so both the server and client know what representation type has been used on a request / response. 

If you allow interaction over multiple representation types then drest will (as a default behavior) scan the HTTP headers ("Content-Type" for push requests, "Accept" for pull requests) to determine which the client wishes to interact over.
Or if no representation can be determined from the HTTP headers then drest will use the first of the declared representations in either the default or annotation configurations. 

<div class="alert">
If <strong>Drest\Config::set415ForNoMediaMatch();</strong> has been set to true then any [GET] requests where a representation can't be determined will produce an <strong>UnableToMatchRepresentationException</strong>, which when using the default error handler will return a 415 HTTP status. Find out more in the <a href="{{site.url}}/docs/advanced-configuration">advanced configuration section</a>
</div>

You can override the way drest detects the representation type by using a number of available configuration options. Any number configuration options can be combined together.


{% highlight php %}
// Only use the Accept / Content-Type header to determine the representation (default behavior)
$drestConfig->setDetectContentOptions(array(
    Configuration::DETECT_PULL_CONTENT_HEADER => 'Accept',
    Configuration::DETECT_PUSH_CONTENT_HEADER => 'Content-Type',
));

// Only use extension detections. For example /users.json or /user/123.xml
$drestConfig->setDetectContentOptions(array(
    // extension is matched against getMatchableExtensions() on representation classes 
    Configuration::DETECT_CONTENT_EXTENSION => true
));

// Only use parameter 'format' for representation detection. E.g [GET] /user/1?format=xml
$drestConfig->setDetectContentOptions(array(
    // extension is matched against getMatchableFormatParams() on representation classes 
    Configuration::DETECT_CONTENT_PARAM => 'format'
));
{% endhighlight %}

<div class="alert alert-info">These configurations only apply to pulling data [GET]. Content detection for push requests [POST/PATCH/PUT] will always use the "Content-Type" http header. If using the drest client tool this is automatically set.</div>

Once a representation type has been determined it is set on a **Drest\Service** object and can be accessed from your own custom service action classes by using the following method:
{% highlight php %}
$this->service->getRepresentation();   
{% endhighlight %}

See [creating your own]({{site.url}}/docs/service-actions/#creating_your_own) service actions for more information.

###Usage

If using the default service actions you should never have to directly interact with the representation classes as drest will take care of creating them for you. 
However you may find the need arises if your using either the [client tool]({{site.url}}/docs/client-tool), or your own [custom service actions]({{site.url}}/docs/service-actions/#creating_your_own).

Representation objects are fully interchangeable between two states. Their representation (xml/json string) and an array. Because of this they can created in either of the two ways.

{% highlight php %}
$jsonString = '{
  "user": {
    "username": "leedavis81",
    "email_address": "lee.davis@somewhere.com",
    "profile": {
      "id": "1",
      "title": "mr",
      "firstname": "lee",
      "lastname": "davis"
    },
    "phone_numbers": [
      {
        "id": "1",
        "number": "2087856458"
      }
    ]
  }
}';

$representation = Drest\Representation\Json::createFromString($jsonString);
$arrayRepresentation = $representation->toArray();
{% endhighlight %}

The $arrayRepresentation variable will contain the following:
{% highlight php %}
array('user' => array(
    'username' => 'leedavis81',
    'email_address' => 'lee.davis@somewhere.com',
    'profile' => array(
        'id' => '1',
        'title' => 'mr',
        'firstname' => 'lee',
        'lastname' => 'davis',
    ),
    'phone_numbers' => array(
        array(
            'id' => '1',
            'number' => '2087856458'
        )
    )
));
{% endhighlight %}

This could then be used to create a completely different representation:

{% highlight php %}
$xmlRepresentation = new Drest\Representation\Xml();
echo $xmlRepresentation->output(Drest\Query\ResultSet::create($arrayRepresentation['user'], 'user'));
{% endhighlight %}

will output:
{% highlight php %}
<?xml version="1.0" encoding="UTF-8"?>
<user>
  <username>leedavis81</username>
  <email_address>lee.davis@somewhere.com</email_address>
  <profile>
    <id>1</id>
    <title>mr</title>
    <firstname>lee</firstname>
    <lastname>davis</lastname>
  </profile>
  <phone_numbers>
    <phone_number>
      <id>1</id>
      <number>2087856458</number>
    </phone_number>
  </phone_numbers>
</user>
{% endhighlight %}






