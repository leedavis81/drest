<?php

namespace Drest\Router;


interface RouterInterface
{

	public function generate($name, array $params, $absolute = false);
}