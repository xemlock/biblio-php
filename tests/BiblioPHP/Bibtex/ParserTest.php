<?php

class BiblioPHP_Bibtex_ParserTest extends PHPUnit_Framework_TestCase
{
    public function testParseString()
    {
        $parser = new BiblioPHP_Bibtex_Parser();
        $parser->setInputString('
            @Article {
                test:2014,
                key1 = value1 # " @value2 " # {va{lue}3},

                key2 = {value4},
            }
        ');

        $entries = array();
        while ($entry = $parser->next()) {
            $entries[] = $entry;
        }

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
        $parser = $parser->setInputString('
            @Article{
                ,
                author = {Ludwig van Beethoven and Strauss, Jr., Johann}
            }
        ');

        $entries = array();
        while ($entry = $parser->next()) {
            $entries[] = $entry;
        }

        $this->assertEquals(count($entries), 1);
        $this->assertEquals(
            $entries[0]['author'],
            'Ludwig van Beethoven and Strauss, Jr., Johann'
        );
    }

    public function testSpacesInCiteKey()
    {
        $parser = new BiblioPHP_Bibtex_Parser();
        $parser = $parser->setInputString('
            @Book{
                inv:alid CiteKey,
                title = {Invalid cite key},
            }
        ');

        $entries = array();
        while ($entry = $parser->next()) {
            $entries[] = $entry;
        }

        $this->assertEquals(count($entries), 1);
        $this->assertEquals($entries[0], array(
            'entryType' => 'book',
            'citeKey'   => 'inv:alid',
            'title'     => 'Invalid cite key',
        ));
    }

    public function testParseFile()
    {
        $parser = new BiblioPHP_Bibtex_Parser();
        $parser->setInputString('
            @article{Ishii20131903,
                title = "Cellular pattern formation in detonation propagation ",
                keywords = "Detonation",
                keywords = "Cellular pattern",
                keywords = "Smoke foil record",
                keywords = "Particle entrainment",
                keywords = "Adhesive force ",
            }
        ');

        $result = $parser->next();

        $this->assertInternalType('array', $result);
        $this->assertEquals($result['keywords'], array(
            'Detonation',
            'Cellular pattern',
            'Smoke foil record',
            'Particle entrainment',
            'Adhesive force',
        ));
    }

    public function testParseWithStringEntry()
    {
        $input = '
            @STRING{TU_Eindhoven = {Technische Universiteit Eindhoven}}

            @PHDTHESIS{Aarts_1993_PhD,
              author = {Ronald Aarts},
              title = {A numerical study of quantized vortices in He II},
              school = TU_Eindhoven,
              year = {1993}
            }
        ';

        $parser = new BiblioPHP_Bibtex_Parser();
        $entry = $parser->setInputString($input)->next();

        $this->assertEquals($entry['entryType'], 'phdthesis');
        $this->assertEquals($entry['author'], 'Ronald Aarts');
        $this->assertEquals($entry['school'], 'Technische Universiteit Eindhoven');
    }
}
