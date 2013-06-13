---
layout: post
title:  "Exposing Data"
date:   2013-06-13 13:32:55
categories: docs
---


By using the **expose** parameter on a **@Drest\Route** you can specify exactly what data you want your users to see (or send).

It must be the format of an array, and can even include your relational data (at any depth). 
You can declare relational data by simply including the variable name in your entity. Or, if you want to limit parameters of that relation, then the variable name should be used as the array key.

So in example below, say that expose definition was used on [GET] route for a User entity. 
Data displayed on request would only include;
- the user property *email_address*
- the relation *profile* with only the *last_name* property
- a further relation on profile of *addresses (which only displayed the *address* property)
- a user relation *phone_numbers* (which only displayed the *number* property)   

{% highlight php %}
// In annotation form:
expose={"email_address", "profile" : {"lastname", "addresses" : {"address"}}, "phone_numbers" : {"number"}}

// When viewed in array form:
array(
    'email_address',
    'profile' => array(
        'lastname',
        'addresses' => array(
            'address'
        )
    ),
    'phone_numbers' = array(
        'number'
    )
)
{% endhighlight %}

So the server might respond with something like this;

@todo: insert XML / JSON response using above expose

You can set a default expose depth in the Drest\Configuration, however an **expose** parameter used on a route will take precedence.


### Syntax




### For a PULL request
When data is pulled from the server via a GET call only data defined in the **expose** variable will be 



* mention cool user[profile[user[profile]]] exposure

### For a PUSH request


        - Setting an exposure depth
                * By default all data entity data is exposed for a relational depth of 2
        - Explicit exposure settings
            - annotation syntax
            
        - Clients' exposure requests
            * mention mobile etc
            - request syntax
                * Anything fields requested by the client that isn't exposed on the server are not served. Client's requested expose setting are always filtered against the server settings.
            -  exposure request options
                * head / params / get params / post params
        - Using doctrine's fetch type