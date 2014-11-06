<?php

class BiblioPHP_PublicationAuthor
{
    protected $_firstName;

    protected $_lastName;

    protected $_suffix;

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

    public function setLastName($lastName)
    {
        $this->_lastName = trim($lastName);
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
        $string = $this->_lastName;

        if ($this->_firstName) {
            $string .= ', ' . $this->_firstName;
        }

        if ($this->_suffix) {
            $string .= ', ' . $this->_suffix;
        }

        return $string;
    }
}
