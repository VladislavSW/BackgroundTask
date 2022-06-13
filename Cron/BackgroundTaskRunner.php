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
use Psr\Log\LoggerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Exception\AlreadyExistsException;
use Scandiweb\BackgroundTask\Model\Handler\BackgroundTaskHandlerInterface;
use Scandiweb\BackgroundTask\Model\Api\BackgroundTaskRepositoryFactory;
use Scandiweb\BackgroundTask\Model\Api\Data\BackgroundTask;

/**
 * Background task runner class.
 * Handles pending tasks.
 */
class BackgroundTaskRunner
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var BackgroundTaskRepositoryFactory
     */
    protected $backgroundTaskRepositoryFactory;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param LoggerInterface $logger
     * @param ObjectManagerInterface $objectManager
     * @param DateTime $dateTime
     * @param BackgroundTaskRepositoryFactory $backgroundTaskRepositoryFactory
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        LoggerInterface $logger,
        ObjectManagerInterface $objectManager,
        DateTime $dateTime,
        BackgroundTaskRepositoryFactory $backgroundTaskRepositoryFactory,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->logger = $logger;
        $this->objectManager = $objectManager;
        $this->dateTime = $dateTime;
        $this->backgroundTaskRepositoryFactory = $backgroundTaskRepositoryFactory;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Task runner
     *
     * @return void
     * @throws Exception
     */
    public function execute(): void
    {
        $isDisabled = $this->scopeConfig->getValue(BackgroundTask::IS_DISABLED_CONFIG_PATH);

        if (!$isDisabled) {
            $taskList = $this->backgroundTaskRepositoryFactory
                ->create()
                ->getListByStatus(BackgroundTask::STATUS_PENDING)
                ->getItems();

            if (count($taskList)) {
                $task = reset($taskList);

                try {
                    $this->run($task);

                    if ($task->getStatus() === BackgroundTask::STATUS_ERROR) {
                        $this->error($task);
                    }
                } catch (Exception $e) {
                    $task->addMessage($e->getMessage());
                    $this->error($task);
                }
            }
        }
    }

    /**
     * Run task
     *
     * @param BackgroundTask $task
     *
     * @return void
     * @throws AlreadyExistsException
     */
    private function run(BackgroundTask $task): void
    {
        $handler = $this->objectManager->create($task->getHandler());

        if (!($handler instanceof BackgroundTaskHandlerInterface)) {
            $instanceClass = BackgroundTaskHandlerInterface::class;
            $handlerClass = get_class($handler);

            $task->addMessage("Job handler {$handlerClass} must be an instance of {$instanceClass}");
            $this->error($task);
        } else {
            $task->setStatus(BackgroundTask::STATUS_RUNNING)
                ->setExecutedAt($this->getCurrentDateTime());

            $this->saveTask($task);
            $handler->runTask($task);
            $this->success($task);
        }
    }

    /**
     * Error task handler
     *
     * @param BackgroundTask $task
     *
     * @return void
     * @throws AlreadyExistsException
     */
    private function error(BackgroundTask $task): void
    {
        $this->logger->error($task->getMessages());

        $task->setStatus(BackgroundTask::STATUS_ERROR)
            ->setFinishedAt($this->getCurrentDateTime());

        $this->saveTask($task);
    }

    /**
     * Success task handler
     *
     * @param BackgroundTask $task
     *
     * @return void
     * @throws AlreadyExistsException
     */
    private function success(BackgroundTask $task): void
    {
        $task->setStatus(BackgroundTask::STATUS_SUCCESS)
            ->setFinishedAt($this->getCurrentDateTime());

        $this->saveTask($task);
    }

    /**
     * Save task
     *
     * @param BackgroundTask $task
     *
     * @return void
     * @throws AlreadyExistsException
     */
    private function saveTask(BackgroundTask $task): void
    {
        $backgroundTaskRepository = $this->backgroundTaskRepositoryFactory->create();
        $backgroundTaskRepository->save($task);
    }

    /**
     * Get current date time
     *
     * @return string
     */
    private function getCurrentDateTime(): string
    {
        return date(
            'Y-m-d H:i:s',
            $this->dateTime->gmtTimestamp()
        );
    }
}
