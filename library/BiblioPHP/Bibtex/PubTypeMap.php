<?php

abstract class BiblioPHP_Bibtex_PubTypeMap
{
    public static function toPubType($value)
    {
        $value = strtolower($value);

        switch ($value) {
            case 'article':
                return BiblioPHP_PublicationType::ARTICLE;

            case 'book':
                return BiblioPHP_PublicationType::BOOK;

            case 'booklet':
                return BiblioPHP_PublicationType::BOOKLET;

            case 'inbook':
            case 'incollection':
                return BiblioPHP_PublicationType::CHAPTER;

            case 'proceedings':
                return BiblioPHP_PublicationType::PROCEEDINGS;

            case 'inproceedings':
            case 'conference':
                return BiblioPHP_PublicationType::CONF_PAPER;

            case 'manual':
                return BiblioPHP_PublicationType::MANUAL;

            case 'mastersthesis':
                return BiblioPHP_PublicationType::MASTER_THESIS;

            case 'phdthesis':
                return BiblioPHP_PublicationType::PHD_THESIS;

            case 'techreport':
                return BiblioPHP_PublicationType::REPORT;

            case 'unpublished':
                return BiblioPHP_PublicationType::UNPUBLISHED;

            default:
                return BiblioPHP_PublicationType::GENERIC;
        }
    }

    public static function fromPubType($pubType)
    {
        switch ($pubType) {
            case BiblioPHP_PublicationType::ARTICLE:
                return 'article';

            case BiblioPHP_PublicationType::BOOK:
                return 'book';

            case BiblioPHP_PublicationType::BOOKLET:
                return 'booklet';

            case BiblioPHP_PublicationType::CHAPTER:
                return 'inbook';

            case BiblioPHP_PublicationType::CONF_PAPER:
                return 'inproceedings';

            case BiblioPHP_PublicationType::MANUAL:
                return 'manual';

            case BiblioPHP_PublicationType::MASTER_THESIS:
                return 'mastersthesis';

            case BiblioPHP_PublicationType::PHD_THESIS:
                return 'phdthesis';

            case BiblioPHP_PublicationType::PROCEEDINGS:
                return 'proceedings';

            case BiblioPHP_PublicationType::REPORT:
                return 'techreport';

            case BiblioPHP_PublicationType::UNPUBLISHED:
                return 'unpublished';

            default:
                return 'misc';
        }
    }
}
