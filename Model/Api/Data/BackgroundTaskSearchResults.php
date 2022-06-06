<?php
/**
 * Scandiweb_BackgroundTask
 *
 * @category Scandiweb
 * @package  Scandiweb_BackgroundTask
 * @author   Vladislavs Piscikovs <vladislavs@scandiweb.com | info@scandiweb.com>
 */

declare(strict_types=1);

namespace Scandiweb\BackgroundTask\Model\Api\Data;

use Magento\Framework\Api\SearchResults;
use Scandiweb\BackgroundTask\Api\Data\BackgroundTaskSearchResultsInterface;

/**
 * {@inheritdoc}
 */
class BackgroundTaskSearchResults extends SearchResults implements BackgroundTaskSearchResultsInterface
{
}
