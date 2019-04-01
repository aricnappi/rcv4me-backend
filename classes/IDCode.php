<?php
/**
 * A class that generates a unique poll ID code.
 */
class IDCode {
    /**
     * A mysqli object that connects to the database.
     * @var mysqli 
     */
    private $mysqli;
    
    /**
     * The unique poll ID code represented by this object.
     * @var string 
     */
    private $IDCodeString;
    
    /**
     * Create a new IDCode object.
     * @param mysqli $mysqli A mysqli object with an open connection to the database.
     */
    public function __construct(mysqli $mysqli) {
        $this->mysqli = $mysqli;
        $this->IDCodeString = $this->uniqueID();
    }
    
    /**
     * Get the poll ID code as a string.
     * @return string The poll ID code as a string.
     */
    public function getIDCodeString() : string {
        return $this->IDCodeString;
    }
    
    /**
     * Generate a random string consisting of five lower-case letters.
     * @return string The generated code.
     */
    private function randomCode() : string {
        //  Generate a random int from 0 to 25
        //  turn into ASCII decimal code for letters a-z by adding 97
        //  convert result to character
        //  cancat character to end of code string
        //  repeat 4 more times to generate entire code
        $code = '';
        for ($i = 0; $i < 5; $i++) {
            $randInt = rand(0, 25);
            $asciiDec = $randInt + 97;
            $char = chr($asciiDec);
            $code = $code . $char;
        }
        return $code;
    }

    /**
     * Takes a lower-case letter and increments it by one letter, modulo 'z'+1.
     * @example
     * incrementLetter('b');  // returns 'c'
     * incrementLetter('z');  // returns 'a'
     * @param string $char The lower-case letter to be incremented.
     * @return string The new letter after incrementing.
     */
    private function incrementLetter (string $char) : string {
        // Convert the letter to ASCII decimal
        $dec = ord($char);
        // Normalize
        $normal = $dec - 97;
        // Increment modulo 26
        $incremented = ($normal + 1) % 26;
        // Convert to ASCII decimal
        $newDec = $incremented + 97;
        // Convert to letter
        $newChar = chr($newDec);
        // Return
        return $newChar;
    }

    /**
     * Increments the given ID code by one character, where 'zzzzz'+1='aaaaa'.
     * @example
     * incrementCode('aaaaa');  // returns 'aaaab'
     * incrementCode('abcde');  // returns 'abcdf'
     * incrementCode('zzzzz');  //returns 'aaaaa'
     * @param string $code A five-character string consisting of digits and/or 
     *     lower-case letters.
     * @return string A new code string.
     */
    private function incrementCode(string $code) : string {
        $index = 5;
        do {
            // Go to next letter in code
            $index--;
            // Get that letter
            $char = $code[$index];
            // Increment it
            $newChar = $this->incrementLetter($char);
            // Replace the old letter with the incremented letter
            $code[$index] = $newChar;
        // if the character that was incremented was 'z' and we have not hit the end of the code string, do it again
        } while (($char === 'z') && ($index > 0));
        return $code;
    }
    
    /**
     * Determines if the given ID code is unique among the poll ID codes already in
     * the database.
     * @param string $code The code to test.
     * @return bool Whether the code is unique.
     */
    private function codeIsUnique(string $code) : bool {
        // Get the IDs of the already existing polls
        $sql = 'SELECT poll_id FROM `polls` WHERE 1';
        $result = $this->mysqli->query($sql);
        $ids = $result->fetch_all(MYSQLI_NUM);  // use syntax $ids[$i][0] to get the $i'th id
        // Check if the given ID code is unique among the fetched IDs
        // Initialize return variable
        $isUnique = true;
        // for each row fetched from the database
        foreach($ids as $row) {
            // the ID is the first entry in the row
            $id = $row[0];
            // If the given ID code matches an ID code already in the database
            if ($id === $code) {
                // the given ID code is not unique
                $isUnique = false;
                break;
            }
        }
        // Return whether the given ID is unique
        return $isUnique;
    }

    /**
     * Generate a five-character ID code that is unique among the IDs of the polls
     * already in the database.
     * @return string A unique five-character ID code.
     */
    private function uniqueID() : string {
        // Generate a random ID code
        $code = $this->randomCode();
        // If the code is not unique
        while (!$this->codeIsUnique($code)) {
            // Increment it
            $code = $this->incrementCode($code);
        }
        // The code is now guaranteed to be unique if there are any unused codes left; return it
        return $code;
    }

}
