<?php

require_once 'BiblioPHP/Bibtex/Mapper.php';
require_once 'BiblioPHP/Bibtex/PubTypeMap.php';

require_once 'BiblioPHP/Publication.php';
require_once 'BiblioPHP/PublicationType.php';

class BiblioPHP_Bibtex_MapperTest extends PHPUnit_Framework_TestCase
{
    public function testAuthors()
    {
        $mapper = new BiblioPHP_Bibtex_Mapper();
        $publication = $mapper->fromArray(array(
            'entryType' => 'article',
            'author'    => 'Ludwig van Beethoven and Strauss, Jr., Johann'
        ));

        $this->assertEquals(
            $publication->getAuthors(),
            array(
                'van Beethoven, Ludwig',
                'Strauss, Johann, Jr.',
            )
        );
    }
}
