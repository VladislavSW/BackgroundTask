<?php
/**
 * Scandiweb_BackgroundTask
 *
 * @category Scandiweb
 * @package  Scandiweb_BackgroundTask
 * @author   Vladislavs Piscikovs <vladislavs@scandiweb.com | info@scandiweb.com>
 */

declare(strict_types=1);

namespace Scandiweb\BackgroundTask\Cron;

use Exception;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Scandiweb\BackgroundTask\Model\Api\BackgroundTaskRepositoryFactory;

/**
 * Background task cleaner class.
 * Cleans the database from old background tasks.
 * Cleaning frequency is based on the THRESHOLD.
 */
class BackgroundTaskCleaner
{
    /**
     * Cleaning threshold in days
     */
    private const THRESHOLD = 30;

    /**
     * @var BackgroundTaskRepositoryFactory
     */
    protected $backgroundTaskRepositoryFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @param BackgroundTaskRepositoryFactory $backgroundTaskRepositoryFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param DateTime $dateTime
     */
    public function __construct(
        BackgroundTaskRepositoryFactory $backgroundTaskRepositoryFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        DateTime $dateTime
    ) {
        $this->backgroundTaskRepositoryFactory = $backgroundTaskRepositoryFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->dateTime = $dateTime;
    }

    /**
     * Clean old records
     *
     * @return void
     * @throws Exception
     */
    public function execute(): void
    {
        $timeOffset = 3600 * 24 * self::THRESHOLD;
        $cleanFromDate = date('Y-m-d H:i:s', $this->dateTime->gmtTimestamp() - $timeOffset);
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('created_at', $cleanFromDate, 'lteq')
            ->create();
        $backgroundTaskRepository = $this->backgroundTaskRepositoryFactory->create();
        $tasks = $backgroundTaskRepository->getList($searchCriteria)->getItems();

        foreach ($tasks as $task) {
            $backgroundTaskRepository->delete($task);
        }
    }
}
