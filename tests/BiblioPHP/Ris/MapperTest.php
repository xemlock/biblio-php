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
        $parser = new BiblioPHP_Ris_Parser();
        $mapper = new BiblioPHP_Ris_Mapper();

        $entries = $parser->parseFile(test_asset('ams.ris'));
        $publication = $mapper->fromArray($entries[0]);

        $this->assertEquals($publication->getPubType(), BiblioPHP_PublicationType::ARTICLE);
        $this->assertEquals($publication->getDoi(), '10.1175/JAS-D-12-0295.1');
        $this->assertEquals($publication->getYear(), 2013);
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

        $publication = $mapper->fromArray(array(
            'TY' => 'GEN',
            'SP' => '11, 12 - 21',
        ));

        $this->assertEquals($publication->getPages(), array(
            '11-21',
        ));
    }
}
