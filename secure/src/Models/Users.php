<?php

namespace src\Models;

use src\Abstracts\Model;

class Users extends Model
{
    protected function getTable(): string
    {
        return "users";
    }

    /**
     * Retrieves user details based on the provided identifier.
     *
     * This method attempts to find a user by their ID, username, or email.
     * It returns the first matching user details as an associative array.
     *
     * @param string $uid The user identifier, which can be an ID, username, or email.
     * @return array The user details as an associative array, or an empty array if no user is found.
     */
    public function getUserDetails(string $uid): array
    {
        $user = $this->findBy("id", $uid);
        if ($user) {
            return $user[0];
        }

        $users = $this->all();
        foreach ($users as $user) {
            if (decryptText($user["username"], "_username") === $uid || decryptText($user["email"], "_email") === $uid) {
                return $user;
            }
        }

        return [];
    }
}
