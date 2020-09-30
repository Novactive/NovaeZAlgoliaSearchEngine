import React from 'react';
import ReactDOM from 'react-dom';
import algoliasearch from 'algoliasearch/lite';

import {
    InstantSearch,
    Configure,
    SearchBox,
    Hits,
    Stats,
    HitsPerPage,
    connectPagination,
    SortBy,
    RefinementList
} from 'react-instantsearch-dom';

const NovaEzAlgoliaSearch = ({ replicas, config, query }) => {
    const searchClient = algoliasearch(
        JSON.parse(config).app_id,
        JSON.parse(config).api_key
    );

    const queryParameters = JSON.parse(query);

    const indexName =
        JSON.parse(config).index_name_prefix + '-' + queryParameters.language;

    const fullIndexName = (indexName, replica) => {
        if (replica !== null) {
            indexName += '-' + replica;
        }
        return indexName;
    };

    return (
        <div>
            <h3>{queryParameters.language}</h3>
            <InstantSearch
                searchClient={searchClient}
                indexName={fullIndexName(indexName, queryParameters.replica)}
            >
                <Configure
                    {...queryParameters.requestOptions}
                    page={queryParameters.page}
                    hitsPerPage={queryParameters.hitsPerPage}
                    query={queryParameters.term}
                    filters={queryParameters.filtersString}
                />
                <table cellSpacing={'10px'}>
                    <tbody>
                        <tr>
                            <td>&nbsp;</td>
                            <td>
                                <Stats />
                            </td>
                            <td>
                                <SearchBox />
                            </td>
                            <td>
                                <CustomHitsPerPage
                                    hitsPerPage={queryParameters.hitsPerPage}
                                />
                            </td>
                            <td>
                                <CustomSorting
                                    indexName={indexName}
                                    replicas={JSON.parse(replicas)}
                                    fullIndexName={fullIndexName(
                                        indexName,
                                        queryParameters.replica
                                    )}
                                />
                            </td>
                        </tr>
                        <tr>
                            <td style={{ paddingRight: '30px' }}>
                                <CustomFacets facets={queryParameters.facets} />
                            </td>
                            <td colSpan={4}>
                                <Hits hitComponent={Hit} />
                            </td>
                        </tr>
                        <tr>
                            <td>&nbsp;</td>
                            <td colSpan={4}>
                                <CustomPagination />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </InstantSearch>
        </div>
    );
};

const CustomHitsPerPage = ({ hitsPerPage }) => {
    const items = [
        { value: hitsPerPage, label: hitsPerPage + ' hits per page' }
    ];
    const biggerValue = hitsPerPage * 2;
    items.unshift({
        value: biggerValue,
        label: biggerValue + ' hits per page'
    });
    if (hitsPerPage > 1) {
        const lessValue = Math.floor(hitsPerPage / 2);
        items.push({ value: lessValue, label: lessValue + ' hits per page' });
    }
    return <HitsPerPage defaultRefinement={hitsPerPage} items={items} />;
};

const CustomSorting = ({ indexName, replicas, fullIndexName }) => {
    const items = [{ value: indexName, label: 'Default' }];
    for (const index in replicas) {
        items.push({
            value: indexName + '-' + replicas[index].key,
            label: replicas[index].label
        });
    }

    return (
        <>
            <div style={{ float: 'left' }}>Sort By:</div>
            <div style={{ float: 'left' }}>
                <SortBy defaultRefinement={fullIndexName} items={items} />
            </div>
        </>
    );
};

const CustomFacets = ({ facets }) => {
    return facets.map(attr => {
        let i;
        const frags = attr.split('_');
        frags.pop();
        for (i = 0; i < frags.length; i++) {
            frags[i] = frags[i].charAt(0).toUpperCase() + frags[i].slice(1);
        }
        const label = frags.join(' ');
        return (
            <React.Fragment key={attr}>
                <h5>{label}</h5>
                <RefinementList attribute={attr} />
            </React.Fragment>
        );
    });
};

const Hit = ({ hit }) => <p>{hit.content_name_s}</p>;

const Pagination = ({ currentRefinement, nbPages, refine, createURL }) => (
    <ul style={{ paddingLeft: 0 }}>
        {new Array(nbPages).fill(null).map((_, index) => {
            const page = index + 1;
            const linkStyle = {
                fontWeight: currentRefinement === page ? 'bold' : ''
            };

            return (
                <li
                    key={index}
                    style={{
                        listStyleType: 'none',
                        float: 'left',
                        marginRight: '5px'
                    }}
                >
                    <a
                        href={createURL(page)}
                        style={linkStyle}
                        onClick={event => {
                            event.preventDefault();
                            refine(page);
                        }}
                    >
                        {page}
                    </a>
                </li>
            );
        })}
    </ul>
);

const CustomPagination = connectPagination(Pagination);

const container = document.getElementById('js-algolia-search-container');
ReactDOM.render(<NovaEzAlgoliaSearch {...container.dataset} />, container);
