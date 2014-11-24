<?php

interface BiblioPHP_ParserInterface
{
    public function setInputFile($file);

    public function setInputString($string);

    public function setInputStream($stream);

    public function current();

    public function next();
}
