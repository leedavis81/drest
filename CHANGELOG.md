
Change log
-----------

A list of changes made between versions

# 1.0.0

- No longer allowed to inject the EntityManager directly. A Drest\EntityManagerRegistry must be used. A convenience method is allowed to quickly set up where only a single entity manager is needed.
- Service actions can now hook onto the entity manager registry where ->getEntityManager() simple returns the default.
- Service actions are no longer constructed by Drest and must be injected. They must implement AbstractAction and be registered on the service action registry object. See http://leedavis81.github.io/drest/docs/service-actions/#creating_your_own for more information
- Removed optional support for php 5.6
- Dropped support for php 5.3 (sorry, traits are really handy), which makes this a >= php 5.4 tool now
- Added support for HHVM
- Added support for php 7
- A large number of tidy up changes
- Pushed code coverage over 90%
- Pushed scrutinizer quality score above 8.0
- Removed injectRequest option, and always inject request object into a handle. 
- Add support for multiple drivers
- Add YAML, PHP and JSON configuration drivers