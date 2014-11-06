<?php

require_once 'BiblioPHP/Bibtex/Tokenizer.php';

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

    public function testTokenizeCommentEntry() // {{{
    {
        // only part from @Comment to the end of the line is expected to be
        // ignored, whole book entry must be tokenized
        $string = '
            @Comment{
                @Book{jansson:1946,
                    author = {Tove Jansson},
                    title = {Comet in Moominland},
                    year = 1946
                }
            }
        ';

        $tokenizer = new BiblioPHP_Bibtex_Tokenizer();
        $tokenizer->setString($string);

        $result = array();
        while ($token = $tokenizer->nextToken()) {
            $result[] = $token;
        }

        $this->assertEquals($result, array(
            array(
                'type' => BiblioPHP_Bibtex_Tokenizer::T_TYPE,
                'value' => '@Book',
                'line' => 3,
            ),
            array(
                'type' => BiblioPHP_Bibtex_Tokenizer::T_STRING,
                'value' => 'jansson:1946',
                'line' => 3,
            ),
            array(
                'type' => BiblioPHP_Bibtex_Tokenizer::T_COMMA,
                'value' => ',',
                'line' => 3,
            ),

            array(
                'type' => BiblioPHP_Bibtex_Tokenizer::T_STRING,
                'value' => 'author',
                'line' => 4,
            ),
            array(
                'type' => BiblioPHP_Bibtex_Tokenizer::T_SEPARATOR,
                'value' => '=',
                'line' => 4,
            ),
            array(
                'type' => BiblioPHP_Bibtex_Tokenizer::T_STRING,
                'value' => 'Tove Jansson',
                'line' => 4,
            ),
            array(
                'type' => BiblioPHP_Bibtex_Tokenizer::T_COMMA,
                'value' => ',',
                'line' => 4,
            ),

            array(
                'type' => BiblioPHP_Bibtex_Tokenizer::T_STRING,
                'value' => 'title',
                'line' => 5,
            ),
            array(
                'type' => BiblioPHP_Bibtex_Tokenizer::T_SEPARATOR,
                'value' => '=',
                'line' => 5,
            ),
            array(
                'type' => BiblioPHP_Bibtex_Tokenizer::T_STRING,
                'value' => 'Comet in Moominland',
                'line' => 5,
            ),
            array(
                'type' => BiblioPHP_Bibtex_Tokenizer::T_COMMA,
                'value' => ',',
                'line' => 5,
            ),

            array(
                'type' => BiblioPHP_Bibtex_Tokenizer::T_STRING,
                'value' => 'year',
                'line' => 6,
            ),
            array(
                'type' => BiblioPHP_Bibtex_Tokenizer::T_SEPARATOR,
                'value' => '=',
                'line' => 6,
            ),
            array(
                'type' => BiblioPHP_Bibtex_Tokenizer::T_STRING,
                'value' => '1946',
                'line' => 6,
            ),

            array(
                'type' => BiblioPHP_Bibtex_Tokenizer::T_END,
                'value' => '',
                'line' => 7,
            ),
        ));
    } // }}}
}
