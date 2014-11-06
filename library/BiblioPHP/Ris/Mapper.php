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

        foreach (array('DO', 'N1', 'L3', 'M3') as $field) {
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
            $sp = $data['SP'];

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

            // store date if at least month is given
            $month = (int) $publication->getMonth();
            if ($month > 0) {
                $date = sprintf("%04d/%02d", $year, $month);

                $day = (int) $publication->getDay();
                if ($day > 0) {
                    $date .= sprintf("/%02", $day);
                }

                $string .= sprintf("DA  - %s\r\n", $date);
            }
        }

        $pages = $publication->getPages();

        switch (count($pages)) {
            case 0:
                break;

            case 1:
                $string .= sprintf("SP  - %d\r\nEP  - %d\r\n",
                    $publication->getFirstPage(),
                    $publication->getLastPage()
                );
                break;

            default:
                // If there is more than one range of pages, store them in SP,
                // and store the last page in EP. This approach supposedly works
                // with both EndNote X3 and Zotero 2.0.8
                $string .= sprintf("SP  - %s\r\n", implode(', ', $pages));
                $string .= sprintf("EP  - %d\r\n", $publication->getLastPage());
                break;
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
