<?php

/**
 * BibTeX tokenizer.
 */
class BiblioPHP_Bibtex_Tokenizer
{
    const S_DEFAULT         = 'DEFAULT';
    const S_COMMENT         = 'COMMENT';
    const S_TYPE            = 'TYPE';
    const S_EXPECT_ENTRY    = 'EXPECT_ENTRY';
    const S_EXPECT_STRING   = 'EXPECT_STRING';
    const S_STRING          = 'STRING';
    const S_QUOTED_STRING   = 'QUOTED_STRING';
    const S_BRACED_STRING   = 'BRACED_STRING';

    const T_TYPE            = 'T_TYPE';
    const T_STRING          = 'T_STRING';
    const T_QUOTED_STRING   = 'T_QUOTED_STRING';
    const T_BRACED_STRING   = 'T_BRACED_STRING';
    const T_CONCAT          = 'T_CONCAT';
    const T_COMMA           = 'T_COMMA';
    const T_SEPARATOR       = 'T_SEPARATOR';
    const T_BEGIN           = 'T_BEGIN';
    const T_END             = 'T_END';

    /**
     * Input stream
     * @var resource
     */
    protected $_stream;

    /**
     * Buffer for lines read from input stream
     * @var string
     */
    protected $_streamBuf;

    /**
     * Current read position in the stream buffer
     * @var int
     */
    protected $_streamBufOffset;

    /**
     * Buffer for returned characters
     * @var string
     */
    protected $_ungetBuf;

    /**
     * Tokenizer state, one of S_ constants
     * @var string
     */
    protected $_state;

    /**
     * Line counter
     * @var int
     */
    protected $_line;

    /**
     * Current token value
     * @array|false
     */
    protected $_token;

    /**
     * Next token value
     * @array|false
     */
    protected $_nextToken;

