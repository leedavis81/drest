Drest
=====

### Dress up doctrine entities and expose them as REST resources



| Quality / Metrics | Releases | Downloads | Dependencies |
| ----- | -------- | ------- | ------------- |
[![Build Status](https://scrutinizer-ci.com/g/leedavis81/drest/badges/build.png?b=master)](https://scrutinizer-ci.com/g/leedavis81/drest/build-status/master) [![Code Coverage](https://scrutinizer-ci.com/g/leedavis81/drest/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/leedavis81/drest/?branch=master) [![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/leedavis81/drest/badges/quality-score.png?s=54655af2afbd263417c9e80a4d6ee9664083b5c5)](https://scrutinizer-ci.com/g/leedavis81/drest/) | [![Latest Stable Version](https://poser.pugx.org/leedavis81/drest/v/stable.png)](https://packagist.org/packages/leedavis81/drest) [![Latest Unstable Version](https://poser.pugx.org/leedavis81/drest/v/unstable.png)](https://packagist.org/packages/leedavis81/drest) | [![Total Downloads](https://poser.pugx.org/leedavis81/drest/downloads.png)](https://packagist.org/packages/leedavis81/drest)| [![Dependency Status](https://www.versioneye.com/user/projects/5194ec66296d610002000343/badge.png)](https://www.versioneye.com/user/projects/5194ec66296d610002000343) |


[![Bitdeli Badge](https://d2weczhvl823v0.cloudfront.net/leedavis81/drest/trend.png)](https://bitdeli.com/free "Bitdeli Badge")

This library allows you to quickly annotate your doctrine entities into restful resources. It comes shipped with it's own internal router, and can be used standalone or alongside your existing framework stack. Routes are mapped to either a default or customised service action that takes care of handling requests.

Features:

- Quickly annotate existing Doctrine entities to become a fully functional REST resource.

- Utilises the internal router for matching resource route patterns.

- Specify what data you want to expose from your entities (including relations), or let the client choose!

- Generate data objects using exposable data for your API users to consume.

- Comes shipped with both JSON and XML representations, or you can create your own.

- Allows media type detection from your client requests, getting you one step close to RMM level 3.

- Use it independently from your existing framework stack, or alongside it.

- Allows extension points so you can configure requests to your specific needs.

Find out more by [viewing the documentation](http://leedavis81.github.io/drest/)
