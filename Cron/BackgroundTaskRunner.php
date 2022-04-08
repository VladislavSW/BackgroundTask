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
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Exception\AlreadyExistsException;
use Scandiweb\BackgroundTask\Api\Data\BackgroundTaskInterface;
use Scandiweb\BackgroundTask\Model\BackgroundTaskHandlerInterface;
use Scandiweb\BackgroundTask\Model\Api\BackgroundTaskRepositoryFactory;

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
     * @param LoggerInterface $logger
     * @param ObjectManagerInterface $objectManager
     * @param DateTime $dateTime
     * @param BackgroundTaskRepositoryFactory $backgroundTaskRepositoryFactory
     */
    public function __construct(
        LoggerInterface $logger,
        ObjectManagerInterface $objectManager,
        DateTime $dateTime,
        BackgroundTaskRepositoryFactory $backgroundTaskRepositoryFactory
    ) {
        $this->logger = $logger;
        $this->objectManager = $objectManager;
        $this->dateTime = $dateTime;
        $this->backgroundTaskRepositoryFactory = $backgroundTaskRepositoryFactory;
    }

    /**
     * Run tasks
     *
     * @return void
     * @throws Exception
     */
    public function execute(): void
    {
        $taskList = $this->backgroundTaskRepositoryFactory
            ->create()
            ->getListByStatus(BackgroundTaskInterface::STATUS_PENDING)
            ->getItems();

        if (count($taskList)) {
            $task = reset($taskList);

            try {
                $this->run($task);

                if ($task->getStatus() === BackgroundTaskInterface::STATUS_ERROR) {
                    $this->error($task);
                }
            } catch (Exception $e) {
                $task->setMessage($e->getMessage());
                $this->error($task);
            }
        }
    }

    /**
     * @param BackgroundTaskInterface $task
     *
     * @return void
     * @throws AlreadyExistsException
     */
    private function run(BackgroundTaskInterface $task): void
    {
        $handler = $this->objectManager->create($task->getHandler());

        if (!($handler instanceof BackgroundTaskHandlerInterface)) {
            $instanceClass = BackgroundTaskHandlerInterface::class;
            $handlerClass = get_class($handler);

            $task->setMessage("Job handler {$handlerClass} must be an instance of {$instanceClass}");
            $this->error($task);
        } else {
            $task->setStatus(BackgroundTaskInterface::STATUS_RUNNING)
                ->setExecutedAt(strftime(
                    '%Y-%m-%d %H:%M:%S',
                    $this->dateTime->gmtTimestamp()
                ));

            $this->saveTask($task);
            $handler->execute($task);
            $this->success($task);
        }
    }

    /**
     * @param BackgroundTaskInterface $task
     *
     * @return void
     * @throws AlreadyExistsException
     */
    private function error(BackgroundTaskInterface $task): void
    {
        $this->logger->error($task->getMessage());

        $task->setStatus(BackgroundTaskInterface::STATUS_ERROR)
            ->setFinishedAt(strftime(
                '%Y-%m-%d %H:%M:%S',
                $this->dateTime->gmtTimestamp()
            ));

        $this->saveTask($task);
    }

    /**
     * @param BackgroundTaskInterface $task
     *
     * @return void
     * @throws AlreadyExistsException
     */
    private function success(BackgroundTaskInterface $task): void
    {
        $task->setStatus(BackgroundTaskInterface::STATUS_SUCCESS)
            ->setFinishedAt(strftime(
                '%Y-%m-%d %H:%M:%S',
                $this->dateTime->gmtTimestamp()
            ));

        $this->saveTask($task);
    }

    /**
     * @param BackgroundTaskInterface $task
     *
     * @return void
     * @throws AlreadyExistsException
     */
    private function saveTask(BackgroundTaskInterface $task): void
    {
        $backgroundTaskRepository = $this->backgroundTaskRepositoryFactory->create();
        $backgroundTaskRepository->save($task);
    }
}
