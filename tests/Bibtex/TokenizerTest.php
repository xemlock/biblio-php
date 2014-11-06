<?php

require_once 'Bibtex/Tokenizer.php';

class BiblioPHP_Bibtex_TokenizerTest extends PHPUnit_Framework_TestCase
{
    public function testTokenizeString() // {{{
    {
        $tokenizer = new BiblioPHP_Bibtex_Tokenizer();
        $tokenizer->setString(
<<<EOS
@Article {
    article:2014,
    key1 = value1 # " @value2 " # {va{lue}3},

    key2= {value4},
}
EOS
    );
        $tokens = array();
        while ($token = $tokenizer->nextToken()) {
            $tokens[] = $token;
        }
        $this->assertEquals($tokens, array(
            array(
                'type' => BiblioPHP_Bibtex_Tokenizer::T_TYPE,
                'value' => '@Article',
                'line' => 1,
            ),
            array(
                'type' => BiblioPHP_Bibtex_Tokenizer::T_STRING,
                'value' => 'article:2014',
                'line' => 2,
            ),
            array(
                'type' => BiblioPHP_Bibtex_Tokenizer::T_COMMA,
                'value' => ',',
                'line' => 2,
            ),
            array(
                'type' => BiblioPHP_Bibtex_Tokenizer::T_STRING,
                'value' => 'key1',
                'line' => 3,
            ),
            array(
                'type' => BiblioPHP_Bibtex_Tokenizer::T_SEPARATOR,
                'value' => '=',
                'line' => 3,
            ),
            array(
                'type' => BiblioPHP_Bibtex_Tokenizer::T_STRING,
                'value' => 'value1',
                'line' => 3,
            ),
            array(
                'type' => BiblioPHP_Bibtex_Tokenizer::T_CONCAT,
                'value' => '#',
                'line' => 3,
            ),
            array(
                'type' => BiblioPHP_Bibtex_Tokenizer::T_STRING,
                'value' => ' @value2 ',
                'line' => 3,
            ),
            array(
                'type' => BiblioPHP_Bibtex_Tokenizer::T_CONCAT,
                'value' => '#',
                'line' => 3,
            ),
            array(
                'type' => BiblioPHP_Bibtex_Tokenizer::T_STRING,
                'value' => 'va{lue}3',
                'line' => 3,
            ),
            array(
                'type' => BiblioPHP_Bibtex_Tokenizer::T_COMMA,
                'value' => ',',
                'line' => 3,
            ),
            array(
                'type' => BiblioPHP_Bibtex_Tokenizer::T_STRING,
                'value' => 'key2',
                'line' => 5,
            ),
            array(
                'type' => BiblioPHP_Bibtex_Tokenizer::T_SEPARATOR,
                'value' => '=',
                'line' => 5,
            ),
            array(
                'type' => BiblioPHP_Bibtex_Tokenizer::T_STRING,
                'value' => 'value4',
                'line' => 5,
            ),
            array(
                'type' => BiblioPHP_Bibtex_Tokenizer::T_COMMA,
                'value' => ',',
                'line' => 5,
            ),
            array(
                'type' => BiblioPHP_Bibtex_Tokenizer::T_END,
                'value' => '',
                'line' => 6,
            ),
        ));
    } // }}}
}
