ESP
===

# Table of Contents

- [What is ESP](#what-is-esp)
- [Installation](#installation)
- [Usage](#usage)
  - [Commands](#commands)
    - [Setup](#setup)
    - [TLD](#tld-)
    - [Site](#site)

Environment Setup Program

# What is ESP?

***E***nvironment ***S***etup ***P***rogram is a small program using [Laravel Zero](https://laravel-zero.com/) 
to quickly set up a PHP development environment by installing NginX, PHP 8.2 
and EasyRSA.

ESP will also generate nginx configurations file, Laravel applications and 
basic docker configurations for a project.

I developed it because I was unhappy with [Valet-Linux](https://github.com/cpriego/valet-linux)
breaking systemd under WSL2 and [Laravel Sail](https://laravel.com/docs/sail) 
requires too much tinkering for me to be truly happy with it and thus ESP was 
born.

# Requirements

1. Windows 10 or Windows 11.
2. WSL2 + Ubuntu.
3. Docker Desktop (for Windows with WSL Integration enabled.)
4. PHP 8.2 (at least `php8.2-cli`).
5. A towel.

# Installation

todo: composerable installation?
```sh
curl -sS <todo: download url>
sudo mv esp /usr/local/bin/esp
```

# Usage

## Commands
### Setup

`setup` - Installs nginx, php8.2 and EasyRSA.

### TLD 

`tld` - Allows you to view the current TLD.

`tld <new-tld>` - Allows you to update the current TLD. 

**You do not need to supply a period (.) for the new TLD.**

### Site
`site:create` - Create a new site.

`site:list` - List all registered sites.
