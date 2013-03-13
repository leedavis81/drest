<?php

namespace Drest\Mapping\Annotation;


/**
 * @Annotation
 * @Target({"ANNOTATION"})
 */
final class Route implements Annotation
{
    /** @var string */
    public $name;
    /** @var string */
    public $pattern;
    /** @var string */
    public $repositoryMethod;
    /** @var array */
    public $verbs;



    /**
     * Check if this route matches the request passed
     *
     * Parse this route's pattern, and then compare it to an HTTP resource URI
     * This method was modeled after the techniques demonstrated by Dan Sosedoff at:
     *
     * http://blog.sosedoff.com/2009/09/20/rails-like-php-url-router/
     *
     * @param  string $resourceUri A Request URI
     * @return bool
     */
    public function matches(\Drest\Request\Adapter\AdapterInterface $request)
    {

		if ($this->usesHttpVerbs())
		{
			// make sure the verb used matches
			$request->getPost()
		}

        //Convert URL params into regex patterns, construct a regex for this route, init params
        $patternAsRegex = preg_replace_callback('#:([\w]+)\+?#', array($this, 'matchesCallback'),
            str_replace(')', ')?', (string) $this->pattern));
        if (substr($this->pattern, -1) === '/') {
            $patternAsRegex .= '?';
        }

        //Cache URL params' names and values if this route matches the current HTTP request
        if (!preg_match('#^' . $patternAsRegex . '$#', $resourceUri, $paramValues)) {
            return false;
        }
        foreach ($this->paramNames as $name) {
            if (isset($paramValues[$name])) {
                if (isset($this->paramNamesPath[ $name ])) {
                    $this->params[$name] = explode('/', urldecode($paramValues[$name]));
                } else {
                    $this->params[$name] = urldecode($paramValues[$name]);
                }
            }
        }

        return true;
    }

    /**
     * Is this route specific to defined HTTP verbs
     */
    public function usesHttpVerbs()
    {
		return empty($this->verbs);
    }
}
