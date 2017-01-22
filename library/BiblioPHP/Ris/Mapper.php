<?php

class BiblioPHP_Ris_Mapper
{
    /**
     * @param  array $data
     * @return BiblioPHP_Publication
     */
    public function fromArray(array $data)
    {
        if (empty($data['TY'])) {
            throw new InvalidArgumentException('Invalid data provided');
        }

        $pubType = BiblioPHP_Ris_PubTypeMap::toPubType($data['TY']);

        $publication = new BiblioPHP_Publication();
        $publication->setPubType($pubType);

        // http://support.mendeley.com/customer/portal/articles/1006006-what-is-the-mapping-between-ris-files-and-mendeley-
        // check for title, look in TI first, then T1, then CT (Mendeley)
        $title = null;

        foreach (array('TI', 'T1', 'CT') as $field) {
            if (empty($title) && isset($data[$field])) {
                $title = trim($data[$field]);
            }
        }

        $publication->setTitle($title);

        // journal title
        $journal = null;

        // Sience and AMS Journal use JF to store full journal title
        // and abbreviated in JO

        if (isset($data['JF'])) {
            $journal = trim($data['JF']);
        }

        if (empty($journal) && isset($data['JO'])) {
            $journal = trim($data['JO']);
        }

        // Nature uses JA to store journal title
        if (empty($journal) && isset($data['JA'])) {
            $journal = trim($data['JA']);
        }

        // T2 is also used as a Journal title
        // http://www.refman.com/support/risformat_intro.asp
        if (empty($journal) && isset($data['T2'])) {
            $journal = trim($data['T2']);
        }

        $publication->setJournal($journal);

        // T3 - Tertiary title, series title
        $series = null;

        if (empty($series) && isset($data['T3'])) {
            $series = trim($data['T3']);
        }

        $publication->setSeries($series);

        // check for DOI
        // DO, N1 (Science, AMS), L3 (Nature), ID (APS) or M3
        $doi = null;

        foreach (array('DO', 'N1', 'L3', 'ID', 'M3') as $field) {
            if (empty($doi) && isset($data[$field])) {
                $tmp = trim($data[$field]);
                if (strncasecmp('doi:', $tmp, 4) === 0) {
                    // strip off doi: prefix
                    $doi = ltrim(substr($tmp, 4));
                } elseif (strncmp('10.', $tmp, 3) === 0) {
                    $doi = $tmp;
                } elseif (strncmp('http://dx.doi.org/', $tmp, 18) === 0) {
                    // some providers (Elsevier) put DOI URL here
                    $doi = substr($tmp, 18);
                }
            }
        }

        $publication->setDoi($doi);

        // set pages, page ranges if present are expected to be stored in SP
        if (isset($data['SP'])) {
            $sp = (string) $data['SP'];

            if (strpos($sp, '-') !== false || strpos($sp, ',') !== false) {
                $publication->setPages($sp);
            } elseif (isset($data['EP'])) {
                $publication->setPages($sp . '-' . (int) $data['EP']);
            } else {
                $publication->setPages($sp);
            }
        }

        // do not look for URL in L1-L4 fields, as the spec says they are
        // for local file attachments only
        $url = null;
        if (isset($data['UR'])) {
            $tmp = trim($data['UR']);
            if (preg_match('/(ht|f)tp(s)?:\/\//i', $tmp)) {
                $url = $tmp;
            }
        }
        $publication->setUrl($url);


        if (isset($data['SN'])) {
            $publication->setSerialNumber($data['SN']);
        }

        // issue number
        $issue = null;

        // EndNote stores issue number in M1
        foreach (array('IS', 'M1') as $field) {
            if (empty($issue) && isset($data[$field])) {
                $issue = trim($data[$field]);
            }
        }

        $publication->setIssue($issue);

        if (isset($data['VL'])) {
            $publication->setVolume($data['VL']);
        }

        if (isset($data['PB'])) {
            $publication->setPublisher($data['PB']);
        }

        if (isset($data['CY'])) {
            $publication->setPlace($data['CY']);
        }

        if (isset($data['LA'])) {
            $publication->setLanguage($data['LA']);
        }

        // look for abstract in AB and N2
        $abstract = null;
        foreach (array('AB', 'N2') as $field) {
            if (empty($abstract) && isset($data[$field])) {
                $tmp = trim($data[$field]);

                // AMS adds Abstract at the beginning of abstract
                if (strncasecmp($tmp, 'Abstract', 8) === 0) {
                    $tmp = substr($tmp, 8);
                }

                if (strlen($tmp)) {
                    $abstract = $tmp;
                }
            }
        }
        $publication->setAbstract($abstract);

        // read dates, PY is expected to be four digits only, however a whole
        // date may be stored there. First check PY, then DA fields

        // EndNote (tested in X7) has broken support for DA. It does strange
        // things when date is not given in YYYY/MM/DD format. Don't care.

        // always check strlen when performing ctype_digit checks; before
        // PHP 5.1.0 those functions returned TRUE when text was an empty string

        // Science uses Y1

        foreach (array('PY', 'DA', 'Y1') as $field) {
            if (isset($data[$field])) {
                $parts = array_map(
                    'trim',
                    explode('/', $data[$field])
                );

                if (strlen($parts[0]) && ctype_digit($parts[0])) {
                    $publication->setYear($parts[0]);
                } else {
                    continue;
                }

                if (isset($parts[1]) && strlen($parts[1]) && ctype_digit($parts[1])) {
                    $publication->setMonth($parts[1]);
                } else {
                    continue;
                }

                if (isset($parts[2]) && strlen($parts[2]) && ctype_digit($parts[2])) {
                    $publication->setDay($parts[2]);
                }
            }
        }

        // authors
        // Science uses A1
        foreach (array('AU', 'A1') as $field) {
            if (isset($data[$field])) {
                foreach ($this->normalizeNameList($data[$field]) as $author) {
                    $publication->addAuthor($author);
                }
            }
        }

        // editors, ED - Mendeley
        foreach (array('A2', 'A3', 'ED') as $field) {
            if (isset($data[$field])) {
                foreach ($this->normalizeNameList($data[$field]) as $author) {
                    $publication->addEditor($author);
                }
            }
        }

        // translators
        if (isset($data['A4'])) {
            foreach ($this->normalizeNameList($data[$field]) as $author) {
                $publication->addTranslator($author);
            }
        }

        // keywords
        if (isset($data['KW'])) {
            foreach ((array) $data['KW'] as $keyword) {
                $publication->addKeyword($keyword);
            }
        }

        // notes
        // N1 (spec), RN (Mendeley)
        $notes = null;

        foreach (array('N1', 'RN') as $field) {
            if (empty($notes) && isset($data[$field])) {
                $notes = trim($data[$field]);
            }
        }

        $publication->setNotes($notes);

        return $publication;
    }

    /**
     * @param array|string $nameList
     * @return array
     */
    protected function normalizeNameList($nameList)
    {
        $normalizedNameList = array();
        foreach ((array) $nameList as $names) {
            // iopscience.iop.org exports names in a single A1 field separated by 'and'
            foreach (preg_split('/\s+(and)\s+/', $names) as $name) {
                $name = trim($name);
                if (strlen($name)) {
                    $normalizedNameList[] = $name;
                }
            }
        }
        return $normalizedNameList;
    }
}
