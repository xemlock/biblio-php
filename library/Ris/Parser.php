<?php

/**
 * RIS format file parser.
 *
 * http://en.wikipedia.org/wiki/RIS_%28file_format%29
 * http://www.adeptscience.co.uk/kb/article/FE26
 */
class BiblioPHP_Ris_Parser
{
    /**
     * @var stream
     */
    protected $_stream;

    /**
     * @var int
     */
    protected $_line;

    /**
     * @param  string $string
     * @return array
     */
    public function parse($string)
    {
        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $string);
        rewind($stream);
        return $this->parseStream($stream);
    }

    /**
     * @param  string $file
     * @return array
     * @throws Exception
     */
    public function parseFile($file)
    {
        $stream = @fopen($file, 'rb');
        if (!$stream) {
            throw new Exception(sprintf('Unable to open file: %s', $file));
        }
        return $this->parseStream($stream);
    }

    /**
     * @param  resource $stream
     * @return array
     * @throws InvalidArgumentException
     */
    public function parseStream($stream)
    {
        $meta = @stream_get_meta_data($stream);

        if (empty($meta)) {
            throw new InvalidArgumentException('Invalid stream provided');
        }

        $this->_line = 0;
        $this->_stream = $stream;

        $entries = array();
        while ($entry = $this->_parseEntry()) {
            $entries[] = $entry;
        }

        fclose($this->_stream);

        return $entries;
    }

    /**
     * @return string|false
     */
    protected function _getLine()
    {
        $line = fgets($this->_stream);
        if ($line !== false) {
            ++$this->_line; // increment line number
        }
        return $line;
    }

    /**
     * @return string|false
     */
    protected function _getNonEmptyLine()
    {
        while (($line = $this->_getLine()) !== false) {
            if (strlen($line) && !ctype_space($line)) {
                return $line;
            }
            $this->_debug("Empty line\n");
        }
        return false;
    }

    /**
     * @return array|false
     */
    protected function _parseEntry()
    {
        $entry = array('TY' => 'GEN');

        while (($line = $this->_getNonEmptyLine()) !== false) {
            if (!preg_match('/^(?P<key>[A-Z][A-Z0-9])  - /i', $line, $match)) {
                $this->_debug("Invalid line '%s'\n", $line);
                continue;
            }
            $key = strtoupper($match['key']);

            if ($key === 'ER') {
                $this->_debug("End of record\n");
                break;
            }

            $value = trim(substr($line, 6));

            if ($key === 'TY') {
                // record type must always be a string, and never expanded
                // to an array of types
                $this->_debug("Start of record\n");
                $entry['TY'] = $value;
                continue;
            }

            if (isset($entry[$key])) {
                if (!is_array($entry[$key])) {
                    $entry[$key] = array($entry[$key]);
                }
                $entry[$key][] = $value;
            } else {
                $entry[$key] = $value;
            }
        }

        if (count($entry) > 1) {
            return $entry;
        }

        return false;
    }

    /**
     * @internal
     */
    protected function _debug($message)
    {
        if (false) {
            $args = func_get_args();
            $args[0] = '[' . $this->_line . '] ' . $message;
            call_user_func_array('printf', $args);
        }
    }
}