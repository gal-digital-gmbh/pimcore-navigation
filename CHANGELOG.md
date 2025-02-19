# Changelog

## 3.1.1

* Migrate menu renderer bugfix of Version 2.1.0 to Version 3 
* Run php-cs-fixer

## 3.1.0

* Add support for `rootCallback` option that was introduced in Pimcore 11.2.0

## 3.0.1

* Change return types of navigation models from `self` to `static`

## 3.0.0

* Update bundle to support Pimcore 11
* Make classes final
* Update PHPStan
* Update PHP-CS-Fixer

## 2.0.2

* Stop iterating breadcrumb pages when no more parent is found

## 2.0.1

* Fixes setting of parent page when using addRoot option

## 2.0.0

* Adds support for `markActiveTrail` option
* Removes deprecated call to `\Pimcore\Navigation\Builder::getNavigation`

## 1.0.0

First Release
