<?php

class BiblioPHP_NameParserTest extends PHPUnit_Framework_TestCase
{
    public function getNameParser()
    {
        return new BiblioPHP_NameParser();
    }
    
    public function testWithMixedFormAndSeparators()
    {
        $this->assertEquals(
            array(
                array(
                    'first' => 'Juan A.',
                    'last'  => 'Navarro Perez',
                ),
                array(
                    'first' => 'Raul',
                    'last'  => 'de la Garza',
                ),
                array(
                    'first' => 'Andres',
                    'last'  => 'Espinoza',
                ),
            ),
            $this->getNameParser()->parse(
                'Juan A. Navarro Perez and de la Garza, Raul and Espinoza, Andres'
            )
        );
    }
}
