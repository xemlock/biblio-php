<?php

class BiblioPHP_Publication
{
    /**
     * One of BiblioPHP_PublicationType constants
     * @var string
     */
    protected $_pubType;

    protected $_citeKey;

    protected $_title;

    /**
     * Journal title, book title, or conference title in case of
     * conference paper
     * @var string
     */
    protected $_journal;

    /**
     * Series title
     */
    protected $_series;

    /**
     * @var array
     */
    protected $_authors;

    protected $_editors;

    protected $_translators;

    protected $_doi;

    protected $_year;

    protected $_month;

    protected $_day;

    protected $_place;

    protected $_language;

    /**
     * @var array
     */
    protected $_pages;

    protected $_volume;

    protected $_issue;

    protected $_publisher;

    protected $_serialNumber;

    protected $_url;

    protected $_abstract;

    protected $_keywords;

    protected $_notes;

    public function __construct(array $data = null)
    {
        if ($data) {
            $this->setFromArray($data);
        }
    }

    public function setDoi($doi)
    {
        $doi = trim($doi);

        // strip off doi: prefix
        if (strncasecmp('doi:', $doi, 4) === 0) {
            $doi = ltrim(substr($doi, 4));
        }

        // extract DOI from URL
        if (preg_match('/^http(s)?:\/\/.+\/(?P<doi>10\..+)/i', $doi, $match)) {
            $doi = $match['doi'];
        }

        // Perform simple DOI format validation:
        // - identifier must begin with 10.
        // - must have a non-empty suffix separated by a forward slash
        if ((substr($doi, 0, 3) === '10.') &&
            (($pos = strpos($doi, '/')) !== false) &&
            ($pos < strlen($doi) - 2)
        ) {
            $this->_doi = $doi;
        }

        return $this;
    }

    public function addKeyword($keyword)
    {
        $keyword = trim($keyword);
        if (strlen($keyword)) {
            $this->_keywords[] = $keyword;
        }
        return $this;
    }

    public function setKeywords(array $keywords)
    {
        $this->_keywords = array();
        array_map(array($this, 'addKeyword'), $keywords);
        return $this;
    }

    public function getKeywords()
    {
        return (array) $this->_keywords;
    }

    /**
     * @param  BiblioPHP_PublicationAuthor $author
     * @return BiblioPHP_Publication
     */
    public function addAuthor($author)
    {
        $author = self::normalizeAuthor($author);
        if ($author !== false) {
            $this->_authors[] = $author;
        }
        return $this;
    }

    public function getAuthors()
    {
        return (array) $this->_authors;
    }

    public function setAuthors(array $authors)
    {
        $this->_authors = null;
        array_map(array($this, 'addAuthor'), $authors);
        return $this;
    }

    public function addEditor($author)
    {
        $author = self::normalizeAuthor($author);
        if ($author !== false) {
            $this->_editors[] = $author;
        }
        return $this;
    }

    public function getEditors()
    {
        return (array) $this->_editors;
    }

    public function setEditors(array $authors)
    {
        $this->_authors = null;
        array_map(array($this, 'addEditor'), $authors);
        return $this;
    }

    public function addTranslator($author)
    {
        $author = self::normalizeAuthor($author);
        if ($author !== false) {
            $this->_translators[] = $author;
        }
        return $this;
    }

    public function getTranslators()
    {
        return (array) $this->_translators;
    }

    public function setTranslators(array $authors)
    {
        $this->_translators = null;
        array_map(array($this, 'addTranslator'), $authors);
        return $this;
    }

    public function setFromArray(array $data)
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, '_' . $key)) {
                $this->{'set' . $key}($value);
            }
        }
        return $this;
    }

    public function toArray()
    {
        $array = array();

        foreach (get_object_vars($this) as $property => $value) {
            if (substr($property, 0, 1) === '_' && !empty($value)) {
                $array[substr($property, 1)] = $value;
            }
        }

        $array['pages'] = $this->getPages();

        return $array;
    }

    public function __call($method, $args)
    {
        switch (true) {
            case strncasecmp($method, 'get', 3) === 0:
                $property = '_' . strtolower(substr($method, 3, 1)) . substr($method, 4);
                if (property_exists($this, $property)) {
                    return $this->{$property};
                }
                break;

            case strncasecmp($method, 'set', 3) === 0:
                $property = '_' . strtolower(substr($method, 3, 1)) . substr($method, 4);
                if (property_exists($this, $property)) {
                    $this->{$property} = trim(array_shift($args));
                    return $this;
                }
                break;
        }

        throw new BadMethodCallException('Invalid method name: ' . $method);
    }

    /**
     * @param  string|array $pages
     */
    public function setPages($pages)
    {
        $this->_pages = self::normalizePages($pages);
        return $this;
    }

    /**
     * @reutrn array
     */
    public function getPages()
    {
        $pages = array();
        foreach ((array) $this->_pages as $start => $end) {
            if ($start === $end) {
                $pages[] = $start;
            } else {
                $pages[] = $start . '-' . $end;
            }
        }
        return $pages;
    }

    /**
     * @return int|false
     */
    public function getLastPage()
    {
        if ($this->_pages) {
            return end($this->_pages);
        }
        return false;
    }

    /**
     * @return int|false
     */
    public function getFirstPage()
    {
        if ($this->_pages) {
            foreach ($this->_pages as $start => $end) {
                return $start;
            }
        }
        return false;
    }

    /**
     * @param  string $string
     * @return string|false
     */
    public static function normalizeAuthor($string)
    {
        // the author name must be in the following syntax:
        // Lastname, Firstname, Suffix
        // Lastname is the only required part.

        // make sure name does not contain colon

        $string = trim($string, ", \r\n\t");
        $string = str_replace(';', '', $string);

        $parts = preg_split('/\s*,\s*/', $string);
        $parts = array_slice($parts, 0, 3);

        if ($parts[0] === '') {
            return false;
        }

        return implode(', ', $parts);
    }

    /**
     * @param  string|array $pages
     * @return array
     */
    public static function normalizePages($pages)
    {
        if (!is_array($pages)) {
            $pages = explode(',', $pages);
        }
        $result = array();
        foreach ($pages as $part) {
            $range = self::extractRange($part);
            if ($range !== false) {
                list($start, $end) = $range;
                if (isset($result[$start])) {
                    $result[$start] = max($end, $result[$start]);
                } else {
                    $result[$start] = $end;
                }
            }
        }

        // sort ranges by start page
        ksort($result);

        // normalize pages, merge intersecting or adjacent ranges
        $prevEnd = null;
        $prevStart = null;
        foreach ($result as $start => $end) {
            if ($prevEnd !== null && $start <= $prevEnd + 1) {
                $result[$prevStart] = max($result[$prevStart], $end);
                // expand range, and use it's right end as the in next iteration
                $prevEnd = $result[$prevStart];
                unset($result[$start]);
                continue;
            }

            $prevStart = $start;
            $prevEnd = $end;
        }

        return $result;
    }

    /**
     * @param  string $range
     * @return array|false
     */
    public static function extractRange($range)
    {
        $range = trim($range);

        if (strpos($range, '-') === false) {
            $start = $end = intval($range);
        } else {
            list($start, $end) = array_map('intval', explode('-', $range, 2));
        }

        if ($start > 0 && $end >= $start) {
            return array($start, $end);
        }

        return false;
    }
}
