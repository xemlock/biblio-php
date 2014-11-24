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

    public function next()
    {
        $token = $this->_getToken(BiblioPHP_Bibtex_Tokenizer::T_TYPE);

        if (empty($token)) {
            return false;
        }

        $entryType = substr($token['value'], 1);

        // handle entries with empty reftype

        $token = $this->_getToken();

        if (empty($token)) {
            return false;
        }

        // expect 
        if ($token['type'] === BiblioPHP_Bibtex_Tokenizer::T_STRING) {
            $citeKey = $token['value'];
        } else {
            $citeKey = null;
        }

        $entry = array(
            'entryType' => strtolower($entryType),
            'citeKey' => $citeKey,
        );

        // consume comma, if not already consumed
        if ($token['type'] !== BiblioPHP_Bibtex_Tokenizer::T_COMMA) {
            $this->_getToken(BiblioPHP_Bibtex_Tokenizer::T_COMMA);
        }

        while ($token = $this->_getToken()) {
            if ($token['type'] === BiblioPHP_Bibtex_Tokenizer::T_END) {
                break;
            }

            $keyToken = $token;

            if (!$this->_getToken(BiblioPHP_Bibtex_Tokenizer::T_SEPARATOR)) {
                break;
            }

            $valueToken = $this->_getToken(BiblioPHP_Bibtex_Tokenizer::T_STRING);
            $value = $valueToken['value'];

            $end = false;

            while ($token = $this->_getToken()) {
                if ($token['type'] === BiblioPHP_Bibtex_Tokenizer::T_END) {
                    // ok, after string value record ends, process that string
                    // and finish processing of this entry
                    $end = true;
                    break;
                }

                if ($token['type'] === BiblioPHP_Bibtex_Tokenizer::T_CONCAT) {
                    $token = $this->_getToken(BiblioPHP_Bibtex_Tokenizer::T_STRING);
                    $value .= $token['value'];
                }

                if ($token['type'] === BiblioPHP_Bibtex_Tokenizer::T_COMMA) {
                    break;
                }
            }

            $key = strtolower($keyToken['value']);

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

            if ($end) {
                break;
            }
        }

        return $this->_current = $entry;
    }

    protected function _getToken($type = null)
    {
        while (($token = $this->_tokenizer->nextToken()) !== false) {
            if ($type === null || $token['type'] === $type) {
                return $token;
            }
        }
        return false;
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
