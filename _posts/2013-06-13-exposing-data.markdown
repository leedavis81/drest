---
layout: post
title:  "Exposing Data"
date:   2013-06-13 13:32:55
categories: docs
---


        - Setting an exposure depth
                * By default all data entity data is exposed for a relational depth of 2
        - Explicit exposure settings
            - annotation syntax
            * mention cool user[profile[user[profile]]] exposure
        - Clients' exposure requests
            * mention mobile etc
            - request syntax
                * Anything fields requested by the client that isn't exposed on the server are not served. Client's requested expose setting are always filtered against the server settings.
            -  exposure request options
                * head / params / get params / post params
        - Using doctrine's fetch type