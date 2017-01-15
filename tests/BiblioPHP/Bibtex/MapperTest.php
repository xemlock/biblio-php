<?php

class BiblioPHP_Bibtex_MapperTest extends PHPUnit_Framework_TestCase
{
    public function stringifyAuthor(BiblioPHP_PublicationAuthor $author)
    {
        $parts = array(
            $author->getLastName(),
        );
        if ($author->getFirstName()) {
            $parts[] = $author->getFirstName();
        }
        if ($author->getSuffix()) {
            $parts[] = $author->getSuffix();
        }
        return implode(', ', $parts);
    }

    public function testAuthors()
    {
        $mapper = new BiblioPHP_Bibtex_Mapper();
        $publication = $mapper->fromArray(array(
            'entryType' => 'article',
            'author'    => 'Ludwig van Beethoven and Strauss, Jr., Johann'
        ));

        $this->assertEquals(
            array(
                'van Beethoven, Ludwig',
                'Strauss, Johann, Jr.',
            ),
            array_map(
                array($this, 'stringifyAuthor'),
                $publication->getAuthors()
            )
        );
    }
}
