<?php

namespace src\Components\Auth;

use Random\RandomException;

class AuthUtil
{
    /**
     * Checks if the client is currently logged in.
     *
     * This method determines if the client is logged in by checking if user data is available.
     *
     * @return bool True if the client is logged in, false otherwise.
     */
    public static function isClientLoggedIn(): bool
    {
        return !empty(self::getUserData());
    }

    /**
     * Retrieves user data from either cookies or session.
     *
     * This method attempts to retrieve user data first from cookies, and if not found,
     * it then attempts to retrieve it from the session. If no user data is found in either,
     * it returns an empty array.
     *
     * @return array The user data as an associative array, or an empty array if no data is found.
     */
    public static function getUserData(): array
    {
        return self::retrieveUserData("cookie") ?? self::retrieveUserData("session") ?? [];
    }

    /**
     * Retrieves user data from the specified source.
     *
     * This method attempts to retrieve user data from either cookies or session based on the provided source.
     * If the data is found, it is decrypted and returned as an associative array. If the data is not found or
     * decryption fails, the client is logged out and null is returned.
     *
     * @param string $source The source from which to retrieve user data. Valid values are "cookie" and "session".
     * @return array|null The decrypted user data as an associative array, or null if no data is found or decryption fails.
     */
    private static function retrieveUserData(string $source): ?array
    {
        $value = null;

        if ($source === "cookie" && isCookieActive("user")) {
            $value = getCookieValue("user");
        } elseif ($source === "session" && isSessionActive("user")) {
            $value = getSessionValue("user");
        }

        if (!$value) {
            return null;
        }

        $data = decryptData($value);
        if (empty($data)) {
            self::logoutClient();
            return null;
        }

        return $data;
    }

    /**
     * Logs out the client by deleting session and cookie data.
     *
     * This method removes the user's session and cookie data to log out the client.
     *
     * @return void
     */
    public static function logoutClient(): void
    {
        deleteSession("user");
        deleteCookie("user");
    }

    /**
     * Creates user data and stores it in either a cookie or session.
     *
     * This method encrypts the provided user data and stores it in a cookie if cookies are accepted,
     * otherwise, it stores the data in the session.
     *
     * @param array $data The user data to be encrypted and stored.
     * @return void
     * @throws RandomException If encryption fails.
     */
    public static function createUserData(array $data): void
    {
        $encryptedData = encryptData($data);

        if (self::isClientLoggedIn()) {
            self::logoutClient();
        }

        if (self::shouldStoreInCookie()) {
            setCookieValue("user", $encryptedData);
        } else {
            setSessionValue("user", $encryptedData);
        }
    }

    /**
     * Determines if cookies should be used to store user data.
     *
     * This method checks if the "cookies_accepted" cookie is active and its value is "yes".
     *
     * @return bool True if cookies should be used to store user data, false otherwise.
     */
    private static function shouldStoreInCookie(): bool
    {
        return isCookieActive("cookies_accepted") && getCookieValue("cookies_accepted") === "yes";
    }

    /**
     * Checks if the current user is an administrator.
     *
     * This method retrieves the user data and checks if the "is_admin" flag is set to true.
     *
     * @return bool True if the user is an administrator, false otherwise.
     */
    public static function isAdmin(): bool
    {
        $userData = self::getUserData();
        return $userData["is_admin"] === true;
    }
}
