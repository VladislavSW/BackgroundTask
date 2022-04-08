<?php
/**
 * Scandiweb_BackgroundTask
 *
 * @category Scandiweb
 * @package  Scandiweb_BackgroundTask
 * @author   Vladislavs Piscikovs <vladislavs@scandiweb.com | info@scandiweb.com>
 */

declare(strict_types=1);

namespace Scandiweb\BackgroundTask\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface BackgroundTaskSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get search result items
     *
     * @return BackgroundTaskInterface[]
     */
    public function getItems();

    /**
     * Set search result items
     *
     * @param BackgroundTaskInterface[] $items
     *
     * @return $this
     */
    public function setItems(array $items);
}
