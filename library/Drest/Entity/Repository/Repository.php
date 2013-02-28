<?php


namespace Drest\Entity\Repository;

interface Repository
{

	public function getItem();

	public function getCollection();

	public function putItem();

	public function putCollection();

	public function deleteItem();

	public function deleteCollection();

	public function postItem();

	public function postCollection();

}