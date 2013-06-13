---
layout: post
title:  "Service Actions"
date:   2013-06-13 13:32:55
categories: docs
---


        - What are they?
        - Default service object
            - GET
                - content type: Element / Collection
                - HTTP Response codes
            - POST
                - HTTP Response codes           
            - PUT
                - HTTP Response codes           
            - DELETE
                - HTTP Response codes
                
                
      The "default" behaviours of this tool sees communication directly with your data store using Doctrine's entity manager. Data is fetched using array hydration for speed and caching purposes. It's the persisted data that is exposed to your endpoint, meaning any tranformations or augmentations you may have written into your entities won't be executed. This isn't to say you can't use this tool. Service classes can be written with the sole responsibility of fetching data for a resource endpoint. So you could run these and return a Drest\ResultSet to be exposed. But be aware, if you have a lot of transformations, the work involved writing Service classes may outweight the practical use of this tool. This doesn't become a problem when using endpoints for creating / updating or deleting entities (only reading).                
                
                
                
                           
        - Creating your own
            - Objects / resources available
            - Suggested call methods
            - ResultSet