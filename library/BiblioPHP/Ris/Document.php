<?php

class BiblioPHP_Ris_Document extends BiblioPHP_Document
{
    public function __construct()
    {
        $this->_parser = new BiblioPHP_Ris_Parser();
        $this->_mapper = new BiblioPHP_Ris_Mapper();
    }
}
