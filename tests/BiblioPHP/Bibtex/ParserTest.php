<?php

require_once 'BiblioPHP/Bibtex/Tokenizer.php';
require_once 'BiblioPHP/Bibtex/Parser.php';

class BiblioPHP_Bibtex_ParserTest extends PHPUnit_Framework_TestCase
{
    public function testParseString()
    {
        $parser = new BiblioPHP_Bibtex_Parser();
        $entries = $parser->parse('
            @Article {
                test:2014,
                key1 = value1 # " @value2 " # {va{lue}3},

                key2 = {value4},
            }
        ');

        $this->assertEquals($entries, array(
            array(
                'entryType' => 'article',
                'citeKey'   => 'test:2014',
                'key1'      => 'value1 @value2 va{lue}3',
                'key2'      => 'value4'
            )
        ));
    }

    public function testEmptyCiteKey()
    {
        $parser = new BiblioPHP_Bibtex_Parser();
        $entries = $parser->parse('
            @Article{
                ,
                author = {Ludwig van Beethoven and Strauss, Jr., Johann}
            }
        ');
        $this->assertEquals(
            $entries[0]['author'],
            'Ludwig van Beethoven and Strauss, Jr., Johann'
        );
    }

    public function testParseFile()
    {
        $parser = new BiblioPHP_Bibtex_Parser();

        $string = '
            @article{Ishii20131903,
                title = "Cellular pattern formation in detonation propagation ",
                keywords = "Detonation",
                keywords = "Cellular pattern",
                keywords = "Smoke foil record",
                keywords = "Particle entrainment",
                keywords = "Adhesive force ",
            }
        ';

        $result = $parser->parse($string);

        $this->assertEquals($result[0]['keywords'], array(
            'Detonation',
            'Cellular pattern',
            'Smoke foil record',
            'Particle entrainment',
            'Adhesive force',
        ));
    }
}
