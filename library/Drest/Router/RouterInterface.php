<?php

namespace Drest\Router;

use Drest\Mapping\Annotation\Route;

interface RouterInterface
{

	public function getMatchedRoutes(Drest\Request\Adapter\AdapterInterface $request);

	public function generate($name, array $params, $absolute = false);

	/**
	 * Register a route into this router instance
	 */
	public function register(Route $route);

}