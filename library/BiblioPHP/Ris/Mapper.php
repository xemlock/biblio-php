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

        $publication = new BiblioPHP_Publication();
        $publication->setType(
            BiblioPHP_Ris_PubTypeMap::toPubType($data['TY'])
        );

        //http://support.mendeley.com/customer/portal/articles/1006006-what-is-the-mapping-between-ris-files-and-mendeley-
        // check for title, look in TI first, then T1, then CT (Mendeley)
        $title = null;

        if (isset($data['TI'])) {
            $title = trim($data['TI']);
        }
        if (empty($title) && isset($data['T1'])) {
            $title = trim($data['T1']);
        }
        if (empty($title) && isset($data['CT'])) {
            $title = trim($data['CT']);
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
        // DO, N1 (Science, AMS), L3 (Nature) or M3
        $doi = null;

        if (isset($data['DO'])) {
            $doi = trim($data['DO']);
        }

        foreach (array('N1', 'L3', 'M3') as $field) {
            if (empty($doi) && isset($data[$field])) {
                $tmp = trim($data[$field]);
                if (strncasecmp('doi:', $tmp, 4) === 0) {
                    // strip off doi: prefix
                    $doi = ltrim(substr($tmp, 4));
                } elseif (strncmp('10.', $tmp, 3) === 0) {
                    $doi = $tmp;
                }
            }
        }

        $publication->setDoi($doi);

        // set pages, page ranges if present are expected to be stored in SP
        if (isset($data['SP'])) {
            $sp = $data['SP'];

            if (strpos($sp, '-') !== false || strpos($sp, ',') !== false) {
                $publication->setPages($sp);
            } elseif (isset($data['EP'])) {
                $publication->setPages($sp . '-' . (int) $data['EP']);
            } else {
                $publication->setPages($sp);
            }
        }

        if (isset($data['UR'])) {
            $publication->setUrl($data['UR']);
        }
        // TODO look for URL in L1, L2, L3, L4

        if (isset($data['SN'])) {
            $publication->setSerialNumber($data['SN']);
        }

        if (isset($data['IS'])) {
            $publication->setIssue($data['IS']);
        }

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

        if (isset($data['AB'])) {
            $abstract = trim($data['AB']);
            // AMS adds Abstract in front of abstract
            if (strncasecmp($abstract, 'Abstract', 8) === 0) {
                $abstract = substr($abstract, 8);
            }
            $publication->setAbstract($abstract);
        }

        // dates
        if (isset($data['PY'])) {
            $publication->setYear(intval($data['PY']));
        }

        // dates are expected to be in format YYYY/MM/DD
        if (isset($data['DA'])) {
            $date = null;
            $parts = array_map(
                'intval',
                array_slice(explode('/', $data['DA']), 0, 3)
            );
            switch (count($parts)) {
                case 3:
                    list($y, $m, $d) = $parts;
                    if (checkdate($m, $d, $y)) {
                        $date = sprintf('%04d-%02d-%02d', $y, $m, $d);
                    }
                    break;

                case 2:
                    list($y, $m) = $parts;
                    if (1 <= $m && $m <= 12) {
                        $date = sprintf('%04d-%02d', $y, $m);
                    }
                    break;

                case 1:
                    list($y) = $parts;
                    $date = sprintf('%04d', $y);
                    break;
            }

            if ($date) {
                $publication->setDate($date);
            }
        }

        // authors
        // Science uses A1
        foreach (array('AU', 'A1') as $field) {
            if (isset($data[$field])) {
                foreach ((array) $data[$field] as $author) {
                    $publication->addAuthor($author);
                }
            }
        }

        // editors, ED - Mendeley
        foreach (array('A2', 'A3', 'ED') as $field) {
            if (isset($data[$field])) {
                foreach ((array) $data[$field] as $author) {
                    $publication->addEditor($author);
                }
            }
        }

        // translators
        if (isset($data['A4'])) {
            foreach ((array) $data[$field] as $author) {
                $publication->addTranslator($author);
            }
        }

        // keywords
        if (isset($data['KW'])) {
            foreach ((array) $data['KW'] as $keyword) {
                $publication->addKeyword($keyword);
            }
        }

        return $publication;
    }

    /**
     * @param  BiblioPHP_Publication $publication
     * @return string
     */
    public function toString(BiblioPHP_Publication $publication)
    {
        $string = sprintf("TY  - %s\r\n", BiblioPHP_Ris_PubTypeMap::fromPubType($publication->getType()));

        $string .= sprintf("TI  - %s\r\n", $this->normalizeSpace($publication->getTitle()));
        $string .= sprintf("T2  - %s\r\n", $this->normalizeSpace($publication->getJournal()));

        // volumeTitle
        $series = $publication->getSeries();
        if ($series) {
            $string .= sprintf("T3  - %s\r\n", $this->normalizeSpace($series));
        }

        foreach ($publication->getAuthors() as $author) {
            $string .= sprintf("AU  - %s\r\n", $this->normalizeSpace($author));
        }

        foreach ($publication->getEditors() as $editor) {
            $string .= sprintf("A2  - %s\r\n", $this->normalizeSpace($editor));
        }

        foreach ($publication->getTranslators() as $translator) {
            $string .= sprintf("A4  - %s\r\n", $this->normalizeSpace($editor));
        }

        $year = (int) $publication->getYear();
        if ($year > 0) {
            $string .= sprintf("PY  - %04d\r\n", $year);
        }

        $ranges = $publication->getPages();
        if (count($ranges) > 1) {
            // The SP approach works properly with both Endnote X3 and
            // Zotero 2.0.8, even though it is technically not within the spec
            // https://jira.sakaiproject.org/browse/SAK-16740
            $string .= sprintf("SP  - %s\r\n", implode(', ', $ranges));
            $string .= sprintf("EP  - %d\r\n", $publication->getLastPage());
        } elseif (count($ranges)) {
            $string .= sprintf("SP  - %d\r\nEP  - %d\r\n",
                $publication->getFirstPage(),
                $publication->getLastPage()
            );
        }

        $vol = (int) $publication->getVolume();
        if ($vol > 0) {
            $string .= sprintf("VL  - %d\r\n", $vol);
        }

        $issue = (int) $publication->getIssue();
        if ($issue > 0) {
            $string .= sprintf("IS  - %d\r\n", $issue);
        }

        $publisher = $publication->getPublisher();
        if ($publisher) {
            $string .= sprintf("PB  - %s\r\n", $this->normalizeSpace($publisher));
        }

        $lang = $publication->getLanguage();
        if ($lang) {
            $string .= sprintf("LA  - %s\r\n", $this->normalizeSpace($lang));
        }

        $sn = $publication->getSerialNumber();
        if ($sn) {
            $string .= sprintf("SN  - %s\r\n", $this->normalizeSpace($sn));
        }

        $doi = $publication->getDoi();
        if ($doi) {
            $string .= sprintf("DO  - %s\r\n", $this->normalizeSpace($doi));
        }

        $url = $publication->getUrl();
        if ($url) {
            $string .= sprintf("UR  - %s\r\n", $this->normalizeSpace($url));
        }

        foreach ($publication->getKeywords() as $keyword) {
            $string .= sprintf("KW  - %s\r\n", $this->normalizeSpace($keyword));
        }

        $abstract = $publication->getAbstract();
        if ($abstract) {
            $string .= sprintf("AB  - %s\r\n", $this->normalizeSpace($abstract));
        }

        return $string;
    }

    public function normalizeSpace($value)
    {
        return trim(preg_replace('/\s+/', ' ', $value));
    }
}
