<?php

/**
 * BibTeX tokenizer.
 */
class BiblioPHP_Bibtex_Tokenizer
{
    const S_DEFAULT         = 'DEFAULT';
    const S_COMMENT         = 'COMMENT';
    const S_TYPE            = 'TYPE';
    const S_EXPECT_STRING   = 'EXPECT_STRING';
    const S_STRING          = 'STRING';
    const S_QUOTED_STRING   = 'QUOTED_STRING';
    const S_BRACED_STRING   = 'BRACED_STRING';

    const T_TYPE            = 'T_TYPE';
    const T_STRING          = 'T_STRING';
    const T_CONCAT          = 'T_CONCAT';
    const T_COMMA           = 'T_COMMA';
    const T_SEPARATOR       = 'T_SEPARATOR';
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
            $this->_streamBuf = fgets($this->_stream);
            $this->_streamBufOffset = 0;
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

    protected function _nextToken()
    {
        $token = array(
            'type'  => null,
            'value' => null,
            'line'  => null,
        );

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
                            $token['value'] .= '@';
                            break;

                        default:
                            // all characters outside entries are ignored
                    }
                    break;

                case '{':
                    switch ($this->_state) {
                        case self::S_TYPE:
                            switch (strtolower($token['value'])) {
                                case '@comment':
                                    // If the entry type is @comment, it is not considered to be the start
                                    // of an entry. Actual rule is that everything from the @comment and to
                                    // the end of line is ignored. Read more:
                                    // http://maverick.inria.fr/~Xavier.Decoret/resources/xdkbibtex/bibtex_summary.html#comment
                                    $this->_setState(self::S_COMMENT);
                                    break;

                                default:
                                    $this->_setState(self::S_EXPECT_STRING);
                                    return $token;
                            }
                            break;

                        case self::S_EXPECT_STRING:
                            $this->_setState(self::S_BRACED_STRING);
                            $token['type']  = self::T_STRING;
                            $token['value'] = '';
                            $token['line']  = $this->_line;
                            $nestLevel = 0;
                            break;

                        case self::S_BRACED_STRING:
                            $token['value'] .= '{';
                            ++$nestLevel;
                            break;
                    }
                    break;

                case '}':
                    switch ($this->_state) {
                        case self::S_EXPECT_STRING:
                            $this->_setState(self::S_DEFAULT);
                            $token['type'] = self::T_END;
                            $token['line'] = $this->_line;
                            return $token;

                        case self::S_BRACED_STRING:
                            if ($nestLevel) {
                                $token['value'] .= '}';
                                --$nestLevel;
                            } else {
                                $this->_setState(self::S_EXPECT_STRING);
                                return $token;
                            }
                            break;

                        case self::S_QUOTED_STRING:
                            $token['value'] .= '}';
                            break;

                        default:
                            break;
                    }
                    break;

                case ',':
                    switch ($this->_state) {
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
                    switch ($this->_state) {
                        case self::S_COMMENT:
                            $this->_state = self::S_DEFAULT;
                            break;
                    }
                    // falls through

                case " ":
                case "\r":
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
                        case self::S_EXPECT_STRING:
                            $this->_setState(self::S_QUOTED_STRING);
                            $token['type']  = self::T_STRING;
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
                            }
                            break;

                        case self::S_EXPECT_STRING:
                        case self::S_STRING:
                            if (ctype_alnum($char) || (strpos('-:._', $char) !== false)) {
                                if ($this->_state === self::S_EXPECT_STRING) {
                                    $token['type']  = self::T_STRING;
                                    $token['value'] = $char;
                                    $token['line']  = $this->_line;
                                } else {
                                    $token['value'] .= $char;
                                }
                                $this->_setState(self::S_STRING);
                            }
                            break;

                        case self::S_QUOTED_STRING:
                        case self::S_BRACED_STRING:
                            $token['value'] .= $char;
                            break;
                    }
                    break;
            }
        }

        return false;
    }

    public function __destruct()
    {
        $this->closeStream();
    }
}
