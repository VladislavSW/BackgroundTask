# BackgroundTask

The module provides the functionality to execute tasks in the background.

For task CRUD operations use `Model/Api/BackgroundTaskRepository`.
The repository methods can be exposed for REST API (not tested).
The task will be executed by `scandiweb_background_task` cron group.

The module is limited to 5 simultaneous tasks. 
This limitation can be changed by adding new cron jobs to the `etc/crontab.xml`.

The execution errors will be logged in `project_root/var/log/scandiweb/background_task.log`.

An example of a pending task from the `scandiweb_background_task` table:

| id  | handler                                                      | args       |status  | message | created_at          | executed_at | finished_at |
|-----|--------------------------------------------------------------|------------|--------|---------|---------------------|-------------|-------------|
| 1   | Scandiweb\Sales\Model\BackgroundTask\SetMassShippedHandler   | ["357028"] |pending | [NULL]  | 2022-04-05 14:11:31 | [NULL]      | [NULL]      |

All tasks can be viewed in the admin panel `Admin->Scandiweb->Background Tasks->View Tasks`.

## Task runner
`scandiweb_background_task` cron group runs `scandiweb_background_task_runner_[X]` cron jobs.

`scandiweb_background_task_runner_[X]` cron jobs will get the first task with `pending` status 
from the `scandiweb_background_task` database table. The cron job logic will create an object from the `handler`
value and will run the `execute` method passing the `task` as an argument. 

The `handler` class must implement `Model/BackgroundTaskHandlerInterface`, otherwise, an exception
will be thrown. 

Running the task, the task status will be changed to `running`.

The `handler` class handles the logic task logic. 

Task runner cron job respects task object `error` status or any thrown `exceptions`. 
In error or exception cases the task `status` will be changed to `error`, and the task `message` will be saved. 

In the success case, the task status will be changed to `success` and all messages set to the task will be saved.

Use `BackgroundTask\Model\MessageManager::addBackgroundTaskNotice`
to notice that the selected task will run in the background.

## Task cleaner
The cron job `scandiweb_background_task_cleaner` will clean old tasks. 
The threshold is hardcoded and is set to 30 days, meaning that records older than 30 days will be deleted.

## Code example
### Add mass-action to the background tasks list
`Scandiweb\Sales\Controller\Adminhtml\Order\SetMassDelivered`
```injectablephp

...

use Scandiweb\Sales\Model\BackgroundTask\SetMassDeliveredHandler;
use Scandiweb\Sales\Model\BackgroundTask\SetMassDeliveredHandlerFactory;
use Scandiweb\BackgroundTask\Model\BackgroundTaskFactory;
use Scandiweb\BackgroundTask\Model\Api\BackgroundTaskRepositoryFactory;

...

public function __construct(
    ...
    BackgroundTaskFactory $backgroundTaskFactory,
    BackgroundTaskRepositoryFactory $backgroundTaskRepositoryFactory,
    SetMassDeliveredHandlerFactory $setMassDeliveredHandlerFactory
) {
    ...
    
    $this->backgroundTaskFactory = $backgroundTaskFactory;
    $this->backgroundTaskRepositoryFactory = $backgroundTaskRepositoryFactory;
    $this->setMassDeliveredHandlerFactory = $setMassDeliveredHandlerFactory;
}

...

public function massAction(AbstractCollection $collection)
{
    ...
    
    $backgroundTask = $this->backgroundTaskFactory
        ->create()
        ->setArgs(['order_ids' => $orderIds]);

    if ($ordersCount > $massActionThreshold) {
        ...
        
        // Run in a background
        $backgroundTask->setHandler(SetMassDeliveredHandler::class);
        $this->backgroundTaskRepositoryFactory
            ->create()
            ->save($backgroundTask);

        ...
    } else {
        // Run within the same request
        $this->setMassDeliveredHandlerFactory
            ->create()
            ->execute($backgroundTask);
    }

    ...
}
```
`Sporltand\Sales\Model\BackgroundTask\SetMassDeliveredHandler`
```injectablephp
...

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Scandiweb\BackgroundTask\Api\Data\BackgroundTaskInterface;
use Scandiweb\BackgroundTask\Model\BackgroundTaskHandlerInterface;
use Scandiweb\Migration\Setup\Patch\Data\AddOrderStatuses;
use Scandiweb\Sales\Helper\OrderStatusUpdate;

class SetMassDeliveredHandler implements BackgroundTaskHandlerInterface
{
    /**
     * @var OrderStatusUpdate
     */
    protected $orderStatusUpdateHelper;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @param OrderStatusUpdate $orderStatusUpdateHelper
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        OrderStatusUpdate $orderStatusUpdateHelper,
        ManagerInterface $messageManager
    ) {
        $this->orderStatusUpdateHelper = $orderStatusUpdateHelper;
        $this->messageManager = $messageManager;
    }

    /**
     * @param BackgroundTaskInterface $backgroundTask
     *
     * @return void
     * @throws LocalizedException
     */
    public function execute(BackgroundTaskInterface $backgroundTask): void
    {
        $args = $backgroundTask->getArgs();

        if (!array_key_exists('order_ids', $args)) {
            throw new LocalizedException(__('Argument order_ids is missing.'));
        }

        foreach ($args['order_ids'] as $orderId) {
            $this->orderStatusUpdateHelper->updateCompleteOrderStatuses(
                (int)$orderId,
                (string)__('Order Delivered'),
                AddOrderStatuses::ORDER_STATUS_COMPLETE_DELIVERED_CODE
            );
        }

        $successMsg = __('Order statuses updated for complete orders.');
        $this->messageManager->addSuccessMessage($successMsg);
        $backgroundTask->setMessage($successMsg->__toString());
    }
}
```