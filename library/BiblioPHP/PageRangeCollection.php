<?php

class BiblioPHP_PageRangeCollection
{
    protected $_dirty = false;

    protected $_pageRanges = array();

    /**
     * @param int $page
     * @return BiblioPHP_PageRangeCollection
     */
    public function addPage($page)
    {
        return $this->addPageRange($page, $page);
    }

    /**
     * @param int $startPage
     * @param int $endPage
     * @return BiblioPHP_PageRangeCollection
     * @throws InvalidArgumentException
     */
    public function addPageRange($startPage, $endPage)
    {
        $startPage = (int) $startPage;

        if ($startPage <= 0) {
            throw new InvalidArgumentException('Start page number must be a positive integer');
        }

        $endPage = (int) $endPage;
        if ($endPage <= 0) {
            throw new InvalidArgumentException('End page number must be a positive integer');
        }
        if ($endPage < $startPage) {
            throw new InvalidArgumentException('End page number must be equal or greater than start page numnber');
        }

        if (!isset($this->_pageRanges[$startPage]) || $endPage > $this->_pageRanges[$startPage]) {
            $this->_pageRanges[$startPage] = $endPage;
            $this->_dirty = true;
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getPageRanges()
    {
        if ($this->_dirty) {
            $pageRanges = $this->_pageRanges;

            // sort ranges by start page
            ksort($pageRanges);

            // normalize pages, merge intersecting or adjacent ranges
            $prevEnd = null;
            $prevStart = null;

            foreach ($pageRanges as $start => $end) {
                if ($prevEnd !== null && $start <= $prevEnd + 1) {
                    // expand range, and use it's right end as the in next iteration
                    $pageRanges[$prevStart] = max($pageRanges[$prevStart], $end);
                    $prevEnd = $pageRanges[$prevStart];
                    // $prevStart remains the same
                    unset($pageRanges[$start]);
                    continue;
                }

                $prevStart = $start;
                $prevEnd = $end;
            }

            $this->_pageRanges = $pageRanges;
            $this->_dirty = false;
        }
        return $this->_pageRanges;
    }

    /**
     * @return int
     */
    public function getPageCount()
    {
        $pageCount = 0;
        foreach ($this->getPageRanges() as $start => $end) {
            $pageCount += $end - $start + 1;
        }
        return $pageCount;
    }

    /**
     * @return int|false
     */
    public function getFirstPage()
    {
        foreach ($this->getPageRanges() as $start => $end) {
            return $start;
        }
        return false;
    }

    /**
     * @return int|false
     */
    public function getLastPage()
    {
        $pageRanges = $this->getPageRanges();
        return end($pageRanges);
    }

    /**
     * @return string
     */
    public function toString()
    {
        $parts = array();
        foreach ($this->getPageRanges() as $start => $end) {
            if ($start === $end) {
                $parts[] = sprintf('%d', $start);
            } else {
                $parts[] = sprintf('%d-%d', $start, $end);
            }
        }
        return implode(', ', $parts);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * @param string $pages
     * @return BiblioPHP_PageRangeCollection
     */
    public static function fromString($pages)
    {
        $ranges = new self();
        $pages = explode(',', (string) $pages);

        foreach ($pages as $part) {
            $range = self::extractRange($part);
            if ($range !== false) {
                list($start, $end) = $range;
                $ranges->addPageRange($start, $end);
            }
        }

        return $ranges;
    }

    /**
     * @param  string $range
     * @return array|false
     */
    public static function extractRange($range)
    {
        $range = trim($range);

        if (strpos($range, '-') === false) {
            $start = $end = intval($range);
        } else {
            list($start, $end) = array_map('intval', explode('-', $range, 2));
        }

        if ($start > 0 && $end >= $start) {
            return array($start, $end);
        }

        return false;
    }
}
