import React from 'react';
import ReactDOM from 'react-dom';
import algoliasearch from 'algoliasearch/lite';

import {InstantSearch, SearchBox, Hits, Stats, HitsPerPage, connectPagination, SortBy, RefinementList} from 'react-instantsearch-dom';

const NovaEzAlgoliaSearch = ({lang, replicas, config}) => {

    const searchClient = algoliasearch(JSON.parse(config).app_id, JSON.parse(config).app_secret);

    const indexName = JSON.parse(config).index_name + '-' + lang;

    const replicaNames = [
        {value: indexName, label: 'Default'}
    ];
    const replicasArray = JSON.parse(replicas);
    for (let key in replicasArray) {
        let label = replicasArray[key]['label'][lang];
        if (undefined === label) {
            label = replicasArray[key]['label']['eng-GB'];
        }
        replicaNames.push({value: indexName+'-'+key, label: label});
    }

    return <div>
        <h3>{lang}</h3>
        <InstantSearch searchClient={searchClient} indexName={indexName}>
            <table cellSpacing={'10px'}>
                <tr>
                    <td>
                    </td>
                    <td>
                        <Stats/>
                    </td>
                    <td>
                        <SearchBox/>
                    </td>
                    <td>
                        <HitsPerPage
                            defaultRefinement={5}
                            items={[
                                {value: 5, label: 'Show 5 hits'},
                                {value: 2, label: 'Show 2 hits'},
                            ]}
                        />
                    </td>
                    <td>
                        <div style={{float: 'left'}}>Sort By:</div>
                        <div style={{float: 'left'}}>
                            <SortBy
                                defaultRefinement={indexName}
                                items={replicaNames}
                            />
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <RefinementList attribute="content_type_name_s"/>
                    </td>
                    <td colSpan={4}>
                        <Hits hitComponent={Hit}/>
                    </td>
                </tr>
                <tr>
                    <td>
                    </td>
                    <td colSpan={4}>
                        <CustomPagination/>
                    </td>
                </tr>
            </table>
        </InstantSearch>
    </div>
};

const Hit = ({hit}) => <p>{hit.article_title_value_s}</p>;

const Pagination = ({currentRefinement, nbPages, refine, createURL}) => (
    <ul style={{paddingLeft: 0}}>
        {new Array(nbPages).fill(null).map((_, index) => {
            const page = index + 1;
            const linkStyle = {
                fontWeight: currentRefinement === page ? 'bold' : '',
            };

            return (
                <li key={index} style={{'list-style-type': 'none', float: 'left', marginRight: '5px'}}>
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
ReactDOM.render(<NovaEzAlgoliaSearch {...container.dataset}/>, container);
