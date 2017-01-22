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
     * @var PublicationAuthor[]
     */
    protected $_authors;

    /**
     * @var PublicationAuthor[]
     */
    protected $_editors;

    /**
     * @var PublicationAuthor[]
     */
    protected $_translators;

    protected $_doi;

    protected $_year;

    protected $_month;

    protected $_day;

    protected $_place;

    protected $_language;

    /**
     * @var BiblioPHP_PageRangeCollection
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

    public function setTitle($title)
    {
        // normalize spaces
        $this->_title = trim(preg_replace('/\s+/', ' ', $title));
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

    public function setKeywords($keywords)
    {
        if (!is_array($keywords)) {
            $keywords = explode(',', $keywords);
        }
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
        $author = BiblioPHP_PublicationAuthor::factory($author);
        if ($author) {
            $this->_authors[] = $author;
        }
        return $this;
    }

    public function getAuthors()
    {
        return (array) $this->_authors;
    }

    /**
     * @param  string|array $authors
     * @return BiblioPHP_Publication
     */
    public function setAuthors($authors)
    {
        if (!is_array($authors)) {
            $authors = explode(';', $authors);
        }
        $this->_authors = null;
        array_map(array($this, 'addAuthor'), $authors);
        return $this;
    }

    public function addEditor($author)
    {
        $author = BiblioPHP_PublicationAuthor::factory($author);
        if ($author !== false) {
            $this->_editors[] = $author;
        }
        return $this;
    }

    public function getEditors()
    {
        return (array) $this->_editors;
    }

    public function setEditors($authors)
    {
        if (!is_array($authors)) {
            $authors = explode(';', $authors);
        }
        $this->_authors = null;
        array_map(array($this, 'addEditor'), $authors);
        return $this;
    }

    public function addTranslator($author)
    {
        $author = BiblioPHP_PublicationAuthor::factory($author);
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
     * @param string
     * @return BiblioPHP_Publication
     */
    public function setPages($pages)
    {
        $this->_pages = BiblioPHP_PageRangeCollection::fromString($pages);
        return $this;
    }

    /**
     * @return BiblioPHP_PageRangeCollection
     */
    public function getPages()
    {
        if (!$this->_pages instanceof BiblioPHP_PageRangeCollection) {
            $this->_pages = new BiblioPHP_PageRangeCollection();
        }
        return $this->_pages;
    }
}
