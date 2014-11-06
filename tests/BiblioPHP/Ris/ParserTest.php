<?php

require_once 'BiblioPHP/Ris/Parser.php';

class BiblioPHP_Ris_ParserTest extends PHPUnit_Framework_TestCase
{
    public function testParseFromFile()
    {
        $parser = new BiblioPHP_Ris_Parser();
        $entries = $parser->parseFile(test_asset('ams.ris'));
    }

    public function testParse()
    {
        $string =<<<EOS


TY  - JOUR
AU  - Sullivan, Nancy J
TI  - CD8+ cellular immunity mediates rAd5 vaccine protection against Ebola virus infection of nonhuman primates
JA  - Nat Med
PY  - 2011/09//print
VL  - 17
IS  - 9
SP  - 1128
EP  - 1131
SN  - 1078-8956
UR  - http://dx.doi.org/10.1038/nm.2447
ER  - 

EOS;
        $parser = new BiblioPHP_Ris_Parser();
        $entries = $parser->parse($string);

        $this->assertEquals($entries[0]['TY'], 'JOUR');
        $this->assertEquals($entries[0]['SP'], '1128');
        $this->assertEquals($entries[0]['UR'], 'http://dx.doi.org/10.1038/nm.2447');

    }

    public function testParseInvalidString()
    {
        $parser = new BiblioPHP_Ris_Parser();
        $entries = $parser->parse('This is an invalid input');

        $this->assertEquals($entries, array());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testParseInvalidStream()
    {
        $parser = new BiblioPHP_Ris_Parser();
        $parser->parseStream(false);
    }

    /**
     * @expectedException Exception
     */
    public function testParseInvalidFile()
    {
        $parser = new BiblioPHP_Ris_Parser();
        $parser->parseFile('?');
    }

    public function testEndNoteFile()
    {
        $parser = new BiblioPHP_Ris_Parser();
        $entries = $parser->parseFile(test_asset('ams.endnote.ris'));

        $this->assertEquals($entries[0]['TY'], 'JOUR');
        $this->assertEquals($entries[0]['KW'], array(
            'Keyword1',
            'Keyword2',
            'Keyword3 with more than one word',
        ));
    }
}
