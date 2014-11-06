<?php

require_once 'Bibtex/Tokenizer.php';
require_once 'Bibtex/Parser.php';

class BiblioPHP_Bib_ParserTest extends PHPUnit_Framework_TestCase
{
    public function testParseString()
    {
        $parser = new BiblioPHP_Bibtex_Parser();

        $result = $parser->parse(
<<<EOS
@Article {
    test:2014,
    key1 = value1 # " @value2 " # {va{lue}3},

    key2 = {value4},
}
EOS
        );

        $this->assertEquals($result, array(
            array(
                'type'    => 'article',
                'citeKey' => 'test:2014',
                'key1'    => 'value1 @value2 va{lue}3',
                'key2'    => 'value4'
            )
        ));
    }

    public function testNormalizeAuthors()
    {
        $this->assertEquals(
            BiblioPHP_Bibtex_Parser::normalizeAuthors('Ludwig van Beethoven and Strauss, Jr., Johann'),
            array(
                array(
                    'firstName' => 'Ludwig',
                    'lastName'  => 'van Beethoven',
                    'suffix'    => '',
                ),
                array(
                    'firstName' => 'Johann',
                    'lastName'  => 'Strauss',
                    'suffix'    => 'Jr.',
                )
            )
        );
    }

    public function testParseFile()
    {
        $parser = new BiblioPHP_Bibtex_Parser();

        $string = '
@article{Ishii20131903,
title = "Cellular pattern formation in detonation propagation ",
journal = "Proceedings of the Combustion Institute ",
volume = "34",
number = "2",
pages = "1903 - 1911",
year = "2013",
note = "",
issn = "1540-7489",
doi = "http://dx.doi.org/10.1016/j.proci.2012.07.004",
url = "http://www.sciencedirect.com/science/article/pii/S1540748912002957",
author = "K. Ishii and K. Morita and Y. Okitsu and S. Sayama and H. Kataoka",
keywords = "Detonation",
keywords = "Cellular pattern",
keywords = "Smoke foil record",
keywords = "Particle entrainment",
keywords = "Adhesive force ",
abstract = "The relation between the soot track on smoked plate records and the frontal structure of gaseous detonations was experimentally studied to clarify the mechanism of cellular pattern formation by using combination images of high-speed schlieren pictures, self-emission images of the reaction front, and the smoked plate record. Several materials were tested as alternatives to soot particles of smoked foil technique to record detonation structure. The experimental results show that the triple point trajectory coincides with the soot track and that cellular cell-like patterns are obtained for CaCO3 particles, fly ash, heat-sensitive paper, and pressure-sensitive paper. An asymmetrical cellular pattern in the smoked plate record is exhibited in the case of the pressure-sensitive paper, while a symmetrical pattern is observed for the other materials. This asymmetry is successfully explained by the temporal response of the pressure-sensitive paper from evaluation of time integration of pressure, namely impulse to time varying loading. Estimation of wall shear stress and tensile strength of agglomerated particles layer on the basis of an analogy to particle entrainment from fine powder layers shows the critical particle diameter for removal of particles. However, the shear stress is found to be not strong enough for removal of particles located in the triple point trajectory. Finally other additional mechanisms for local detachment of particles are discussed. "
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
