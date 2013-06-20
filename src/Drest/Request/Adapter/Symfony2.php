<?php

namespace Drest\Request\Adapter;

use Drest\DrestException;

class Symfony2 extends AdapterAbstract
{

    /**
     * @see \Drest\Request\Adapter\AdapterInterface::getAdpatedClassName()
     */
    public static function getAdaptedClassName()
    {
        return 'Symfony\Component\HttpFoundation\Request';
    }

    /**
     * @see \Drest\Request\Adapter.AdapterInterface::getHttpMethod()
     */
    public function getHttpMethod()
    {
        $const = 'METHOD_' . $this->getRequest()->getMethod();
        if (!defined('Drest\Request::' . $const)) {
            throw DrestException::unknownHttpVerb(get_class($this));
        }
        return constant('Drest\Request::' . $const);
    }

    /**
     * @see \Drest\Request\Adapter\AdapterInterface::getBody()
     */
    public function getBody()
    {
        return $this->getRequest()->getContent();
    }

    /**
     * @see \Drest\Request\Adapter\Request::getCookie()
     */
    public function getCookie($name = null)
    {
        if ($name === null) {
            return $this->getRequest()->cookies->all();
        }
        if ($this->getRequest()->cookies->has($name)) {
            return $this->getRequest()->cookies->get($name);
        }
        return '';
    }

    /**
     * @see \Drest\Request\Adapter\Request::getHeaders()
     */
    public function getHeaders($name = null)
    {
        if ($name === null) {
            return $this->getRequest()->headers->all();
        }
        if ($this->getRequest()->headers->has($name)) {
            return $this->getRequest()->headers->get($name);
        }
        return '';
    }


    /**
     * @see \Drest\Request\Adapter\Request::setPost()
     */
    public function setPost($name, $value = null)
    {
        if (is_array($name)) {
            $this->getRequest()->request->replace($name);
        } else {
            $this->getRequest()->request->set($name, $value);
        }
    }

    /**
     * @see \Drest\Request\Adapter\Request::getPost()
     */
    public function getPost($name = null)
    {
        if ($name === null) {
            return $this->getRequest()->request->all();
        }
        if ($this->getRequest()->request->has($name)) {
            return $this->getRequest()->request->get($name);
        }
        return '';
    }

    /**
     * @see \Drest\Request\Adapter\Request::getQuery()
     */
    public function getQuery($name = null)
    {
        if ($name === null) {
            return $this->getRequest()->query->all();
        }
        if ($this->getRequest()->query->has($name)) {
            return $this->getRequest()->query->get($name);
        }
        return '';
    }

    /**
     * @see \Drest\Request\Adapter\Request::setQuery()
     */
    public function setQuery($name, $value = null)
    {
        if (is_array($name)) {
            $this->getRequest()->query->replace($name);
        } else {
            $this->getRequest()->query->set($name, $value);
        }
    }

    /**
     * @see \Drest\Request\Adapter\AdapterInterface::getUri()
     */
    public function getUri()
    {
        return $this->getRequest()->getUri();
    }

    /**
     * Symfony 2 Request object
     * @return \Symfony\Component\HttpFoundation\Request $request
     */
    public function getRequest()
    {
        return $this->request;
    }
}