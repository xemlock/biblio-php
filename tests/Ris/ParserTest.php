<?php

require 'Ris/Parser.php';

class BiblioPHP_Ris_ParserTest extends PHPUnit_Framework_TestCase
{
    public function testParseFromFile()
    {
        $parser = new BiblioPHP_Ris_Parser();
        $entries = $parser->parseFile(dirname(__FILE__) . '/test.ris');
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

        $this->assertTrue($entries[0]['TY'] === 'JOUR');
        $this->assertTrue($entries[0]['SP'] === '1128');
        $this->assertTrue($entries[0]['UR'] === 'http://dx.doi.org/10.1038/nm.2447');

    }

    public function testParseInvalidString()
    {
        $parser = new BiblioPHP_Ris_Parser();
        $entries = $parser->parse('This is an invalid input');

        $this->assertTrue($entries === array());
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
}
