<?php

abstract class BiblioPHP_Document implements Countable, IteratorAggregate
{
    /**
     * @var BiblioPHP_ParserInterface
     */
    protected $_parser;

    /**
     * @var BiblioPHP_MapperInterface
     */
    protected $_mapper;

    /**
     * @var BiblioPHP_Publication[]
     */
    protected $_entries = array();

    /**
     * @param  string $path
     * @return int
     */
    public function loadFromFile($path)
    {
        return $this->_loadEntries($this->getParser()->parseFile($path));
    }

    /**
     * @param  string $string
     * @return int
     */
    public function loadFromString($string)
    {
        return $this->_loadEntries($this->getParser()->parse($path));
    }

    /**
     * @param  array $data
     * @return int
     */
    protected function _loadEntries(array $data)
    {
        $loaded = 0;
        foreach ($data as $entry) {
            $this->_entries[] = $this->getMapper()->fromArray($entry);
            ++$loaded;
        }
        return $loaded;
    }

    /**
     * @return BiblioPHP_ParserInterface
     */
    public function getParser()
    {
        return $this->_parser;
    }

    /**
     * @return BiblioPHP_MapperInterface
     */
    public function getMapper()
    {
        return $this->_mapper;
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->_entries);
    }

    /**
     * @return Traversable
     */
    public function getIterator()
    {
        return new ArrayIterator($this->_entries);
    }
}
