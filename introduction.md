---
title: Introduction
---

### Introducton 

## Features

1. Drest is a tool that allows you to annotate Doctrine ORM Entities into a fully functional REST resources.
2. Quickly annotate existing Doctrine entities to become fully functional REST resources.
3. Utilises it's own internal router for matching resource route patterns to client requests.
4. Specify what data you want to expose from your entities (including relations), or let the client choose!
5. Comes with JSON, XML response writers with the ability easily inject your own.
6. Allows media type detection from client request / header information, getting you one step close to level 3 RMM.
7. It is structured to be used either independently from a framework, or alongside your existing framework with the use of adapters.
8. Allows extension points, so you can take advantage of common REST behaviours or configure to your needs.

## Why do I want to use it?

When building an API you'd be surprised at just how much common ground you'll cover over and over. If you want to create public exposure points for the data your already persisting with Doctrine then this tool will take away a lot of the repetitive boiler plate work.

It can be as simple as adding the following annotation to an Entity class to expose it as a GET'able REST endpoint.

```
/* @Drest\Resource(
 *      routes={
 *          @Drest\Route(
 *              name="get_user",
 *              route_pattern="/user/:id",
 *              verbs={"GET"},
 *              content="element"
 * )});
 ```

Although you could argue that a REST endpoints should not be intertwined with entities, it's very commonplace that this is the case. a User or Status entity very often become exposed via a /user or /status route.