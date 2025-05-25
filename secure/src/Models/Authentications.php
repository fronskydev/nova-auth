<?php

namespace src\Models;

use src\Abstracts\Model;

class Authentications extends Model
{
    protected function getTable(): string
    {
       return "authentications";
    }

    /**
     * Generates a unique identifier.
     *
     * This method generates a unique 32-character string identifier. It ensures the uniqueness
     * by checking the database for existing identifiers and regenerating the string if a duplicate is found.
     *
     * @return string The unique identifier.
     */
    public function generateUniqueIdentifier(): string
    {
        $unique_identifier = "";
        $found = true;
        while ($found) {
            $unique_identifier = generateRandomString(32);
            $row = $this->findBy("unique_identifier", $unique_identifier);
            if (!$row) {
                $found = false;
            }
        }

        return $unique_identifier;
    }
}
