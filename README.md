# Novactive eZ Algolia Search Engine Bundle

----

Novactive eZ Algolia Search Engine is an eZ Platform bundle to provide Algolia search integration.
This bundle allows you to use the Algolia Search Engine to index the data and then do the search.
All basic cases that are used by Ez Platform to retrieve the contents and locations are managed by the bundle behind the scene. 
It's done by converting the Ez Query instance (including Criterions, Sort Clauses, Facet Builders) into the request that is sent to Algolia.

# Install

## Requirements

* eZ Platform 3.1+
* PHP 7.3

## Installation steps


Add the following to your composer.json and run `php composer.phar update novactive/ezalgoliasearchengine algolia/algoliasearch-client-php` to refresh dependencies:

```json
# composer.json

"require": {
    "novactive/ezalgoliasearchengine": "^1.0.0"
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

### Add routes

```yaml
_novaezalgoliasearchengine_routes:
    resource: '@NovaEzAlgoliaSearchEngine/Resources/config/routing.yaml'
```

### ENV variables

The `SEARCH_ENGINE` environment variable  should be set to `algolia`

### Configuration

```yaml
nova_ezalgoliasearchengine:
    system:
        default:
            index_name_prefix: PREFIX
            app_id: APPLICATION_ID
            api_secret_key: ADMIN_API_KEY
            api_search_only_key: SEARCH_ONLY_PAI_KEY

webpack_encore:
    builds:
        nova_ezalgolia: "%kernel.project_dir%/public/assets/nova_ezalgolia/build"

framework:
    assets:
        packages:
            nova_ezalgolia:
                json_manifest_path: '%kernel.project_dir%/public/assets/nova_ezalgolia/build/manifest.json'

```

The Application Id and the Keys can be found on the **API Keys** page of the Algolia dashboard.

## Usage

#### Query Criterions
All the Criterions created inside the Ez Queries as a filter or query field are transformed into the Algolia filter string like 

`doc_type_s:content AND (content_type_identifier_s:"article")`;

Some of the main [Criterions](https://doc.ezplatform.com/en/master/guide/search/search_criteria_reference/) are not implemented:

- IsUserBased
- IsUserEnabled
- MapLocationDistance
- ObjectStateIdentifier
- Sibling
- UserEmail
- UserId
- UserLogin

In particular the **MapLocationDistance** Criterion is not implemented because the Algolia geo location specifics doesn't allow to manage multiple fields of this type within the same document.
Filtering by location can be done using the specific request options of the Algolia Search method.
Here is the example:
```php
        $query->setRequestOption('aroundLatLng', '37.7512306, -122.4584587');
        $query->setRequestOption('aroundRadius', 3000);
```

The documentation on this subject can be found [here](https://www.algolia.com/doc/guides/managing-results/refine-results/geolocation/how-to/filter-results-around-a-location/).

The **_geoloc** attribute is already included in the Algolia document by default for the contents that have the Map Location fields.

Another constraint is inside the Logical operators. They are:

- FullText Criterion cannot be inside LogicalNot or LogicalOr operator because it's moved to the query string of the Algolia request which is performed anyway.
- AND operator cannot be inside LogicalNot criterion;
- AND/OR operator cannot be inside LogicalOr criterion.

You can find more info on the specifics of boolean filters of Algolia [here](https://www.algolia.com/doc/api-reference/api-parameters/filters/#boolean-operators).

The full Algolia info about how the filtering works can be found [here](https://www.algolia.com/doc/api-reference/api-parameters/filters/).

#### Using the Query Factory to generate the custom queries

If you want to create more specific custom request that can be achieved with the Search Query Factory service
[`Novactive\Bundle\eZAlgoliaSearchEngine\Core\Search\SearchQueryFactory`](https://github.com/Novactive/NovaeZAlgoliaSearchEngine/blob/master/bundle/Core/Search/SearchQueryFactory.php).
When using it all the request parameters should be specified manually, i.e search term, filters, facets etc. like in the following example:

```php
        $query = $this->searchQueryFactory->create(
            'term',
            'content_type_identifier_s:"article"',
            ['doc_type_s']
        );
        $query->setRequestOption('attributesToRetrieve', ['content_name_s']);
