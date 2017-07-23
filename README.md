Botty - The TYPO3 Slack Bot
===========================

[![Build Status](https://travis-ci.org/NeoBlack/T3Bot.svg)](https://travis-ci.org/NeoBlack/T3Bot)
[![Build Status](https://scrutinizer-ci.com/g/NeoBlack/T3Bot/badges/build.png?b=master)](https://scrutinizer-ci.com/g/NeoBlack/T3Bot/build-status/master)
[![Code Coverage](https://scrutinizer-ci.com/g/NeoBlack/T3Bot/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/NeoBlack/T3Bot/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/NeoBlack/T3Bot/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/NeoBlack/T3Bot/?branch=master)
[![GitHub version](https://badge.fury.io/gh/NeoBlack%2FT3Bot.svg)](http://badge.fury.io/gh/NeoBlack%2FT3Bot) 
[![Support](https://img.shields.io/badge/support-slack-blue.svg)](https://typo3.slack.com/messages/t3bot/) 
[![Documentatiom](https://img.shields.io/badge/documentation-wiki-blue.svg)](https://wiki.typo3.org/T3Bot)

**Hi, I am Botty**
I am in each channel on [typo3.slack.com](http://typo3.slack.com/), even if you do not see me. Talk to me by start a message with @T3Bot or with the command prefix.

A list of the commands I can understand is documented in the [Wiki](http://wiki.typo3.org/T3Bot).

## Developer Notes

If you want to contribute, fork this repository and send a pull request.

## Requirements

This project requires PHP 7.0 

## Setup

```
# copy env file and adjust 
cp .env.example .env

# composer install
composer install

# database mgiration
./bin/doctrine-migrations --configuration=Build/migrations.xml --db-configuration=Build/migrations-db.php migrations:migrate
```

## Unit Test

```
./bin/phpunit -c Build/UnitTests.xml
```

## Coverage report

```
rm -rf public/docs
./bin/phpunit -c Build/UnitTests.xml --coverage-html public/docs
```
