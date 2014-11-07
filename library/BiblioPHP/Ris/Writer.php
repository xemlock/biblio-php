<?php

class BiblioPHP_Ris_Writer
{
    /**
     * @param  BiblioPHP_Publication $publication
     * @return string
     */
    public function write(BiblioPHP_Publication $publication)
    {
        $string = array();

        $type = BiblioPHP_Ris_PubTypeMap::fromPubType($publication->getPubType());
        $string[] = $this->formatField('TY', $type);

        $string[] = $this->formatField('TI', $publication->getTitle());
        $string[] = $this->formatField('T2', $publication->getJournal());

        // volume or series title
        $series = $publication->getSeries();
        if ($series) {
            $string[] = $this->formatField('T3', $series);
        }

        foreach ($publication->getAuthors() as $author) {
            $string[] = $this->formatField('AU', $this->formatAuthor($author));
        }

        foreach ($publication->getEditors() as $editor) {
            $string[] = $this->formatField('A2', $this->formatAuthor($editor));
        }

        foreach ($publication->getTranslators() as $translator) {
            $string[] = $this->formatField('A4', $this->formatAuthor($translator));
        }

        $year = (int) $publication->getYear();
        if ($year > 0) {
            $string[] = $this->formatField('PY', $year);

            // wrtie date if at least month is given
            $month = (int) $publication->getMonth();
            if ($month > 0) {
                $date = sprintf("%04d/%02d", $year, $month);

                $day = (int) $publication->getDay();
                if ($day > 0) {
                    $date .= sprintf("/%02", $day);
                }

                $string[] = $this->formatField('DA', $date);
            }
        }

        $pages = $publication->getPages();
        switch (count($pages)) {
            case 0:
                break;

            case 1:
                $string[] = $this->formatField('SP', $publication->getFirstPage());
                $string[] = $this->formatField('EP', $publication->getLastPage());
                break;

            default:
                // If there is more than one range of pages, store them in SP,
                // and store the last page in EP. This approach supposedly works
                // with both EndNote X3 and some others
                $string[] = $this->formatField('SP', implode(', ', $pages));
                $string[] = $this->formatField('EP', $publication->getLastPage());
                break;
        }

        $language = $publication->getLanguage();
        if ($language) {
            $string[] = $this->formatField('LA', $language);
        }

        $volume = (int) $publication->getVolume();
        if ($volume > 0) {
            $string[] = $this->formatField('VL', $volume);
        }

        $issue = (int) $publication->getIssue();
        if ($issue > 0) {
            $string[] = $this->formatField('IS', $issue);
        }

        $publisher = $publication->getPublisher();
        if ($publisher) {
            $string[] = $this->formatField('PB', $publisher);
        }

        $sn = $publication->getSerialNumber();
        if ($sn) {
            $string[] = $this->formatField('SN', $sn);
        }

        $doi = $publication->getDoi();
        if ($doi) {
            $string[] = $this->formatField('DO', $doi);
        }

        $url = $publication->getUrl();
        if ($url) {
            $string[] = $this->formatField('UR', $url);
        }

        foreach ($publication->getKeywords() as $keyword) {
            $string[] = $this->formatField('KW', $keyword);
        }

        $abstract = $publication->getAbstract();
        if ($abstract) {
            $string[] = $this->formatField('AB', $abstract);
        }

        $string[] = $this->formatField('ER');

        return implode("\r\n", $string);
    }

    /**
     * @param  string $name
     * @param  mixed $value
     * @return string
     */
    public function formatField($name, $value = '')
    {
        $name = strtoupper(substr($name, 0, 2));
        $value = trim(preg_replace('/\s+/', ' ', $value));
        return sprintf("%2s  - %s", $name, $value);
    }

    /**
     * @return string
     */
    public function formatAuthor($author)
    {
        return $author;
    }
}
