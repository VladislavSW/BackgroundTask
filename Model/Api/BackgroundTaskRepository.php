<?php
/**
 * Scandiweb_BackgroundTask
 *
 * @category Scandiweb
 * @package  Scandiweb_BackgroundTask
 * @author   Vladislavs Piscikovs <vladislavs@scandiweb.com | info@scandiweb.com>
 */

declare(strict_types=1);

namespace Scandiweb\BackgroundTask\Model\Api;

use Exception;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Scandiweb\BackgroundTask\Api\BackgroundTaskRepositoryInterface;
use Scandiweb\BackgroundTask\Api\Data\BackgroundTaskInterface;
use Scandiweb\BackgroundTask\Api\Data\BackgroundTaskSearchResultsInterface;
use Scandiweb\BackgroundTask\Api\Data\BackgroundTaskSearchResultsInterfaceFactory;
use Scandiweb\BackgroundTask\Model\BackgroundTaskFactory;
use Scandiweb\BackgroundTask\Model\ResourceModel\BackgroundTask as BackgroundTaskResource;
use Scandiweb\BackgroundTask\Model\ResourceModel\BackgroundTask\CollectionFactory;
use Magento\Framework\Exception\AlreadyExistsException;

class BackgroundTaskRepository implements BackgroundTaskRepositoryInterface
{
    /**
     * @var BackgroundTaskResource
     */
    protected $backgroundTaskResource;

    /**
     * @var BackgroundTaskFactory
     */
    protected $backgroundTaskFactory;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var BackgroundTaskSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @param BackgroundTaskResource $backgroundTaskResource
     * @param BackgroundTaskFactory $backgroundTaskFactory
     * @param CollectionFactory $collectionFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param BackgroundTaskSearchResultsInterfaceFactory $searchResultsFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        BackgroundTaskResource $backgroundTaskResource,
        BackgroundTaskFactory $backgroundTaskFactory,
        CollectionFactory $collectionFactory,
        CollectionProcessorInterface $collectionProcessor,
        BackgroundTaskSearchResultsInterfaceFactory $searchResultsFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->backgroundTaskResource = $backgroundTaskResource;
        $this->backgroundTaskFactory = $backgroundTaskFactory;
        $this->collectionFactory = $collectionFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @param BackgroundTaskInterface $task
     *
     * @return void
     * @throws AlreadyExistsException
     */
    public function save(BackgroundTaskInterface $task): void
    {
        $backgroundTask = $this->backgroundTaskFactory->create();

        $backgroundTask->setId($task->getId())
            ->setHandler($task->getHandler())
            ->setArgs($task->getArgs())
            ->setStatus($task->getStatus())
            ->setMessage($task->getMessage())
            ->setCreatedAt($task->getCreatedAt())
            ->setExecutedAt($task->getExecutedAt())
            ->setFinishedAt($task->getFinishedAt());

        $this->backgroundTaskResource->save($backgroundTask);
    }

    /**
     * @param BackgroundTaskInterface $task
     *
     * @return void
     * @throws Exception
     */
    public function delete(BackgroundTaskInterface $task): void
    {
        $backgroundTask = $this->backgroundTaskFactory->create();
        $backgroundTask->setId($task->getId());

        $this->backgroundTaskResource->delete($backgroundTask);
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return BackgroundTaskSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): BackgroundTaskSearchResultsInterface
    {
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());

        return $searchResults;
    }

    /**
     * @param string $status
     *
     * @return BackgroundTaskSearchResultsInterface
     */
    public function getListByStatus(string $status): BackgroundTaskSearchResultsInterface
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('status', $status)
            ->create();

        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());

        return $searchResults;
    }
}
