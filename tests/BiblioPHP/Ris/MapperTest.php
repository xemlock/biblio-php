<?php

require_once 'BiblioPHP/Ris/Parser.php';
require_once 'BiblioPHP/Ris/Mapper.php';
require_once 'BiblioPHP/Ris/PubTypeMap.php';

require_once 'BiblioPHP/Publication.php';
require_once 'BiblioPHP/PublicationAuthor.php';
require_once 'BiblioPHP/PublicationType.php';

class BiblioPHP_Ris_MapperTest extends PHPUnit_Framework_TestCase
{
    public function testMapper()
    {
        $mapper = new BiblioPHP_Ris_Mapper();
        $parser = new BiblioPHP_Ris_Parser();

        $entries = $parser->parseFile(dirname(__FILE__) . '/../../assets/ams.ris');
        $publication = $mapper->fromArray($entries[0]);
    }

    public function testPages()
    {
        $mapper = new BiblioPHP_Ris_Mapper();
        $publication = $mapper->fromArray(array(
            'TY' => 'GEN',
            'SP' => '5-5, 12-14,13-15, 11-11,  17 - 19, 20, 21',
        ));

        $this->assertEquals($publication->getPages(), array(
            5,
            '11-15',
            '17-21',
        ));

        $this->assertContains(
            "SP  - 5, 11-15, 17-21\r\nEP  - 21\r\n",
            $mapper->toString($publication)
        );

        $publication = $mapper->fromArray(array(
            'TY' => 'GEN',
            'SP' => '11, 12 - 21',
        ));

        $this->assertEquals($publication->getPages(), array(
            '11-21',
        ));

        $this->assertContains(
            "SP  - 11\r\nEP  - 21\r\n",
            $mapper->toString($publication)
        );
    }
}
