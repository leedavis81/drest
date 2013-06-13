---
layout: post
title:  "Representations"
date:   2013-06-13 13:32:55
categories: docs
---


        - What are they?
            * required media types (xml / json)
        - Detecting requested content options
            DETECT_CONTENT_HEADER, DETECT_CONTENT_EXTENSION, DETECT_CONTENT_PARAM
            * detected writer injected into service class
        - Default writers
            * If no media type was detected, the first writer declared on either config or resource is used
        - Explicitly setting a writer per resource
        - Creating your own