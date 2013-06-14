---
layout: post
title:  "Exposing Data"
date:   2013-06-13 13:32:55
categories: docs
---

By using the **expose** parameter on a **@Drest\Route** you can specify exactly what data you want your users to see (or send).


####when pulling data
An expose definition on a \[GET\] request will ensure only data within your definition will be retrievable by the API user.

####when pushing data
An expose definition on a \[PUT/POST/PATCH\] request will act as a filter. Any data sent by the client that isn't in the definition will be stripped from the data representation.

###Usage

You simply declare the name of the variables you wish to **expose** in the format of an array. You can even include an entities relational data (at any depth). 
Relational data is also declared by using the variable name(s) you've set up as a relation on your entities. If you want to limit parameters of that relation, then the variable name should be used as an array key.

So in example below, say that expose definition was used on \[GET\] route for a User entity. 
Data displayed on request would only include;
- the user property *email_address*
- the relation *profile* with only the *last_name* property
- a further relation on profile of *addresses (which only displayed the *address* property)
- a user relation *phone_numbers* (which only displayed the *number* property)   

{% highlight php %}
// In annotation form:
expose={"email_address", "profile" : {"lastname", "addresses" : {"id"}}, "phone_numbers" : {"number"}}

// When viewed in array form:
array(
    'email_address',
    'profile' => array(
        'lastname',
        'addresses' => array(
            'id'
        )
    ),
    'phone_numbers' = array(
        'number'
    )
)
{% endhighlight %}

So the server might respond with something like this;

{% highlight php %}
<user>
    <email_address>user@domain.com</email_address>
    <profile>
        <firstname>Lee</firstname>
        <lastname>Davis</lastname>
        <addresses>
            <address>
                <id>7654</id>
            </address>
        </addresses>        
    </profile>
    <phone_numbers>
        <phone_number>
            <id>56456</id>
            <number>07587288888</number>
        </phone_number>
    </phone_numbers>
</user>  
{% endhighlight %}

By default drest will allow an **expose** to all entity properties and it's relations for depth of *2* - meaning all data on that entities relations, but no further.
So using the example above that would include profile with the lastname property, but not the addresses. The default exposure depth can be configured on *Drest\Configuration::setExposureDepth($depth)*.
By default when handling bi-directional relationships drest will never step back to the parent (owning-side) of relation. This is in place to prevent recursion. This can however be overwridden using either an explicit expose configuration, or from the client's tailored request.
If an **expose** definition exists on a route configuration, it will always take precedence.

As well as specifying the default maximum relationship depth you want to expose, you can also state whether you wish to follow relationships of a certain type.
Doctrine relations are by default lazily loaded, however it's possible to force the loading of a relationship by [enabling an eager loading strategy](https://doctrine-orm.readthedocs.org/en/latest/reference/working-with-objects.html?highlight=eager#by-eager-loading). 
It's possible to leverage this configuration with drest. If for example you wanted a high exposure depth, but only wanted to load relations what were configured to be eagerly loaded, you could do the following:

{% highlight php %}
use Doctrine\ORM\Mapping\ClassMetadataInfo as ORMClassMetaDataInfo;

$drestConfig->setExposureRelationsFetchType(ORMClassMetaDataInfo::FETCH_EAGER);
{% endhighlight %}
Now any relation that was set as LAZY would be not be included when processing an exposure depth.

### Tailored for the client

It's often the case that some of your API users may only want to consume a small portion of the data your exposing. 
By exposing a large rigid data structure at a resource's endpoint it's possible you'll be forcing users to download data they don't need. 
This leads to unncessary bandwidth consumption, and possibly a less responsive application. A legitimate concern if your API is being used with mobile applications. 

To solve this problem it's possible to allow a user to specify the exact data they want from the available expose fields. 
The hard definitions will still apply, and a client cannot request any data outside of these. Anything requested by the client that isn't set to be exposed on the server **is not served**. A requested expose is always filtered against the server expose configuration.

To enable this feature use the **Drest\Configuration::setExposeRequestOptions(s)($setting)** method. For example:
{% highlight php %}
use Drest\Configuration;

// Use the value from a GET parameter called "expose"
$drestConfig->setExposeRequestOption(Configuration::EXPOSE_REQUEST_PARAM_GET, 'expose'); 

// Use the value from an HTTP header called X-Expose or from a parameter of the name "exp" 
$drestConfig->setExposeRequestOptions(array(
    Configuration::EXPOSE_REQUEST_PARAM_HEADER, 'X-Expose', 
    Configuration::EXPOSE_REQUEST_PARAM_PARAM, 'exp', 
)); 
{% endhighlight %}

Once that's done the user can then send a request to fetch the exact fields they require. 
They need to pass in a similar format to the expose definition, but rather than using the annotation syntax, they need to use one thats both URL and HTTP header friendly. 

####syntax

- The required expose should be sent as a string
- Properties must be seperated by using a pipe character ( | ).
- Nested relations should declared using open ( \[ ) and close ( \] ) square brackets.

Examples:


Assuming the first configuration above is enabled 

{% highlight php %}
[GET] http://yourapi.endpoint/user/123/?expose=email_address|profile[title]
{% endhighlight %}
{% highlight php %}
<user>
    <email_address>user@domain.com</email_address>
    <profile>
        <title>Mr</title>
    </profile>
</user>
{% endhighlight %}
{% highlight php %}
[GET] http://yourapi.endpoint/user/123/?expose=email_address|profile
{% endhighlight %}
{% highlight php %}
<user>
    <email_address>user@domain.com</email_address>
    <profile>
        <id>123</id>
        <title>Mr</title>
        <firstname>Lee</firstname>
        <lastname>Davis</lastname>
    </profile>
</user>  
{% endhighlight %}
{% highlight php %}
[GET] http://yourapi.endpoint/user/123/?expose=email_address|profile[title|firstname|addresses[id]]
{% endhighlight %}
{% highlight php %}
<user>
    <email_address>user@domain.com</email_address>
    <profile>
        <title>Mr</title>
        <firstname>Lee</firstname>
        <addresses>
            <address>
                <id>7654</id>
            </address>
        </addresses>
    </profile>
</user>
{% endhighlight %}

If you were to enable a particularly large exposure depth, and you had [bi-directional](http://docs.doctrine-project.org/en/2.0.x/reference/association-mapping.html) relations in your entities it would be possible to honour a request such as:

{% highlight php %}
[GET] http://yourapi.endpoint/user/123/?expose=profile[user[profile[user[profile]]]]

// Ghastly reponse appropriately hidden 
{% endhighlight %}

Not at all ideal, so ensure you always set a reasonable depth limit.

Check out this video for further examples of a tailored response:

{% youtube Ub4skw-xA2Q %}

(hint: you may want to full screen it, the text is quite small - sorry little mobiles)