    public function setString($string)
    {
        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $string);
        rewind($stream);
        return $this->setStream($stream);
    }

    public function setFile($file)
    {
        return $this->setStream(@fopen($file, 'rb'));
    }

    public function setStream($stream)
    {
        if (!is_resource($stream) || get_resource_type($stream) !== 'stream') {
            throw new InvalidArgumentException('Invalid stream resource supplied');
        }

        $this->_stream = $stream;
        $this->_streamBuf = '';
        $this->_streamBufOffset = 0;

        $this->_line = 1;
        $this->_state = self::S_DEFAULT;
        $this->_token = array(
            'type'  => null,
            'value' => null,
            'line'  => null,
        );
        $this->_nextToken = null;

        return $this;
    }

    public function closeStream()
    {
        if ($this->_stream) {
            fclose($this->_stream);
            $this->_stream = null;
        }
        return $this;
    }

    protected function _getChar()
    {
        if (strlen($this->_ungetBuf)) {
            $char = substr($this->_ungetBuf, -1);
            $this->_ungetBuf = substr($this->_ungetBuf, 0, -1);
            return $char;
        }

        if (empty($this->_stream)) {
            return false;
        }

        if ($this->_streamBufOffset === strlen($this->_streamBuf)) {
            $this->_streamBufOffset = 0;
            $this->_streamBuf = fgets($this->_stream);

            if ($this->_streamBuf !== false) {
                // Normalize line endings
                $this->_streamBuf = str_replace(array("\r\n", "\r"), "\n", $this->_streamBuf);

                // If the entry type is @comment, it is not considered to be the start
                // of an entry. Actual rule is that everything from the @comment and to
                // the end of line is ignored. Read more:
                // http://maverick.inria.fr/~Xavier.Decoret/resources/xdkbibtex/bibtex_summary.html#comment
                $this->_streamBuf = preg_replace('/@comment[^\n]*/i', '', $this->_streamBuf);
            }
        }

        if ($this->_streamBuf === false) {
            return false;
        }

        return substr($this->_streamBuf, $this->_streamBufOffset++, 1);
    }

    protected function _ungetChar($char)
    {
        $this->_ungetBuf .= $char;
    }

    protected function _setState($state)
    {
        $this->_state = $state;
    }

    protected function _setToken($type, $value = '')
    {
        $this->_token['type']  = $type;
        $this->_token['value'] = $value;
        $this->_token['line']  = $this->_line;
        return $this->_token;
    }

    protected function _appendToken($value)
    {
        $this->_token['value'] .= $value;
        return $this->_token;
    }

    public function getToken()
    {
        return $this->_token;
    }

    public function peekToken()
    {
        if ($this->_nextToken === null) {
            $this->_nextToken = $this->_nextToken();
        }
        return $this->_nextToken;
    }

    public function nextToken()
    {
        $this->_token = $this->peekToken();
        $this->_nextToken = null;
        return $this->_token;
    }

    protected $_beginCurly;

    protected function _nextToken()
    {
        $token = array(
            'type'  => null,
            'value' => null,
            'line'  => null,
        );

        $nestLevel = 0; // {}
        $bNestLevel = 0; // ()

        while (($char = $this->_getChar()) !== false) {
            switch ($char) {
                case '@':
                    switch ($this->_state) {
                        case self::S_DEFAULT:
                            $this->_setState(self::S_TYPE);
                            $token['type']  = self::T_TYPE;
                            $token['value'] = '@';
                            $token['line']  = $this->_line;
                            break;

                        case self::S_QUOTED_STRING:
                        case self::S_BRACED_STRING:
                        case self::S_STRING:
                            $token['value'] .= '@';
                            break;

                        default:
                            // all characters outside entries are ignored
                    }
                    break;

                case '(':
                    switch ($this->_state) {
                        case self::S_TYPE:
                            $this->_setState(self::S_EXPECT_ENTRY);
                            $this->_ungetChar('(');
                            return $token;

                        case self::S_EXPECT_ENTRY:
                            $this->_setState(self::S_EXPECT_STRING);
                            $token['type']  = self::T_BEGIN;
                            $token['value'] = $char;
                            $token['line']  = $this->_line;
                            $this->_beginCurly = false;
                            return $token;

                        case self::S_EXPECT_STRING:
                            $this->_setState(self::S_STRING);
                            $bNestLevel = 1;
                            $token['type']  = self::T_STRING;
                            $token['value'] = $char;
                            $token['line']  = $this->_line;
                            break;

                        case self::S_STRING:
                            ++$bNestLevel;
                            // falls through

                        case self::S_QUOTED_STRING:
                        case self::S_BRACED_STRING:
                            $token['value'] .= $char;
                            break;
                    }
                    break;

                case ')':
                    switch ($this->_state) {
                        case self::S_TYPE:
                            $this->_setState(self::S_DEFAULT);
                            break;

                        case self::S_STRING:
                            if ($bNestLevel > 0) {
                                $token['value'] .= $char;
                                --$bNestLevel;
                            } else {
                                // not a part of string, but closing delimiter
                                $this->_ungetChar(')');
                                $this->_setState(self::S_EXPECT_STRING);
                                return $token;
                            }
                            break;

                        case self::S_QUOTED_STRING:
                        case self::S_BRACED_STRING:
                            $token['value'] .= $char;
                            break;

                        case self::S_EXPECT_STRING: // both ')' and '}' may be used to close entry
                            $this->_setState(self::S_DEFAULT);
                            $token['type']  = self::T_END;
                            $token['value'] = $char;
                            $token['line']  = $this->_line;
                            return $token;
                    }
                    break;

                case '{':
                    switch ($this->_state) {
                        case self::S_TYPE:
                            $this->_setState(self::S_EXPECT_ENTRY);
                            $this->_ungetChar('{');
                            return $token;

                        case self::S_EXPECT_ENTRY:
                            $this->_setState(self::S_EXPECT_STRING);
                            $token['type']  = self::T_BEGIN;
                            $token['value'] = $char;
                            $token['line']  = $this->_line;
                            $this->_beginCurly = true;
                            return $token;

                        case self::S_EXPECT_STRING:
                            $this->_setState(self::S_BRACED_STRING);
                            $token['type']  = self::T_BRACED_STRING;
                            $token['value'] = '';
                            $token['line']  = $this->_line;
                            $nestLevel = 0;
                            break;

                        case self::S_BRACED_STRING:
                            $token['value'] .= $char;
                            ++$nestLevel;
                            break;

                        case self::S_STRING:
                        case self::S_QUOTED_STRING:
                            $token['value'] .= $char;
                            break;
                    }
                    break;

                case '}':
                    switch ($this->_state) {
                        case self::S_TYPE:
                            $this->_setState(self::S_DEFAULT);
                            break;

                        case self::S_EXPECT_STRING: // both ')' and '}' may be used to close entry
                            $this->_setState(self::S_DEFAULT);
                            $token['type']  = self::T_END;
                            $token['value'] = $char;
                            $token['line']  = $this->_line;
                            return $token;

                        case self::S_BRACED_STRING:
                            if ($nestLevel > 0) {
                                $token['value'] .= $char;
                                --$nestLevel;
                            } else {
                                $this->_setState(self::S_EXPECT_STRING);
                                return $token;
                            }
                            break;

                        case self::S_STRING:
                        case self::S_QUOTED_STRING:
                            $token['value'] .= $char;
                            break;

                        default:
                            break;
                    }
                    break;

                case ',':
                    switch ($this->_state) {
                        case self::S_TYPE:
                            $this->_setState(self::S_DEFAULT);
                            break;

                        case self::S_STRING:
                            $this->_setState(self::S_EXPECT_STRING);
                            $this->_ungetChar(',');
                            return $token;

                        case self::S_EXPECT_STRING:
                            $token['type']  = self::T_COMMA;
                            $token['value'] = ',';
                            $token['line']  = $this->_line;
                            return $token;

                        case self::S_QUOTED_STRING:
                        case self::S_BRACED_STRING:
                            $token['value'] .= ',';
                            break;
                    }
                    break;

                case '#':
                    switch ($this->_state) {
                        case self::S_TYPE:
                            $this->_setState(self::S_DEFAULT);
                            break;

                        case self::S_EXPECT_STRING:
                            $token['type']  = self::T_CONCAT;
                            $token['value'] = '#';
                            $token['line']  = $this->_line;
                            return $token;

                        case self::S_QUOTED_STRING:
                        case self::S_BRACED_STRING:
                            $token['value'] .= '#';
                            break;

                        default:
                            break;
                    }
                    break;

                case "\n":
                    ++$this->_line;
                    // falls through

                case " ":
                case "\t":
                    switch ($this->_state) {
                        case self::S_STRING:
                            $this->_setState(self::S_EXPECT_STRING);
                            return $token;

                        case self::S_QUOTED_STRING:
                        case self::S_BRACED_STRING:
                            $token['value'] .= $char;
                            break;

                        default:
                            // skip spaces
                            break;
                    }
                    break;

                case '=':
                    switch ($this->_state) {
                        case self::S_TYPE:
                            $this->_setState(self::S_DEFAULT);
                            break;

                        case self::S_EXPECT_STRING:
                            $token['type']  = self::T_SEPARATOR;
                            $token['value'] = '=';
                            $token['line']  = $this->_line;
                            return $token;

                        case self::S_QUOTED_STRING:
                        case self::S_BRACED_STRING:
                            $token['value'] .= $char;
                            break;

                        case self::S_STRING:
                            $this->_setState(self::S_EXPECT_STRING);
                            $this->_ungetChar('=');
                            return $token;

                        default:
                            // skip
                            break;
                    }
                    break;

                case '"':
                    switch ($this->_state) {
                        case self::S_TYPE:
                            $this->_setState(self::S_DEFAULT);
                            break;

                        case self::S_EXPECT_STRING:
                            $this->_setState(self::S_QUOTED_STRING);
                            $token['type']  = self::T_QUOTED_STRING;
                            $token['value'] = '';
                            $token['line']  = $this->_line;
                            break;

                        case self::S_QUOTED_STRING:
                            $this->_setState(self::S_EXPECT_STRING);
                            return $token;
                    }
                    break;

                default:
                    switch ($this->_state) {
                        case self::S_TYPE:
                            if (ctype_alpha($char)) {
                                $token['value'] .= $char;
                            } else {
                                $this->_setState(self::S_DEFAULT);
                            }
                            break;

                        case self::S_EXPECT_STRING:
                        case self::S_STRING:
                            // be as liberal as possible when tokenizing undelimited string
                            if ($this->_state === self::S_EXPECT_STRING) {
                                $token['type']  = self::T_STRING;
                                $token['value'] = $char;
                                $token['line']  = $this->_line;
                            } else {
                                $token['value'] .= $char;
                            }
                            $this->_setState(self::S_STRING);
                            break;

                        case self::S_QUOTED_STRING:
                        case self::S_BRACED_STRING:
                            $token['value'] .= $char;
                            break;
                    }
                    break;
            }
        }

        if ($token['type'] !== null) { // something left in buffer
            return $token;
        }

        return false;
    }

    public function __destruct()
    {
        $this->closeStream();
    }
}
