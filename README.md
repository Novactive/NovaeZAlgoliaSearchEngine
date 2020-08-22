# Novactive eZ Algolia Search Engine Bundle

----

Novactive eZ Algolia Search Engine is an eZ Platform bundle to provide Algolia search integration.
This bundle allows to use the Algolia Search Engine to index the data and then do the advanced search.

# Install

## Requirements

* eZ Platform 3.1+
* PHP 7.3

## Installation steps


Add the following to your composer.json and run `php composer.phar update novactive/ezalgoliasearchengine algolia/algoliasearch-client-php` to refresh dependencies:

```json
# composer.json

"require": {
    "novactive/ezalgoliasearchengine": "^1.0.0",
    "algolia/algoliasearch-client-php": "^2.7"
}
```

The Algolia Application should be then created on the website with generated App ID and API secret key: https://www.algolia.com/

After having installed the package the following command should be run to init the Indexes on Algolia and set up the search attributes:
`bin/console nova:ez:algolia:indexes:setup`

----

## Register the bundle

If Symfony Flex did not do it already, activate the bundle in `config\bundles.php` file.

```php
// config\bundles.php
<?php

return [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    ...
    Novactive\Bundle\eZAlgoliaSearchEngine\NovaEzAlgoliaSearchEngine::class => ['all' => true],
];
```