```
Then the created Query instance should be passed to one of the methods of 
[`Novactive\Bundle\eZAlgoliaSearchEngine\Core\Search\Search`](https://github.com/Novactive/NovaeZAlgoliaSearchEngine/blob/master/bundle/Core/Search/Search.php) service depending on the type of search:
- findContents
- findLocations
- find (for the raw search).

#### Sorting
To be able to sort the documents retrieved from Algolia via Search method the Replicas are created and used.
Each Replica is kind of Index duplicate based on specific attribute on which the documents are sorted.
The attributes that are used to generate the Replicas can be set in the `nova_ezalgoliasearchengine.default.attributes_for_replicas` config parameter.
Then while creating the custom queries the Replica can be specified as an option:
```php
        $query = $this->searchQueryFactory->create(
            '',
            'content_language_codes_ms:"eng-GB"',
        );
        $query->setReplicaByAttribute('location_id_i');
```
When using the Ez Query the **sortClauses** field assigned to the Query instance is converted into the Replica key.

#### Reindexing

All the data (Content and Location items) are pushed to the Algolia Index using the `bin/console ezplatform:reindex` command.
All of them (except those specified in _exclude_content_types_ parameter or only those included in _include_content_types_ parameter) 
are converted to a specific format and sent to Algolia via saveObjects method.
Also each particular Content of allowed Content Type (included or not excluded) is pushed to the Index once published on Ez Platform admin dashboard.

#### Front End Implementation

The Search page with `/search` url is overridden with custom Search controller implemented in the Bundle.
The specific routing configuration is used for that:

```yaml
ezplatform.search:
    path: /search
    methods: ['GET']
    defaults:
        _controller: 'Novactive\Bundle\eZAlgoliaSearchEngine\Controller\SearchController::searchAction'
```

The source code of the Front End implementation with React components can be found in the `Resources/assets/js/search.jsx` file.
All the main widgets are included and can be used as examples of their implementation.
The information on React InstantSearch component basic installation and widgets showcases can be also found in the docs:
- [React search installation](https://www.algolia.com/doc/guides/building-search-ui/installation/react/)
- [React Search Widgets](https://www.algolia.com/doc/guides/building-search-ui/widgets/showcase/react/)

#### Security Notes

To restrict the scope of an API key the Secured API keys are used. 
The Secured API key can be only generated from Search-only API key from the Algolia API keys list.
This kind of API key is used when performing the Search method and to prevent possible tweak of the request to impersonate another user, so it's done on the Back End side. 
When performing the saveObjects method to create, update or delete the entries of the Ined the Admin API key is used.

More info [here](https://www.algolia.com/doc/guides/security/api-keys/how-to/user-restricted-access-to-data/?language=php).

#### Advanced Usage

You can select which Content Types to include or exclude from the Index.
Use the following config parameters to exclude or include the specific content types:
- `nova_ezalgoliasearchengine.default.exclude_content_types`
- `nova_ezalgoliasearchengine.default.include_content_types`
The `include` parameter is checked first and hence has the priority.
By default all the content types are saved to the Index except **User** and **User Group**.

There are also the following parameters:
- Searchable Attributes;
- Attributes for Faceting;
- Attributes to Retrieve;
- Attributes for Replicas (used for sorting);

You can see the default list of the attributes that are sent to Algolia in the 
[Deafult Settings](https://github.com/Novactive/NovaeZAlgoliaSearchEngine/blob/master/bundle/Resources/config/default_settings.yaml).

To send all those setting to Algolia use the `bin/console nova:ez:algolia:indexes:setup` command.

