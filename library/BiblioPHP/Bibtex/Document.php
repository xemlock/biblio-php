<?php

class BiblioPHP_Bibtex_Document extends BiblioPHP_Document
{
    public function __construct()
    {
        $this->_parser = new BiblioPHP_Bibtex_Parser();
        $this->_mapper = new BiblioPHP_Bibtex_Mapper();
    }
}
