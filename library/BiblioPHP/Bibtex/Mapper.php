<?php

class BiblioPHP_Bibtex_Mapper
{
    /**
     * @param  array $data
     * @return BiblioPHP_Publication
     */
    public function fromArray(array $data)
    {
        if (empty($data['entryType'])) {
            throw new InvalidArgumentException('Invalid data provided');
        }

        $pubType = BiblioPHP_Bibtex_PubTypeMap::toPubType($data['entryType']);

        $publication = new BiblioPHP_Publication();
        $publication->setPubType($pubType);

        if (isset($data['citeKey'])) {
            $publication->setCiteKey($data['citeKey']);
        }

        if (isset($data['author'])) {
            $publication->setAuthors($this->normalizeAuthors($data['author']));
        }

        if (isset($data['editor'])) {
            $publication->setEditors($this->normalizeAuthors($data['editor']));
        }

        if (isset($data['pages'])) {
            $publication->setPages($data['pages']);
        }

        if (isset($data['year'])) {
            $publication->setYear((int) $data['year']);
        }

        if (isset($data['month'])) {
            $publication->setMonth((int) $data['month']);
        }

        if (isset($data['keywords'])) {
            $publication->setKeywords($this->normalizeKeywords($data['keywords']));
        }

        if (isset($data['doi'])) {
            $publication->setDoi($data['doi']);

            // check if there is a non [dx.]doi.org URL given in doi field,
            // IEEE stores its own DOI urls in doi field
            if (preg_match('/^http(s)?:\/\//i', $data['doi']) &&
                !preg_match('/(dx\.)?doi\.org\//i', $data['doi'])
            ) {
                $publication->setUrl($data['doi']);
            }
        }

        if (isset($data['url'])) {
            $doi = $publication->getDoi();
            if (!$doi && preg_match('/.+\/(?P<doi>10\..+)/', $data['url'], $match)) {
                $doi = $match['doi'];
                $publication->setDoi($doi);
            }

            // set URL either when no DOI was extracted, or DOI was extracted
            // but the URL does not match dx.doi.org domain
            if (!$doi || !preg_match('/http(s)?:\/\/(dx\.)?doi\.org\//i', $data['url'])) {
                $publication->setUrl($data['url']);
            }
        }

        if (isset($data['title'])) {
            $publication->setTitle($data['title']);
        }

        if (isset($data['booktitle'])) {
            $publication->setSeries($data['booktitle']);
        }

        if (isset($data['series'])) {
            $publication->setSeries($data['series']);
        }

        if (isset($data['journal'])) {
            $publication->setJournal($data['journal']);
        }

        if (isset($data['issn'])) {
            $publication->setSerialNumber($data['issn']);
        }

        if (isset($data['isbn'])) {
            $publication->setSerialNumber($data['isbn']);
        }

        if (isset($data['note'])) {
            $publication->setNotes($data['note']);
        }

        if (isset($data['pages'])) {
            $publication->setPages($data['pages']);
        }

        if (isset($data['publisher'])) {
            $publication->setPublisher($data['publisher']);
        }

        if (isset($data['volume'])) {
            $publication->setVolume($data['volume']);
        }

        if (isset($data['issue'])) {
            $publication->setIssue($data['issue']);
        }

        if (isset($data['number'])) {
            $publication->setIssue($data['number']);
        }

        if (isset($data['abstract'])) {
            $publication->setAbstract($data['abstract']);
        }

        if (isset($data['language'])) {
            $publication->setLanguage($data['language']);
        }

        return $publication;
    }

    public function normalizeAuthors($authors)
    {
        // BibTeX allows three possible forms for the name:
        // "First von Last"
        // "von Last, First"
        // "von Last, Jr, First"

        $authors = array_map(
            'trim',
            preg_split('/\s+and\s+/i', $authors)
        );
        foreach ($authors as $index => $author) {
            $normalized = array(
                'firstName' => '',
                'lastName'  => '',
                'suffix'    => '',
            );
            $parts = preg_split('/\s*,\s*/', $author);
            if (count($parts) >= 3) {
                $normalized['lastName'] = $parts[0];
                $normalized['suffix'] = $parts[1];
                $normalized['firstName'] = $parts[2];
            } elseif (count($parts) === 2) {
                $normalized['lastName'] = $parts[0];
                $normalized['firstName'] = $parts[1];
            } else {
                $parts = preg_split('/\s+/', $author);
                // last name part is either a last token,
                // or starts with first lower case letter
                // BibTeX knows where one part ends and the other begins
                // because the tokens in the von part begin with lower-case letters.
                // 
                $boundary = count($parts) - 1;
                for ($i = 0; $i < count($parts); ++$i) {
                    if (ctype_lower(substr($parts[$i], 0, 1))) {
                        $boundary = $i;
                        break;
                    }
                }
                $normalized['lastName'] = implode(' ', array_slice($parts, $boundary));

                if ($boundary > 0) {
                    $normalized['firstName'] = implode(' ', array_slice($parts, 0, $boundary));
                }
            }
            $authors[$index] = $normalized;
        }

        // as long as Publication operates on strings rather than Authors
        // dump authors as Last Name, First Name, Suffix
        foreach ($authors as $key => $value) {
            $val = array($value['lastName']);
            if (strlen($value['firstName'])) {
                $val[] = $value['firstName'];
            }
            if (strlen($value['suffix'])) {
                $val[] = $value['suffix'];
            }
            $authors[$key] = implode(', ', $val);
        }

        return $authors;
    }

    public function normalizeKeywords($keywords)
    {
        if (is_string($keywords)) {
            // detect separator, EndNote uses newlines
            $keywords = str_replace(array("\r\n", "\r"), "\n", $keywords);

            if (strpos($keywords, ',') !== false) {
                $keywords = explode(',', $keywords);
            } else {
                $keywords = explode("\n", $keywords);
            }
        }

        $keywords = array_map('trim', (array) $keywords);
        $keywords = array_filter($keywords, 'strlen');

        return $keywords;
    }
}
