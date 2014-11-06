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
                return BiblioPHP_PublicationType::ARTICLE;

            case 'BOOK':   // Book
            case 'EBOOK':  // Electronic Book
            case 'EDBOOK': // Edited Book
                return BiblioPHP_PublicationType::BOOK;

            case 'PAMP':   // Pamphlet
                return BiblioPHP_PublicationType::BOOKLET;

            case 'CHAP':   // Book Section
            case 'ECHAP':  // Electronic Book Section
                return BiblioPHP_PublicationType::CHAPTER;

            case 'CONF':   // Conference Proceeding
                return BiblioPHP_PublicationType::PROCEEDINGS;

            case 'CPAPER': // Conference Paper
                return BiblioPHP_PublicationType::CONF_PAPER;

            case 'THES':   // Thesis
                // assume PhD thesis by default
                return BiblioPHP_PublicationType::PHD_THESIS;

            case 'RPRT':   // Report
                return BiblioPHP_PublicationType::REPORT;

            case 'UNPB':   // Unpublished Work
                return BiblioPHP_PublicationType::UNPUBLISHED;

            default:       // Generic
                return BiblioPHP_PublicationType::GENERIC;
        }
    }

    public static function fromPubType($pubType)
    {
        switch ($pubType) {
            case BiblioPHP_PublicationType::ARTICLE:
                return 'JOUR';

            case BiblioPHP_PublicationType::BOOK:
                return 'BOOK';

            case BiblioPHP_PublicationType::BOOKLET:
                return 'PAMP';

            case BiblioPHP_PublicationType::CHAPTER:
                return 'CHAP';

            case BiblioPHP_PublicationType::CONF_PAPER:
                return 'CPAPER';

            case BiblioPHP_PublicationType::MASTER_THESIS:
            case BiblioPHP_PublicationType::PHD_THESIS:
                return 'THESIS';

            case BiblioPHP_PublicationType::PROCEEDINGS:
                return 'CONF';

            case BiblioPHP_PublicationType::REPORT:
                return 'RPRT';

            case BiblioPHP_PublicationType::UNPUBLISHED:
                return 'UNPB';

            default:
                return 'GEN';
        }
    }
}
