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
     * Book title, or conference title in case of conference paper
     * @var string
     */
    protected $_bookTitle;

    protected $_seriesTitle;

    /**
     * @var PublicationAuthors[]
     */
    protected $_authors;

    protected $_doi;

    protected $_year;

    protected $_month;

    protected $_day;

    protected $_place;

    protected $_language;

    protected $_startPage;

    protected $_endPage;

    protected $_volume;

    protected $_issue;

    protected $_publisher;

    protected $_serial;

    protected $_url;

    protected $_abstract;

    protected $_keywords;

    protected $_notes;

    /**
     * @param  BiblioPHP_PublicationAuthor $author
     * @return BiblioPHP_Publication
     */
    public function addAuthor(BiblioPHP_PublicationAuthor $author)
    {
        $this->_authors[] = $author;
        return $this;
    }

    /**
     * @param  BiblioPHP_PublicationAuthor[] $authors
     * @return BiblioPHP_Publication
     */
    public function setAuthors(array $authors)
    {
        $this->_authors = array();
        foreach ($authors as $author) {
            $this->addAuthor($author);
        }
        return $this;
    }
}
