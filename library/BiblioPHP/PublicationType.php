<?php

abstract class BiblioPHP_PublicationType
{
    /**
     * Generic type publication.
     */
    const GENERIC       = 'generic';

    /**
     * An article from a journal or magazine.
     */
    const ARTICLE       = 'article';

    /**
     * A book with an explicit publisher.
     */
    const BOOK          = 'book';

    /**
     * A work that is printed and bound, but without a named publisher or
     * sponsoring institution.
     */
    const BOOKLET       = 'booklet';

    /**
     * A part of a book, which may be a chapter (or section or whatever)
     * and/or a range of pages. It may also have its own title.
     */
    const CHAPTER       = 'chapter';

    /**
     * An article in a conference proceedings.
     */
    const CONF_PAPER    = 'conf_paper';

    /**
     * Technical documentation.
     */
    const MANUAL        = 'manual';

    /**
     * A Master's thesis.
     */
    const MASTER_THESIS = 'master_thesis';

    /**
     * A PhD thesis.
     */
    const PHD_THESIS    = 'phd_thesis';

    /**
     * The proceedings of a conference.
     */
    const PROCEEDINGS   = 'proceedings';

    /**
     * A report published by a school or other institution, usually numbered
     * within a series.
     */
    const REPORT        = 'report';

    /**
     * A document having an author and title, but not formally published.
     */
    const UNPUBLISHED   = 'unpublished';

    /**
     * @return array
     */
    public static function getTypes()
    {
        return array(
            self::GENERIC,
            self::ARTICLE,
            self::BOOK,
            self::BOOKLET,
            self::CHAPTER,
            self::CONF_PAPER,
            self::MANUAL,
            self::MASTER_THESIS,
            self::PHD_THESIS,
            self::PROCEEDINGS,
            self::REPORT,
            self::UNPUBLISHED,
        );
    }

    /**
     * @param  string $type
     * @return bool
     */
    public static function isValid($type)
    {
        return in_array($type, self::getTypes(), true);
    }
}
