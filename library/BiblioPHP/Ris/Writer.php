<?php

class BiblioPHP_Ris_Writer
{
    public function write(BiblioPHP_Publication $publication)
    {
        $string = sprintf("TY  - %s\r\n", BiblioPHP_Ris_PubTypeMap::fromPubType($publication->getType()));

        $string .= sprintf("TI  - %s\r\n", $this->normalizeSpace($publication->getTitle()));
        $string .= sprintf("T2  - %s\r\n", $this->normalizeSpace($publication->getJournal()));

        // volume or series title
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

            // wrtie date if at least month is given
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
                // with both EndNote X3 and some others
                $string .= sprintf("SP  - %s\r\n", implode(', ', $pages));
                $string .= sprintf("EP  - %d\r\n", $publication->getLastPage());
                break;
        }

        $lang = $publication->getLanguage();
        if ($lang) {
            $string .= sprintf("LA  - %s\r\n", $this->normalizeSpace($lang));
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

        $string .= "ER  - \r\n";

        return $string;
    }

    public function normalizeSpace($value)
    {
        return trim(preg_replace('/\s+/', ' ', $value));
    }
}
