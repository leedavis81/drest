<?php
namespace DrestTests\Functional;

use DrestTests\DrestFunctionalTestCase;

class ClassGeneratorTest extends DrestFunctionalTestCase
{

    public function testClassGeneration()
    {
        $dm = $this->_getDrestManager($this->_em);

        // Used 'X-DrestCG' http header
        $request = \Symfony\Component\HttpFoundation\Request::create(
            '/',
            'OPTIONS',
            [],
            [],
            [],
            array('HTTP_X_DRESTCG' => 'on')
        );

        $response = $dm->dispatch($request);

        $classes = unserialize($response->getBody(true));
        $classes = array_filter(
            $classes,
            function ($item) {
                return (get_class($item) == 'Zend\Code\Generator\ClassGenerator');
            }
        );

        $this->assertTrue((sizeof($classes) > 0));
        foreach ($classes as $class) {
            $this->assertInstanceOf('Zend\Code\Generator\ClassGenerator', $class);
        }
    }
}
