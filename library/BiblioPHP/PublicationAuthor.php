<?php

class BiblioPHP_PublicationAuthor
{
    /**
     * @var string
     */
    protected $_firstName;

    /**
     * @var string
     */
    protected $_lastName;

    /**
     * @var string
     */
    protected $_suffix;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->fromArray(array_merge(
            array('lastName' => null),
            $data
        ));
    }

    public function getFirstName()
    {
        return $this->_firstName;
    }

    public function setFirstName($firstName)
    {
        $this->_firstName = trim($firstName);
        return $this;
    }

    public function getLastName()
    {
        return $this->_lastName;
    }

    /**
     * @param $lastName
     * @return $this
     * @throws Exception
     */
    public function setLastName($lastName)
    {
        $lastName = trim($lastName);
        if (!strlen($lastName)) {
            throw new Exception('Author last name cannot be empty');
        }
        $this->_lastName = $lastName;
        return $this;
    }

    public function getSuffix()
    {
        return $this->_suffix;
    }

    public function setSuffix($suffix)
    {
        $this->_suffix = trim($suffix);
        return $this;
    }

    public function toString()
    {
        return implode(
            ', ',
            array_filter($this->toArray(), 'strlen')
        );
    }

    /**
     * @param array $data
     * @return $this
     */
    public function fromArray(array $data)
    {
        foreach ($data as $key => $value) {
            $method = 'set' . $key;
            if (method_exists($this, $method)) {
                $this->{$method}($value);
            }
        }
        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array(
            'lastName'  => $this->getLastName(),
            'firstName' => $this->getFirstName(),
            'suffix'    => $this->getSuffix(),
        );
    }

    /**
     * @param string|array|BiblioPHP_PublicationAuthor $data
     * @return BiblioPHP_PublicationAuthor
     */
    public static function factory($data)
    {
        if ($data instanceof BiblioPHP_PublicationAuthor) {
            return $data;
        }

        if (!is_array($data)) {
            // the author name must be in the following syntax:
            // Lastname, Firstname, Suffix
            // Lastname is the only required part.

            // make sure name does not contain colon

            $string = (string) $data;
            $string = trim($string, ", \r\n\t");
            $string = str_replace(';', '', $string);

            $parts = preg_split('/\s*,\s*/', $string);
            $parts = array_slice($parts, 0, 3);

            $data = array(
                'lastName'  => $parts[0],
                'firstName' => isset($parts[1]) ? $parts[1] : null,
                'suffix'    => isset($parts[2]) ? $parts[2] : null,
            );
        }

        return new self($data);
    }
}
