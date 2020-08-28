<?php

/**
 * Nova eZ Algolia Search Engine.
 *
 * @author    Novactive - Maxim Strukov <m.strukov@novactive.com>
 * @copyright 2020 Novactive
 */

declare(strict_types=1);

namespace Novactive\Bundle\eZAlgoliaSearchEngine\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class SearchController
{
    /**
     * @Template("@NovaEzAlgoliaSearchEngine/search.html.twig")
     */
    public function searchAction(): array
    {
        return [];
    }
}