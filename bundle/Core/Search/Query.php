<?php

/**
 * Nova eZ Algolia Search Engine.
 *
 * @author    Novactive - Maxim Strukov <m.strukov@novactive.com>
 * @copyright 2020 Novactive
 */

declare(strict_types=1);

namespace Novactive\Bundle\eZAlgoliaSearchEngine\Core\Search;

use Symfony\Component\Serializer\Annotation as Serialization;

class Query
{
    /**
     * @var string
     * @Serialization\Groups({"Default"})
     */
    private $language;

    /**
     * @var string|null
     * @Serialization\Groups({"Default"})
     */
    private $replica;

    /**
     * @var string
     * @Serialization\Groups({"Default"})
     */
    private $term;

    /**
     * @var array
     */
    private $filters;

    /**
     * @var int
     * @Serialization\Groups({"Default"})
     */
    private $page;

    /**
     * @var int
     * @Serialization\Groups({"Default"})
     */
    private $hitsPerPage;

    /**
     * @var array
     */
    private $facets;

    /**
     * @var array
     */
    private $requestOptions;

    public const DEFAULT_FACETS = ['content_type_identifier_s'];

    public function __construct(
        string $language,
        string $term = '',
        string $filter = '',
        array $facets = [],
        int $page = 0,
        int $hitsPerPage = 25
    ) {
        $this->language = $language;
        $this->term = $term;
        $this->page = $page;
        $this->hitsPerPage = $hitsPerPage;
        $this->filters = [];
        $this->addFilter($filter);
        $this->facets = $facets;
        $this->requestOptions = [];
    }

    public function addFilter(string $filter): self
    {
        $filter = trim($filter);
        if ('' === $filter) {
            return $this;
        }
        $this->filters[] = "({$filter})";

        return $this;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function getReplica(): ?string
    {
        return $this->replica;
    }

    public function setReplica(?string $replica): void
    {
        $this->replica = $replica;
    }

    public function getTerm(): string
    {
        return $this->term;
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * @Serialization\Groups({"Default"})
     */
    public function getFiltersString(): string
    {
        return implode(' AND ', $this->filters);
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getHitsPerPage(): int
    {
        return $this->hitsPerPage;
    }

    /**
     * @Serialization\Groups({"Default"})
     */
    public function getFacets(): array
    {
        if (count($this->facets) > 0) {
            return $this->facets;
        }

        return self::DEFAULT_FACETS;
    }

    public function setRequestOption(string $key, $value): void
    {

        $this->requestOptions[$key] = $value;
    }

    public function getRequestOption(string $key)
    {
        return $this->requestOptions[$key];
    }

    /**
     * @Serialization\Groups({"Default"})
     */
    public function getRequestOptions(): array
    {
        return $this->requestOptions;
    }
}
