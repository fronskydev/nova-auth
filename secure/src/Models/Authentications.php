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
        do {
            $unique_identifier = generateRandomString(32);
            $exists = false;

            foreach ($this->all() as $row) {
                if (decryptText($row["unique_identifier"], "_unique_identifier") === $unique_identifier) {
                    $exists = true;
                    break;
                }
            }

        } while ($exists);

        return $unique_identifier;
    }
}
