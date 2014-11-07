<?php

class BiblioPHP_Bibtex_Parser
{
    protected $_tokenizer;

    public function __construct()
    {
        $this->_tokenizer = new BiblioPHP_Bibtex_Tokenizer();
    }

    public function parseStream($stream)
    {
        $this->_tokenizer->setStream($stream);
        return $this->_parse();
    }

    public function parseFile($file)
    {
        $this->_tokenizer->setFile($file);
        return $this->_parse();
    }

    public function parse($string)
    {
        $this->_tokenizer->setString($string);
        return $this->_parse();
    }

    protected function _parse()
    {
        $entries = array();
        while (($entry = $this->_parseEntry()) !== false) {
            $entries[] = $entry;
        }
        return $entries;
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

    protected function _parseEntry()
    {
        $token = $this->_getToken(BiblioPHP_Bibtex_Tokenizer::T_TYPE);

        if (empty($token)) {
            return false;
        }

        $entryType = substr($token['value'], 1);

        $token = $this->_getToken(BiblioPHP_Bibtex_Tokenizer::T_STRING);

        if (empty($token)) {
            return false;
        }

        $entry = array(
            'type' => strtolower($entryType),
            'citeKey' => $token['value'],
        );

        $this->_getToken(BiblioPHP_Bibtex_Tokenizer::T_COMMA);

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

            while ($token = $this->_getToken()) {
                if ($token['type'] === BiblioPHP_Bibtex_Tokenizer::T_END) {
                    break(2);
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
                    $value = self::normalizeAuthors($value);
                    break;

                case 'pages':
                    $value = self::normalizePages($value);
                    break;

                case 'year':
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
        }

        return $entry;
    }

    public static function normalizeKeywords()
    {
        
    }

    /**
     * @return int
     */
    public static function normalizeMonth($month)
    {
        if (!ctype_digit($month)) {
            $months = array_flip(
                array(
                    0,
                    'jan', 'feb', 'mar', 'apr', 'may', 'jun',
                    'jul', 'aug', 'sep', 'oct', 'nov', 'dec'
                )
            );
            $m = substr(strtolower($value), 0, 3);
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

    public static function normalizeAuthors($authors)
    {
        // BibTeX allows three possible forms for the name:
        // "First von Last"
        // "von Last, First"
        // "von Last, Jr, First"

        $authors = array_map(
            'trim',
            preg_split('/\s+and\s+/i', $authors)
        );
        foreach ($authors as $index => $author) {
            $normalized = array(
                'firstName' => '',
                'lastName'  => '',
                'suffix'    => '',
            );
            $parts = preg_split('/\s*,\s*/', $author);
            if (count($parts) >= 3) {
                $normalized['lastName'] = $parts[0];
                $normalized['suffix'] = $parts[1];
                $normalized['firstName'] = $parts[2];
            } elseif (count($parts) === 2) {
                $normalized['lastName'] = $parts[0];
                $normalized['firstName'] = $parts[1];
            } else {
                $parts = preg_split('/\s+/', $author);
                // last name part is either a last token,
                // or starts with first lower case letter
                // BibTeX knows where one part ends and the other begins
                // because the tokens in the von part begin with lower-case letters.
                // 
                $boundary = count($parts) - 1;
                for ($i = 0; $i < count($parts); ++$i) {
                    if (ctype_lower(substr($parts[$i], 0, 1))) {
                        $boundary = $i;
                        break;
                    }
                }
                $normalized['firstName'] = implode(' ', array_slice($parts, 0, $boundary));
                $normalized['lastName'] = implode(' ', array_slice($parts, $boundary));
            }
            $authors[$index] = $normalized;
        }
        return $authors;
    }
}
