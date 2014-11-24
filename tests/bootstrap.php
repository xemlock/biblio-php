<?php

error_reporting(E_ALL | E_STRICT);
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);

set_include_path(
    realpath(__DIR__ . '/../library')
    . PATH_SEPARATOR
    . get_include_path()
);


require_once 'BiblioPHP/ParserInterface.php';
require_once 'BiblioPHP/Publication.php';
require_once 'BiblioPHP/PublicationType.php';

require_once 'BiblioPHP/Bibtex/Parser.php';
require_once 'BiblioPHP/Bibtex/Mapper.php';
require_once 'BiblioPHP/Bibtex/PubTypeMap.php';

function test_asset($filename) {
    return dirname(__FILE__) . '/assets/' . $filename;
}
