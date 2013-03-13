<?php

namespace Drest\Router;


interface RouteInterface
{


	public function matches(\Drest\Request\Adapter\AdapterInterface $request);
}