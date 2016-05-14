<?php

class BiblioPHP_NameParser
{
    /**
     * Parse string into a list of authors
     *
     * @param $string
     * @return array
     */
    public function parse($string)
    {
        // names can be separated either by 'and' and by semicolons
        $state = 'default';

        foreach ($tokens as $token) {

            switch (true) {
                case 'Jr':
                case 'Jr.':
                case 'Junior':
                case 'Sr':
                case 'Sr.':
                case 'Senior':
                case 'II':
                case 'III':
                    break;

            }
        }

        // if there is only one word, it is taken as the Last part, even if it
        // starts with a lower letter

        // the von part takes as many words as possible, provided that its first
        // and last words begin with a lowercase letter,
        // however the Last part cannot be empty

        // if all the words begin with an uppercase letter, the last word is the
        // Last component, and the First part groups the other words

        // first, von, last, suffix

        // Suffix Jr/Sr/II/III/MD

        // von part starts with lower letters
        // handle tilde
        // first1 first2 first3 last
        // if single letter -> next word after single letter is expected to be either
        // another single letter, or von part or last name
        // if multiple last names
        // { <- starts a single token  } <- ends single token

        // et al, and others
    }
}
