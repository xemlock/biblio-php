<?php

require_once 'BiblioPHP/Ris/Parser.php';

class BiblioPHP_Ris_ParserTest extends PHPUnit_Framework_TestCase
{
    public function testParseFromFile()
    {
        $parser = new BiblioPHP_Ris_Parser();
        $parser = $parser->setInputFile(test_asset('ams.ris'));
        $parser->next();
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
        $parser->setInputString($string);

        $entry = $parser->next();

        $this->assertEquals($entry['TY'], 'JOUR');
        $this->assertEquals($entry['SP'], '1128');
        $this->assertEquals($entry['UR'], 'http://dx.doi.org/10.1038/nm.2447');

    }

    public function testParseInvalidString()
    {
        $parser = new BiblioPHP_Ris_Parser();
        $parser->setInputString('This is an invalid input');

        $entries = array();
        while ($entry = $parser->next()) {
            $entries[] = $entry;
        }

        $this->assertEquals($entries, array());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testParseInvalidStream()
    {
        $parser = new BiblioPHP_Ris_Parser();
        $entry = $parser->setInputStream(false)->next();
    }

    /**
     * @expectedException Exception
     */
    public function testParseInvalidFile()
    {
        $parser = new BiblioPHP_Ris_Parser();
        $entry = $parser->setInputFile('?')->next();
    }

    public function testEndNoteFile()
    {
        $parser = new BiblioPHP_Ris_Parser();
        $entry = $parser->setInputFile(test_asset('ams.endnote.ris'))->next();

        $this->assertEquals($entry['TY'], 'JOUR');
        $this->assertEquals($entry['KW'], array(
            'Keyword1',
            'Keyword2',
            'Keyword3 with more than one word',
        ));
    }
}
