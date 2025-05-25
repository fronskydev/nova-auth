<table align="center"><tr><td align="center" width="9999">
<img src="public/assets/images/logo.png" align="center" width="150" alt="Nova Logo">

<p align="center">
  <img src="https://img.shields.io/badge/version-v1.0.0-blue.svg" align="center" alt="Version Badge"/>
  <img src="https://img.shields.io/badge/license-MIT-green.svg" align="center" alt="License Badge"/>
</p>

# About Nova Auth

Nova Auth is an advanced user authentication component for the Nova Framework.

</td></tr></table>

## Table of Contents
- [Requirements](#requirements)
- [Installation](#installation)
  - [Clone the Repository](#clone-the-repository)
  - [Configuration](#configuration)
  - [Install the Auth Component in your Nova Project](#install-the-auth-component-in-your-nova-project)
  - [Set Up the Database](#set-up-the-database)
- [Usage](#usage)
- [License](#license)

## Requirements

- PHP 8.3 or higher
- Composer
- MySQL (or other supported database for Doctrine DBAL)
- A Project using the Nova Framework

## Installation

Follow these steps to get started with Nova Auth.

### Clone the Repository

Run this command to clone the repository:

```bash
git clone https://github.com/fronskydev/nova-auth.git
```

### Configuration

You can configure the Auth component by editing the [secure/src/Components/Auth/AuthComponent.php](secure/src/Components/Auth/AuthComponent.php) file. At the beginning of the file you can see the settings array to modify to your liking.

### Install the Auth Component in your Nova Project

Copy the secure and public directories from the cloned repository to your Nova project root directory, if asked to overwrite, choose yes to all except the [secure/src/Components/map.php](secure/src/Components/map.php) file.

In the [secure/src/Components/map.php](secure/src/Components/map.php) file of your Nova project, add the following code to array in the return:

```php
    "auth" => "src\Components\Auth\AuthComponent",
```

### Set Up the Database

Create a new database in your MySQL server and run the [nova-auth.sql](nova-auth.sql) file to create the necessary tables.

Make sure the database is correctly set up in your .env file, the example file can be found at: [.env.example](https://gitlab.com/fronsky-development/nova/-/raw/main/secure/.env.example).

## Usage

We created an easy to use AuthUtil class in the Nova Auth component to help you with user authentication. All methods are static, so you can use them without creating an instance of the class.

Here is an example of the AuthUtil class methods:

```php
use src\Components\Auth\AuthUtil;

// If the user is logged in it will return true, otherwise false
AuthUtil::isClientLoggedIn();

// If the user is logged in it will return an array with the user data, otherwise it will return an empty array
AuthUtil::getUserData();

// This will log the user out (it removes all user cookies and/or session data)
AuthUtil::logoutClient();

// This will create/replace the user data in the session (it will not save it to the database)
AuthUtil::createUserData([
  "id" => 1,
  "username" => "admin",
  "email" => "admin@example.com",
  "full_name" => "Nova Admin",
  "is_admin" => 1
]);

// If the user is logged in and has the role 'admin' it will return true, otherwise false
AuthUtil::isAdmin();
```

## License

Nova is open-source software licensed under the [MIT](LICENSE) license.
