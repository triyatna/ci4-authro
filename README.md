# CodeIgniter 4 Authentication Login Regiter and with API Auth JWT CI4 (Development)

Codeigniter 4 Authentication Templates include Login, Register, Forgot, Verify with Email, and API Auth with JWT

## What is CodeIgniter?

CodeIgniter is a PHP full-stack web framework that is light, fast, flexible and secure.
More information can be found at the [official site](https://codeigniter.com).

This repository holds a composer-installable app starter.
It has been built from the
[development repository](https://github.com/codeigniter4/CodeIgniter4).

More information about the plans for version 4 can be found in [CodeIgniter 4](https://forum.codeigniter.com/forumdisplay.php?fid=28) on the forums.

The user guide corresponding to the latest version of the framework can be found
[here](https://codeigniter4.github.io/userguide/).

## Installation

```
git clone https://github.com/triyatna/ci4-dev
cd ci4-dev
```

## Setup

Copy `env` to `.env` and tailor for your app, specifically the baseURL
and any database settings. Then generate an encryption token with `php spark key:generate` and also add the jwt secret key to the .env file

## Migration and Seed

```
php spark migrate
php spark db:seed Setting
```

## How to use

To get notifications via email, you can set your smtp email in the settings table with the line name `smtp` with the contents `{"host":"your smtp host","security":"ssl or tls","port":"your smtp port","user":"your smtp username","pass":"your smtp password"}` according to what you have.

You can register, activate account, log in, forgot password, and reset password.

## Assets

We use premium template assets from vuexy and their resources for the application part, and use premium assets for the landing page as well

## Important Change with index.php

`index.php` is no longer in the root of the project! It has been moved inside the _public_ folder,
for better security and separation of components.

This means that you should configure your web server to "point" to your project's _public_ folder, and
not to the project root. A better practice would be to configure a virtual host to point there. A poor practice would be to point your web server to the project root and expect to enter _public/..._, as the rest of your logic and the
framework are exposed.

**Please** read the user guide for a better explanation of how CI4 works!

## Repository Management

We use GitHub issues, in our main repository, to track **BUGS** and to track approved **DEVELOPMENT** work packages.
We use our [forum](http://forum.codeigniter.com) to provide SUPPORT and to discuss
FEATURE REQUESTS.

This repository is a "distribution" one, built by our release preparation script.
Problems with it can be raised on our forum, or as issues in the main repository.

## Server Requirements

PHP version 7.4 or higher is required, with the following extensions installed:

- [intl](http://php.net/manual/en/intl.requirements.php)
- [mbstring](http://php.net/manual/en/mbstring.installation.php)

Additionally, make sure that the following extensions are enabled in your PHP:

- json (enabled by default - don't turn it off)
- [mysqlnd](http://php.net/manual/en/mysqlnd.install.php) if you plan to use MySQL
- [libcurl](http://php.net/manual/en/curl.requirements.php) if you plan to use the HTTP\CURLRequest library

---

Development by [TY Studio DEV](https://tystudiodev.com)
