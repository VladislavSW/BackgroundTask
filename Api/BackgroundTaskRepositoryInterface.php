<?php
/**
 * Scandiweb_BackgroundTask
 *
 * @category Scandiweb
 * @package  Scandiweb_BackgroundTask
 * @author   Vladislavs Piscikovs <vladislavs@scandiweb.com | info@scandiweb.com>
 */

declare(strict_types=1);

namespace Scandiweb\BackgroundTask\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Scandiweb\BackgroundTask\Api\Data\BackgroundTaskInterface;
use Scandiweb\BackgroundTask\Api\Data\BackgroundTaskSearchResultsInterface;

interface BackgroundTaskRepositoryInterface
{
    /**
     * Save the task
     *
     * @param BackgroundTaskInterface $task
     *
     * @return void
     */
    public function save(BackgroundTaskInterface $task): void;

    /**
     * Delete the task
     *
     * @param BackgroundTaskInterface $task
     *
     * @return void
     */
    public function delete(BackgroundTaskInterface $task): void;

    /**
     * Get task list by search criteria
     *
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return BackgroundTaskSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): BackgroundTaskSearchResultsInterface;

    /**
     * Get task list by status
     *
     * @param string $status
     *
     * @return BackgroundTaskSearchResultsInterface
     */
    public function getListByStatus(string $status): BackgroundTaskSearchResultsInterface;
}
