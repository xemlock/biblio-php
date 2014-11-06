<?php

class BiblioPHP_Bibtex_Mapper
{
    /**
     * @param  array $data
     * @return BiblioPHP_Publication
     */
    public function fromArray(array $data)
    {
        if (empty($data['type'])) {
            throw new InvalidArgumentException('Invalid data provided');
        }

        $publication = new BiblioPHP_Publication();
        $publication->setType(
            BiblioPHP_Bibtex_PubTypeMap::toPubType($data['type'])
        );
    }
}
