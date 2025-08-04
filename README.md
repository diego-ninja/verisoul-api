# Verisoul API Client for PHP

[![Latest Version on Packagist](https://img.shields.io/packagist/v/diego-ninja/verisoul-api.svg?style=flat&color=blue)](https://packagist.org/packages/diego-ninja/verisoul-api)
[![Total Downloads](https://img.shields.io/packagist/dt/diego-ninja/verisoul-api.svg?style=flat&color=blue)](https://packagist.org/packages/diego-ninja/verisoul-api)
![PHP Version](https://img.shields.io/packagist/php-v/diego-ninja/verisoul-api.svg?style=flat&color=blue)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](https://opensource.org/licenses/MIT)
![GitHub last commit](https://img.shields.io/github/last-commit/diego-ninja/verisoul-api?color=blue)
[![wakatime](https://wakatime.com/badge/user/bd65f055-c9f3-4f73-92aa-3c9810f70cc3/project/3cc2ec60-a8b4-4ddc-aeac-ea78e37a094b.svg)](https://wakatime.com/badge/user/bd65f055-c9f3-4f73-92aa-3c9810f70cc3/project/3cc2ec60-a8b4-4ddc-aeac-ea78e37a094b)


[![Tests](https://github.com/diego-ninja/verisoul-api/actions/workflows/tests.yml/badge.svg)](https://github.com/diego-ninja/verisoaul-api/actions/workflows/tests.yml)
[![Static Analysis](https://github.com/diego-ninja/verisoul-api/actions/workflows/static-analysis.yml/badge.svg)](https://github.com/diego-ninja/verisoaul-api/actions/workflows/static-analysis.yml)
[![Code Style](https://github.com/diego-ninja/verisoul-api/actions/workflows/code-style.yml/badge.svg)](https://github.com/diego-ninja/verisoaul-api/actions/workflows/code-style.yml)

## Description

This is a PHP API client designed to interact with the Verisoul API. It provides a simple and effective way to access Verisoul's services, which include account management, session authentication, biometric identity verification, and list management.

The client is designed to be flexible and easy to use, with a focus on the immutability of Data Transfer Objects (DTOs) to ensure data integrity throughout your application.

This documentation has been generated almost in its entirety using 🦠 Claude 4 Sonnet based on source code analysis. Some sections may be incomplete, outdated or may contain documentation for planned or not-released features. For the most accurate information, please refer to the source code or open an issue on the package repository.

## ❤️ Features

* **Account Management**: Allows you to get, update, and delete user accounts, as well as query associated sessions and linked accounts.
* **Session Authentication**: Offers methods to authenticate sessions for both registered and anonymous users, providing a detailed risk analysis.
* **Identity Verification**: Includes functionalities for identity verification through facial recognition (FaceMatch) and identity document validation (IDCheck).
* **List Management**: Enables the creation and management of account lists to group users based on different criteria, such as whitelists, blacklists, or high-risk user lists.
* **Error Handling**: The client handles API errors in a structured manner, with custom exceptions that facilitate debugging and error flow control.

## 📦 Installation

To use this library, you can install it via Composer. First, make sure you have Composer installed in your project, and then run the following command:

```bash
composer require diego-ninja/verisoul-api
```

## 🎛️ Configuration

To start using the client, you need a Verisoul API key. You can get one from the Verisoul dashboard. Next, you can instantiate the main `Verisoul` client as follows:

```php
use Ninja\Verisoul\Verisoul;
use Ninja\Verisoul\Enums\VerisoulEnvironment;

$apiKey = 'your_api_key';
$environment = VerisoulEnvironment::Sandbox; // Or VerisoulEnvironment::Production

$verisoul = new Verisoul($apiKey, $environment);
```

## ⚙️ Basic Usage

#### Account Management

You can interact with user accounts using the accounts client:

```php
// Get account details
$accountDetails = $verisoul->accounts()->getAccount('account_id');

// Update an account
$updateData = ['email' => 'new_email@example.com'];
$updatedAccount = $verisoul->accounts()->updateAccount('account_id', $updateData);

// Delete an account
$deleteResponse = $verisoul->accounts()->deleteAccount('account_id');
```

#### Session Authentication

To authenticate a session and get a risk analysis, you can use the sessions client:

```php
use Ninja\Verisoul\DTO\UserAccount;

// User account data
$userAccount = new UserAccount(
    id: 'user_id',
    email: 'user@example.com'
);

// Authenticate the session
$authenticationResponse = $verisoul->sessions()->authenticate($userAccount, 'session_id');
```

#### Identity Verification with FaceMatch

If you need to verify a user's identity via facial recognition, you can use the FaceMatch client:

```php
// Start a FaceMatch session
$sessionResponse = $verisoul->faceMatch()->session();

// Once the user completes the process, you can verify the session
$verificationResult = $verisoul->faceMatch()->verify($sessionResponse->sessionId);
```

#### List Management

You can also manage lists of accounts to group users:

```php
// Create a new list
$verisoul->lists()->createList('risk_list', 'Users with suspicious activity');

// Add an account to the list
$verisoul->lists()->addAccountToList('risk_list', 'account_id');

// Get all accounts in a list
$accountsInList = $verisoul->lists()->getList('risk_list');
```

## 🙏 Credits

This project is developed and maintained by 🥷 [Diego Rin](https://diego.ninja) in his free time.

If you find this project useful, please consider giving it a ⭐ on GitHub!