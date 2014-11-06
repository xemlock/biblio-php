<?php

require_once 'Ris/Parser.php';
require_once 'Ris/Mapper.php';
require_once 'Ris/PubTypeMap.php';

require_once 'Publication.php';
require_once 'PublicationAuthor.php';
require_once 'PublicationType.php';

class BiblioPHP_Ris_MapperTest extends PHPUnit_Framework_TestCase
{
    public function testMapper()
    {
        $mapper = new BiblioPHP_Ris_Mapper();
        $parser = new BiblioPHP_Ris_Parser();

        $entries = $parser->parseFile(dirname(__FILE__) . '/../assets/ams.ris');
        $publication = $mapper->fromArray($entries[0]);

        print_r($publication->toArray());

        echo $mapper->toString($publication);
    }
}
