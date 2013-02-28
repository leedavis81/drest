<?php
namespace Drest\Request\Adapter;

interface Request
{


	public function getParams();

	public function getPostParams();

	public function getGetParams();

	public function getHeader();
}