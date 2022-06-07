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
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Scandiweb\BackgroundTask\Model\Api\BackgroundTaskRepositoryFactory;
use Scandiweb\BackgroundTask\Model\BackgroundTask;

/**
 * Background task cleaner class.
 * Cleans the database from old background tasks.
 * Cleaning frequency is based on the THRESHOLD.
 */
class BackgroundTaskCleaner
{
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
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param BackgroundTaskRepositoryFactory $backgroundTaskRepositoryFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param DateTime $dateTime
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        BackgroundTaskRepositoryFactory $backgroundTaskRepositoryFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        DateTime $dateTime,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->backgroundTaskRepositoryFactory = $backgroundTaskRepositoryFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->dateTime = $dateTime;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Clean old records
     *
     * @return void
     * @throws Exception
     */
    public function execute(): void
    {
        $isDisabled = $this->scopeConfig->getValue(
            BackgroundTask::IS_DISABLED_CONFIG_PATH,
            ScopeInterface::SCOPE_STORE
        );

        if (!$isDisabled) {
            $frequencyInSeconds = $this->getCleaningFrequency();
            $cleanFromDate = date('Y-m-d H:i:s', $this->dateTime->gmtTimestamp() - $frequencyInSeconds);
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

    /**
     * Get task cleaning frequency in seconds
     *
     * @return int
     */
    private function getCleaningFrequency(): int
    {
        $frequencyInDays = $this->scopeConfig->getValue(
            BackgroundTask::CLEANING_FREQUENCY_CONFIG_PATH,
            ScopeInterface::SCOPE_STORE
        );

        return 3600 * 24 * (int)$frequencyInDays;
    }
}
