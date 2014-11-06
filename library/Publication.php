<?php

class BiblioPHP_Publication
{
    /**
     * One of BiblioPHP_PublicationType constants
     * @var string
     */
    protected $_type;

    protected $_title;

    /**
     * Journal title, book title, or conference title in case of conference paper
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

    protected $_place;

    protected $_language;

    protected $_startPage;

    protected $_endPage;

    protected $_volume;

    protected $_issue;

    protected $_publisher;

    protected $_serialNumber;

    protected $_url;

    protected $_abstract;

    protected $_keywords;

    protected $_notes;

    protected $_date;

    public function addKeyword($keyword)
    {
        $this->_keywords[] = trim($keyword);
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
     * @param  string $string
     * @return string|false
     */
    public static function normalizeAuthor($string)
    {
        // the author name must be in the following syntax:
        // Lastname, Firstname, Suffix
        // Lastname is the only required part.

        $string = trim($string, ", \r\n\t");

        $parts = preg_split('/\s*,\s*/', $string);
        $parts = array_slice($parts, 0, 3);

        if ($parts[0] === '') {
            return false;
        }

        return implode(', ', $parts);
    }
}
