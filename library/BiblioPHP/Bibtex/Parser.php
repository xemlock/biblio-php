<?php

class BiblioPHP_Bibtex_Parser implements BiblioPHP_ParserInterface
{
    /**
     * @var BiblioPHP_Bibtex_Tokenizer
     */
    protected $_tokenizer;

    /**
     * @var array|false
     */
    protected $_current;

    /**
     * String macros.
     * @var string[]
     */
    protected $_strings;

    public function __construct()
    {
        $this->_tokenizer = new BiblioPHP_Bibtex_Tokenizer();
    }

    public function __destruct()
    {
        $this->_tokenizer->closeStream();
    }

    public function setInputStream($stream)
    {
        $this->_current = false;
        $this->_tokenizer->setStream($stream);
        return $this;
    }

    public function setInputFile($file)
    {
        $this->_current = false;
        $this->_tokenizer->setFile($file);
        return $this;
    }

    public function setInputString($string)
    {
        $this->_current = false;
        $this->_tokenizer->setString($string);
        return $this;
    }

    public function current()
    {
        return $this->_current;
    }

    protected function _peekToken()
    {
        return $this->_tokenizer->peekToken();
    }

    protected function _nextToken($type = null)
    {
        while (($token = $this->_tokenizer->nextToken()) !== false) {
            if ($type === null || in_array($token['type'], (array) $type)) {
                return $token;
            }
        }
        return false;
    }

    protected function _expectToken($type)
    {
        if (($token = $this->_peekToken())) {
            if ((is_array($type) && in_array($token['type'], $type, true))
                || ($token['type'] === $type)
            ) {
                return $token;
            }
        }
        return false;
    }

    protected function _getStringValue($string, $expand = false)
    {
        if ($expand && isset($this->_strings[$string])) {
            $string = $this->_strings[$string];
        }
        return $string;        
    }

    protected function _getString($expand = false)
    {
        $token = $this->_nextToken(array(
            BiblioPHP_Bibtex_Tokenizer::T_STRING,
            BiblioPHP_Bibtex_Tokenizer::T_QUOTED_STRING,
            BiblioPHP_Bibtex_Tokenizer::T_BRACED_STRING,
        ));
        if (!$token) {
            return false;
        }

        // perform expansion if string is not delimited FIXME recognize DELIMITED AND NOT DELIMITED STRING
        // only NOT DELIMITED STRINGS are subjects to string macro expansion
        $string = $this->_getStringValue($token['value'], $expand);

        do {
            $continue = false;
            if ($this->_expectToken(BiblioPHP_Bibtex_Tokenizer::T_CONCAT)) {
                $this->_nextToken(); // consume T_CONCAT
                if ($token = $this->_expectToken(array(
                        BiblioPHP_Bibtex_Tokenizer::T_STRING,
                        BiblioPHP_Bibtex_Tokenizer::T_QUOTED_STRING,
                        BiblioPHP_Bibtex_Tokenizer::T_BRACED_STRING,
                    ))
                ) {
                    $this->_nextToken(); // consume T_STRING
                    $string .= $this->_getStringValue($token['value'], $expand);
                    $continue = true; // ok, next loop
                }
            }
        } while ($continue);

        return $string;
    }

    public function next()
    {
        $token = $this->_nextToken(BiblioPHP_Bibtex_Tokenizer::T_TYPE);

        if (empty($token)) {
            return false;
        }

        $entryType = substr($token['value'], 1);

        // skip preamble
        if (strtolower($entryType) === 'preamble') {
            // @preamble { string [ # string ] }
            $this->_nextToken(BiblioPHP_Bibtex_Tokenizer::T_END); // consume and discard preamble
            return $this->next();
        }

        // register string macros
        if (strtolower($entryType) === 'string') {
            // list of key-value pairs, more than one!!!
            
            $key = $this->_getString();
            $value = $this->_getString(true);

            if (strlen($key) && strlen($value)) {
                $this->_strings[$key] = $value;
            }

            return $this->next();
        }

        // skip entry begin
        $this->_nextToken(BiblioPHP_Bibtex_Tokenizer::T_BEGIN);

        $entry = array(
            'entryType' => strtolower($entryType),
        );

        // to handle entries with empty reftype, peek at next token,
        // take it of the stream if it is a string
        if ($next = $this->_expectToken(BiblioPHP_Bibtex_Tokenizer::T_STRING)) {
            $entry['citeKey'] = $next['value'];
            $this->_nextToken(); // consume cite key
        }

        while ($token = $this->_peekToken()) {
            if ($token['type'] === BiblioPHP_Bibtex_Tokenizer::T_END) {
                $this->_nextToken();
                break;
            }

            if ($token['type'] === BiblioPHP_Bibtex_Tokenizer::T_COMMA) {
                // skip commas
                $this->_nextToken();
                continue;
            }

            // parse key-value pairs
            if (in_array($token['type'], array(
                    BiblioPHP_Bibtex_Tokenizer::T_STRING,
                    BiblioPHP_Bibtex_Tokenizer::T_QUOTED_STRING,
                    BiblioPHP_Bibtex_Tokenizer::T_BRACED_STRING,
                ))
            ) {
                $key = $this->_getString(); // consume string

                if (!$this->_expectToken(BiblioPHP_Bibtex_Tokenizer::T_SEPARATOR)) {
                    continue; // skip invalid token, will be handled in next loop
                }

                $this->_nextToken(); // consume separator

                if (!$this->_expectToken(array(
                        BiblioPHP_Bibtex_Tokenizer::T_STRING,
                        BiblioPHP_Bibtex_Tokenizer::T_QUOTED_STRING,
                        BiblioPHP_Bibtex_Tokenizer::T_BRACED_STRING,
                    ))
                ) {
                    continue;
                }

                $value = $this->_getString(true);
                $key = strtolower($key);

                switch ($key) {
                    case 'author':
                    case 'editor':
                        $value = $value;
                        break;

                    case 'pages':
                        $value = self::normalizePages($value);
                        break;

                    case 'year':
                    case 'day':
                        $value = intval($value);
                        break;

                    case 'month':
                        $value = self::normalizeMonth($value);
                        break;

                    default:
                        // spaces may be part of quoted/braced strings
                        $value = trim($value);
                        break;
                }

                if ($key === 'keywords') {
                    // some providers (ScienceDirect) put keywords in separate key=value pairs
                    $entry['keywords'][] = $value;
                } else {
                    $entry[$key] = $value;
                }

                continue;
            }

            // consume unhandled token
            $this->_nextToken();
        }

        return $this->_current = $entry;
    }

    /**
     * @return int
     */
    public static function normalizeMonth($month)
    {
        $month = str_replace('~', ' ', $month);

        // TODO handle all formats: mmm, dd mmm, mmm dd
        if (!ctype_digit($month)) {
            $months = array_flip(
                array(
                    0,
                    'jan', 'feb', 'mar', 'apr', 'may', 'jun',
                    'jul', 'aug', 'sep', 'oct', 'nov', 'dec'
                )
            );
            $m = substr(strtolower($month), 0, 3);
            if (isset($months[$m])) {
                $month = $months[$m];
            }
        }
        $month = intval($month);
        return 0 < $month && $month <= 12 ? $month : 0;
    }

    public static function normalizePages($pages)
    {
        // replace multiple dashes with a single one
        return preg_replace('/--+/', '-', $pages);
    }
}
