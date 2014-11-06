<?php

/**
 * BibTeX tokenizer.
 */
class BiblioPHP_Bibtex_Tokenizer
{
    const S_DEFAULT         = 'DEFAULT';
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

    protected $_stream;

    protected $_streamBuf;

    protected $_streamBufOffset;

    protected $_ungetBuf;

    protected $_state;

    protected $_line;

    protected $_tokenBuf;

    protected $_token;

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
        $meta = @stream_get_meta_data($stream);
        if (empty($meta)) {
            throw new InvalidArgumentException('Invalid stream resource supplied');
        }

        $this->_stream = $stream;
        $this->_streamBuf = '';
        $this->_streamBufOffset = 0;

        $this->_line = 1;
        $this->_tokenBuf = '';
        $this->_token = null;
        $this->_state = self::S_DEFAULT;

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
        $this->_tokenBuf = '';
        $line = $this->_line;
        return $this->_token = compact('type', 'value', 'line');
    }

    public function getToken()
    {
        return $this->_token;
    }

    public function nextToken()
    {
        while (($char = $this->_getChar()) !== false) {
            switch ($char) {
                case '@':
                    switch ($this->_state) {
                        case self::S_DEFAULT:
                            $this->_setState(self::S_TYPE);
                            $this->_tokenBuf = '@';
                            break;

                        case self::S_QUOTED_STRING:
                        case self::S_BRACED_STRING:
                            $this->_tokenBuf .= '@';
                            break;
                    }
                    break;

                case '{':
                    switch ($this->_state) {
                        case self::S_TYPE:
                            switch (strtolower($this->_tokenBuf)) {
                                case 'comment':
                                    $this->_setState(self::S_DEFAULT);
                                    break;

                                default:
                                    $this->_setState(self::S_EXPECT_STRING);
                                    return $this->_setToken(self::T_TYPE, $this->_tokenBuf);
                            }
                            break;

                        case self::S_EXPECT_STRING:
                            $this->_setState(self::S_BRACED_STRING);
                            $nestLevel = 0;
                            break;

                        case self::S_BRACED_STRING:
                            $this->_tokenBuf .= '{';
                            ++$nestLevel;
                            break;
                    }
                    break;

                case '}':
                    switch ($this->_state) {
                        case self::S_EXPECT_STRING:
                            $this->_setState(self::S_DEFAULT);
                            return $this->_setToken(self::T_END);

                        case self::S_BRACED_STRING:
                            if ($nestLevel) {
                                $this->_tokenBuf .= '}';
                                --$nestLevel;
                            } else {
                                $this->_setState(self::S_EXPECT_STRING);
                                return $this->_setToken(self::T_STRING, $this->_tokenBuf);
                            }
                            break;

                        case self::S_QUOTED_STRING:
                            $this->_tokenBuf .= '}';
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
                            return $this->_setToken(self::T_STRING, $this->_tokenBuf);

                        case self::S_EXPECT_STRING:
                            return $this->_setToken(self::T_COMMA, ',');

                        case self::S_QUOTED_STRING:
                        case self::S_BRACED_STRING:
                            $this->_tokenBuf .= ',';
                            break;
                    }
                    break;

                case '#':
                    switch ($this->_state) {
                        case self::S_EXPECT_STRING:
                            return $this->_setToken(self::T_CONCAT, '#');

                        case self::S_QUOTED_STRING:
                        case self::S_BRACED_STRING:
                            $this->_tokenBuf .= '#';
                            break;

                        default:
                            break;
                    }
                    break;

                case "\n":
                    ++$this->_line;
                    // falls through

                case " ":
                case "\r":
                case "\t":
                    switch ($this->_state) {
                        case self::S_STRING:
                            $this->_setState(self::S_EXPECT_STRING);
                            return $this->_setToken(self::T_STRING, $this->_tokenBuf);

                        case self::S_QUOTED_STRING:
                        case self::S_BRACED_STRING:
                            $this->_tokenBuf .= $char;
                            break;

                        default:
                            // skip spaces
                            break;
                    }
                    break;

                case '=':
                    switch ($this->_state) {
                        case self::S_EXPECT_STRING:
                            return $this->_setToken(self::T_SEPARATOR, '=');

                        case self::S_QUOTED_STRING:
                        case self::S_BRACED_STRING:
                            $this->_tokenBuf .= $char;
                            break;

                        case self::S_STRING:
                            $this->_setState(self::S_EXPECT_STRING);
                            $this->_ungetChar('=');
                            return $this->_setToken(self::T_STRING, $this->_tokenBuf);

                        default:
                            // skip
                            break;
                    }
                    break;

                case '"':
                    switch ($this->_state) {
                        case self::S_EXPECT_STRING:
                            $this->_setState(self::S_QUOTED_STRING);
                            break;

                        case self::S_QUOTED_STRING:
                            $this->_setState(self::S_EXPECT_STRING);
                            return $this->_setToken(self::T_STRING, $this->_tokenBuf);
                    }
                    break;

                default:
                    switch ($this->_state) {
                        case self::S_TYPE:
                            if (ctype_alpha($char)) {
                                $this->_tokenBuf .= $char;
                            }
                            break;

                        case self::S_EXPECT_STRING:
                        case self::S_STRING:
                            if (ctype_alnum($char) || (strpos('-:._', $char) !== false)) {
                                $this->_tokenBuf .= $char;
                                $this->_setState(self::S_STRING);
                            }
                            break;

                        case self::S_QUOTED_STRING:
                        case self::S_BRACED_STRING:
                            $this->_tokenBuf .= $char;
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
