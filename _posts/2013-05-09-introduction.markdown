---
layout: post
title:  "Introduction"
date:   2013-05-09 13:32:55
categories: docs
---

### What it does


### Why do I want to use it?

When building an API you'd be surprised at just how much common ground you'll cover over and over. If you want to create public exposure points for the data your already persisting with Doctrine then this tool will take away a lot of the repetitive boiler plate work.

It can be as simple as adding the following annotation to an Entity class to expose it as a GET'able REST endpoint.

{% highlight php %}
/* @Drest\Resource(
 *      routes={
 *          @Drest\Route(
 *              name="get_user",
 *              route_pattern="/user/:id",
 *              verbs={"GET"},
 *              content="element"
 * )});
{% endhighlight %}

Although you could argue that a REST endpoints should not be intertwined with entities, it's very commonplace that this is the case. a User or Status entity very often become exposed via a /user or /status route.