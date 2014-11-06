<?php

abstract class BiblioPHP_Bibtex_PubTypeMap
{
    public static function toPubType($value)
    {
        $value = strtolower($value);

        switch ($value) {
            case 'article':
                return PublicationType::ARTICLE;

            case 'book':
                return PublicationType::BOOK;

            case 'booklet':
                return PublicationType::BOOKLET;

            case 'inbook':
            case 'incollection':
                return PublicationType::CHAPTER;

            case 'proceedings':
                return PublicationType::PROCEEDINGS;

            case 'inproceedings':
            case 'conference':
                return PublicationType::CONF_PAPER;

            case 'manual':
                return PublicationType::MANUAL;

            case 'mastersthesis':
                return PublicationType::MASTER_THESIS;

            case 'phdthesis':
                return PublicationType::PHD_THESIS;

            case 'techreport':
                return PublicationType::REPORT;

            case 'unpublished':
                return PublicationType::UNPUBLISHED;

            default:
                return PublicationType::GENERIC;
        }
    }

    public static function fromPubType($pubType)
    {
        switch ($pubType) {
            case PublicationType::ARTICLE:
                return 'article';

            case PublicationType::BOOK:
                return 'book';

            case PublicationType::BOOKLET:
                return 'booklet';

            case PublicationType::CHAPTER:
                return 'inbook';

            case PublicationType::CONF_PAPER:
                return 'inproceedings';

            case PublicationType::MANUAL:
                return 'manual';

            case PublicationType::MASTER_THESIS:
                return 'mastersthesis';

            case PublicationType::PHD_THESIS:
                return 'phdthesis';

            case PublicationType::PROCEEDINGS:
                return 'proceedings';

            case PublicationType::REPORT:
                return 'techreport';

            case PublicationType::UNPUBLISHED:
                return 'unpublished';

            default:
                return 'misc';
        }
    }
}
