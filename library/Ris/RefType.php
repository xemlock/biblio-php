<?php

class BiblioPHP_Ris_PubTypeMap
{
    public static function toPubType($value)
    {
        $value = strtoupper($value);

        switch ($value) {
            case 'JOUR':   // Journal Article
            case 'EJOUR':  // Electronic Journal Article
            case 'MGZN':   // Magazine Article
            case 'NEWS':   // Newspaper Article
                return PublicationType::ARTICLE;

            case 'BOOK':   // Book
            case 'EBOOK':  // Electronic Book
            case 'EDBOOK': // Edited Book
                return PublicationType::BOOK;

            case 'PAMP':   // Pamphlet
                return PublicationType::BOOKLET;

            case 'CHAP':   // Book Section
            case 'ECHAP':  // Electronic Book Section
                return PublicationType::CHAPTER;

            case 'CONF':   // Conference Proceeding
                return PublicationType::PROCEEDINGS;

            case 'CPAPER': // Conference Paper
                return PublicationType::CONF_PAPER;

            case 'THES':   // Thesis
                // assume PhD thesis by default
                return PublicationType::PHD_THESIS;

            case 'RPRT':   // Report
                return PublicationType::REPORT;

            case 'UNPB':   // Unpublished Work
                return PublicationType::UNPUBLISHED;

            default:       // Generic
                return PublicationType::GENERIC;
        }
    }

    public static function fromPubType($pubType)
    {
        switch ($pubType) {
            case PublicationType::ARTICLE:
                return 'JOUR';

            case PublicationType::BOOK:
                return 'BOOK';

            case PublicationType::BOOKLET:
                return 'PAMP';

            case PublicationType::CHAPTER:
                return 'CHAP';

            case PublicationType::CONF_PAPER:
                return 'CPAPER';

            case PublicationType::MASTER_THESIS:
            case PublicationType::PHD_THESIS:
                return 'THESIS';

            case PublicationType::PROCEEDINGS:
                return 'CONF';

            case PublicationType::REPORT:
                return 'RPRT';

            case PublicationType::UNPUBLISHED:
                return 'UNPB';

            default:
                return 'GEN';
        }
    }
}
