<?php

class BiblioPHP_Ris_MapperTest extends PHPUnit_Framework_TestCase
{
    public function testMapper()
    {
        $parser = new BiblioPHP_Ris_Parser();
        $mapper = new BiblioPHP_Ris_Mapper();

        $parser->setInputFile(test_asset('ams.ris'));

        $entries = array();
        while ($entry = $parser->next()) {
            $entries[] = $entry;
        }

        $publication = $mapper->fromArray($entries[0]);

        $this->assertEquals($publication->getPubType(), BiblioPHP_PublicationType::ARTICLE);
        $this->assertEquals($publication->getDoi(), '10.1175/JAS-D-12-0295.1');
        $this->assertEquals($publication->getYear(), 2013);
    }

    public function testDoi()
    {
        $mapper = new BiblioPHP_Ris_Mapper();

        $publication = $mapper->fromArray(array(
            'TY' => 'JOUR',
            'DO' => '10.1103/PhysRevLett.112.124301',
        ));
        $this->assertEquals('10.1103/PhysRevLett.112.124301', $publication->getDoi());

        $publication = $mapper->fromArray(array(
            'TY' => 'JOUR',
            'DO' => 'http://dx.doi.org/10.1103/PhysRevLett.112.124301',
        ));
        $this->assertEquals('10.1103/PhysRevLett.112.124301', $publication->getDoi());
    }

    public function testNoAuthors()
    {
        $mapper = new BiblioPHP_Ris_Mapper();

        $publication = $mapper->fromArray(array(
            'TY' => 'JOUR',
        ));
        $this->assertEquals(array(), $publication->getAuthors());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Author last name cannot be empty
     */
    public function testEmptyAuthors()
    {
        $mapper = new BiblioPHP_Ris_Mapper();
        $mapper->fromArray(array(
            'TY' => 'JOUR',
            'A1' => '',
        ));
    }


    public function testPages()
    {
        $mapper = new BiblioPHP_Ris_Mapper();
        $publication = $mapper->fromArray(array(
            'TY' => 'GEN',
            'SP' => '5-5, 12-14,13-15, 11-11,  17 - 19, 20, 21',
        ));

        $this->assertEquals($publication->getPages()->getPageRanges(), array(
            5  => 5,
            11 => 15,
            17 => 21,
        ));

        $publication = $mapper->fromArray(array(
            'TY' => 'GEN',
            'SP' => '11, 12 - 21',
        ));

        $this->assertEquals($publication->getPages()->getPageRanges(), array(
            11 => 21,
        ));
    }
}
