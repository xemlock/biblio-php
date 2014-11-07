<?php

require_once 'BiblioPHP/Ris/Writer.php';
require_once 'BiblioPHP/Ris/PubTypeMap.php';

require_once 'BiblioPHP/Publication.php';
require_once 'BiblioPHP/PublicationAuthor.php';
require_once 'BiblioPHP/PublicationType.php';

class BiblioPHP_Ris_WriterTest extends PHPUnit_Framework_TestCase
{
    public function testPages()
    {
        $writer = new BiblioPHP_Ris_Writer();

        $publication = new BiblioPHP_Publication(array(
            'pubType' => BiblioPHP_PublicationType::ARTICLE,
            'pages'   => '5-5, 12-14,13-15, 11-11,  17 - 19, 20, 21',
        ));

        $this->assertContains(
            "SP  - 5, 11-15, 17-21\r\nEP  - 21\r\n",
            $writer->write($publication)
        );

        $publication->setPages('11, 12 - 21');
        $this->assertContains(
            "SP  - 11\r\nEP  - 21\r\n",
            $writer->write($publication)
        );

        $publication->setPages('11, 11-11');
        $this->assertContains(
            "SP  - 11\r\nEP  - 11\r\n",
            $writer->write($publication)
        );
    }
}
