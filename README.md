# BackgroundTask

## Use case

Quite often it happens that tasks like admin mass actions or custom actions are running too long
in a browser, resulting in the connection closing and unfinished action execution. 
To bypass the long-running tasks on the client-side, the BackgroundTask module 
allows scheduling any task to be executed in the background. 
It is possible to run the task on the client-side too, 
which is useful when you want to run the task in the background 
only when the threshold of a selected record is exceeded for example.

## Module settings
The module settings are available in `Admin->System->Background Tasks - Settings->Background Tasks Settings`

`Disable Background Tasks` - disable the background task runner and cleaner. Default value: `No`.

`Cleaning Frequency` - the value in days that determine how old tasks should be cleaned. Default value: `30`. 

`Task Runner Cron Expression` - cron expression in * * * * * format.

`Task Cleaner Cron Expression` - cron expression in * * * * * format.

## Limitations

The module does not rely heavily on the core Magento2 core logic,
however, it was tested in Magento 2.3.3 and Magento 2.4.4 versions,
and it is not known if it works as expected in versions <2.3.3.

The module is limited to 5 simultaneous tasks.
This limitation can be changed by adding new cron jobs to the `etc/crontab.xml`
for the `scandiweb_background_task` cron group.
The cron group is set to run as a standalone process.

In case for any reason, the module eats too many resources, and it is not related to the
non-optimized background task handler, then consider changing the `scandiweb_background_task`
cron group configuration `Use Separate Process` to `No`. Also, in case there are too many long-running tasks, consider changing the cron jobs schedule
time.

## How to move logic to the background task?
1. Analyze the logic that should be moved to the background task. It is necessary to understand code dependencies.
2. Figure out all data that can't be retrieved via cron job and is used the logic that has to be moved to the background process. All found data should be added to the task arguments to use in the background task.
3. Add the task handler that will handle the logic.
4. Update the original method that runs the logic on the client side, and use the task handler class to schedule a new background task.

## Code example
In the example the default Magento2 MassStatus mass-action 
will be implemented as the background task. 

### Default MassStatus controller logic 
`Magento\Catalog\Controller\Adminhtml\Product\MassStatus`
```php
<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml\Product;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Catalog\Controller\Adminhtml\Product;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

/**
 * Updates status for a batch of products.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MassStatus extends \Magento\Catalog\Controller\Adminhtml\Product implements HttpPostActionInterface
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\Processor
     */
    protected $_productPriceIndexerProcessor;

    /**
     * MassActions filter
     *
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Action
     */
    private $productAction;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param Builder $productBuilder
     * @param \Magento\Catalog\Model\Indexer\Product\Price\Processor $productPriceIndexerProcessor
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Catalog\Model\Product\Action $productAction
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        Product\Builder $productBuilder,
        \Magento\Catalog\Model\Indexer\Product\Price\Processor $productPriceIndexerProcessor,
        Filter $filter,
        CollectionFactory $collectionFactory,
        \Magento\Catalog\Model\Product\Action $productAction = null
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->_productPriceIndexerProcessor = $productPriceIndexerProcessor;
        $this->productAction = $productAction ?: ObjectManager::getInstance()
            ->get(\Magento\Catalog\Model\Product\Action::class);
        parent::__construct($context, $productBuilder);
    }

    /**
     * Validate batch of products before theirs status will be set
     *
     * @param array $productIds
     * @param int $status
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _validateMassStatus(array $productIds, $status)
    {
        if ($status == \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED) {
            if (!$this->_objectManager->create(\Magento\Catalog\Model\Product::class)->isProductsHasSku($productIds)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Please make sure to define SKU values for all processed products.')
                );
            }
        }
    }

    /**
     * Update product(s) status action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $productIds = $collection->getAllIds();
        $requestStoreId = $storeId = $this->getRequest()->getParam('store', null);
        $filterRequest = $this->getRequest()->getParam('filters', null);
        $status = (int) $this->getRequest()->getParam('status');

        if (null === $storeId && null !== $filterRequest) {
            $storeId = (isset($filterRequest['store_id'])) ? (int) $filterRequest['store_id'] : 0;
        }

        try {
            $this->_validateMassStatus($productIds, $status);
            $this->productAction->updateAttributes($productIds, ['status' => $status], (int) $storeId);
            $this->messageManager->addSuccessMessage(
                __('A total of %1 record(s) have been updated.', count($productIds))
            );
            $this->_productPriceIndexerProcessor->reindexList($productIds);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('Something went wrong while updating the product(s) status.')
            );
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('catalog/*/', ['store' => $requestStoreId]);
    }
}

```

### Background task handler 
`ExampleVendor\BackgroundTask\Catalog\Model\Product\MassStatusHandler`

```php
<?php
/**
 * ExampleVendor_BackgroundTask
 *
 * @category ExampleVendor
 * @package  ExampleVendor_BackgroundTask
 * @author   John Doe <john.doe@example.com>
 */

declare(strict_types=1);

namespace ExampleVendor\BackgroundTask\Catalog\Model\Product;

use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Indexer\Product\Price\Processor as PriceIndexerProcessor;
use Magento\Catalog\Model\Product\ActionFactory as ProductActionFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Scandiweb\BackgroundTask\Api\Data\BackgroundTaskInterface;
use Scandiweb\BackgroundTask\Model\Handler\BackgroundTaskHandlerInterface;

/**
 * {@inheritdoc}
 * Run the logic in the background task
 */
class MassStatusHandler implements BackgroundTaskHandlerInterface
{
    /**
     * @var PriceIndexerProcessor
     */
    protected $productPriceIndexerProcessor;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var ProductActionFactory
     */
    protected $productActionFactory;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @param PriceIndexerProcessor $productPriceIndexerProcessor
     * @param ProductFactory $productFactory
     * @param ProductActionFactory $productActionFactory
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        PriceIndexerProcessor $productPriceIndexerProcessor,
        ProductFactory $productFactory,
        ProductActionFactory $productActionFactory,
        ManagerInterface $messageManager
    ) {
        $this->productPriceIndexerProcessor = $productPriceIndexerProcessor;
        $this->productFactory = $productFactory;
        $this->productActionFactory = $productActionFactory;
        $this->messageManager = $messageManager;
    }

    /**
     * {@inheritdoc}
     */
    public static function getTaskName(): string
    {
        return __('Product Mass Status Update')->__toString();
    }

    /**
     * {@inheritdoc}
     */
    public function runTask(BackgroundTaskInterface $backgroundTask): void
    {
        $args = $backgroundTask->getArgs();

        if (!array_key_exists('product_ids', $args)) {
            throw new LocalizedException(__('Argument product_ids is missing.'));
        }

        if (!array_key_exists('status', $args)) {
            throw new LocalizedException(__('Argument status is missing.'));
        }

        if (!array_key_exists('store_id', $args)) {
            throw new LocalizedException(__('Argument store_id is missing.'));
        }

        $productIds = $args['product_ids'];
        $status = $args['status'];
        $storeId = $args['store_id'];

        $this->_validateMassStatus($productIds, $status);
        $this->productActionFactory
            ->create()
            ->updateAttributes($productIds, ['status' => $status], (int) $storeId);
        $this->productPriceIndexerProcessor->reindexList($productIds);

        $successMsg = __('A total of %1 record(s) have been updated.', count($productIds));
        $this->messageManager->addSuccessMessage($successMsg);
        $backgroundTask->addMessage($successMsg->__toString());
    }

    /**
     * Validate batch of products before theirs status will be set
     *
     * @param array $productIds
     * @param int $status
     *
     * @return void
     * @throws LocalizedException
     */
    public function _validateMassStatus(array $productIds, $status): void
    {
        if ($status == Status::STATUS_ENABLED) {
            if (!$this->productFactory->create()->isProductsHasSku($productIds)) {
                throw new LocalizedException(
                    __('Please make sure to define SKU values for all processed products.')
                );
            }
        }
    }
}
```
### Overridden controller
`ExampleVendor/BackgroundTask/Catalog/Controller/Adminhtml/Product/MassStatus.php`

```php
<?php
/**
 * ExampleVendor_BackgroundTask
 *
 * @category ExampleVendor
 * @package  ExampleVendor_BackgroundTask
 * @author   John Doe <john.doe@example.com>
 */

declare(strict_types=1);

namespace ExampleVendor\BackgroundTask\Catalog\Controller\Adminhtml\Product;

use Magento\Catalog\Controller\Adminhtml\Product\MassStatus as SourceMassStatus;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Controller\Adminhtml\Product\Builder;
use Magento\Catalog\Model\Indexer\Product\Price\Processor;
use Magento\Catalog\Model\Product\Action;
use Scandiweb\BackgroundTask\Model\MessageManager;
use Scandiweb\BackgroundTask\Model\Api\BackgroundTaskRepositoryFactory;
use Scandiweb\BackgroundTask\Model\Api\Data\BackgroundTaskFactory;
use ExampleVendor\BackgroundTask\Catalog\Model\Product\MassStatusHandler;
use ExampleVendor\BackgroundTask\Catalog\Model\Product\MassStatusHandlerFactory;

/**
 * {@inheritdoc}
 * Run the logic in the background task
 */
class MassStatus extends SourceMassStatus
{
    /**
     * @var BackgroundTaskFactory
     */
    protected $backgroundTaskFactory;

    /**
     * @var BackgroundTaskRepositoryFactory
     */
    protected $backgroundTaskRepositoryFactory;

    /**
     * @var MassStatusHandlerFactory
     */
    protected $massStatusHandlerFactory;

    /**
     * @var MessageManager
     */
    protected $messageManager;

    /**
     * @param Context $context
     * @param Builder $productBuilder
     * @param Processor $productPriceIndexerProcessor
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param BackgroundTaskFactory $backgroundTaskFactory
     * @param BackgroundTaskRepositoryFactory $backgroundTaskRepositoryFactory
     * @param MassStatusHandlerFactory $massStatusHandlerFactory
     * @param MessageManager $messageManager
     * @param Action|null $productAction
     */
    public function __construct(
        Context $context,
        Builder $productBuilder,
        Processor $productPriceIndexerProcessor,
        Filter $filter,
        CollectionFactory $collectionFactory,
        BackgroundTaskFactory $backgroundTaskFactory,
        BackgroundTaskRepositoryFactory $backgroundTaskRepositoryFactory,
        MassStatusHandlerFactory $massStatusHandlerFactory,
        MessageManager $messageManager,
        Action $productAction = null
    ) {
        parent::__construct(
            $context,
            $productBuilder,
            $productPriceIndexerProcessor,
            $filter,
            $collectionFactory,
            $productAction
        );

        $this->backgroundTaskFactory = $backgroundTaskFactory;
        $this->backgroundTaskRepositoryFactory = $backgroundTaskRepositoryFactory;
        $this->massStatusHandlerFactory = $massStatusHandlerFactory;
        $this->messageManager = $messageManager;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $productIds = $collection->getAllIds();
        $requestStoreId = $storeId = $this->getRequest()->getParam('store', null);
        $filterRequest = $this->getRequest()->getParam('filters', null);
        $status = (int) $this->getRequest()->getParam('status');

        if (null === $storeId && null !== $filterRequest) {
            $storeId = (isset($filterRequest['store_id'])) ? (int) $filterRequest['store_id'] : 0;
        }

        try {
            $backgroundTask = $this->backgroundTaskFactory
                ->create() // Create the background task object
                ->setArgs([ // Add necessary arguments
                    'product_ids' => $productIds,
                    'status' => $status,
                    'store_id' => (int) $storeId
                ])
                ->setName(MassStatusHandler::getTaskName()) // Set task name that will be visible in the admin panel
                ->setHandler(MassStatusHandler::class); // Set the handler class name, that will handle the logic

            // Save the background task to the database
            $this->backgroundTaskRepositoryFactory
                ->create()
                ->save($backgroundTask);
            
            // Add background task notice message to inform admin that the task will run in the background
            $this->messageManager->addBackgroundTaskNotice($backgroundTask->getId());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('Something went wrong while updating the product(s) status.')
            );
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('catalog/*/', ['store' => $requestStoreId]);
    }
}
```

### Test
Go to the admin product list `Admin -> Catalog -> Products`. 
Add a product in case your store has no products.
Try to update a product status using mass-action. 
You should see the notification message that the task will run in the background 
e.g.: `The selected task will run in the background with the ID 1. See the background task list here.`

## How to add an action link to the admin grid?

In case if an action link should be used to download some file for example, use `Model\Api\Data\BackgroundTaskActionLinkFactory` to save the route data that will be used during link generation e.g.:

```php
$actionLink = $this->backgroundTaskActionLinkFactory
    ->create()
    ->setText('Download CSV') // The <a> tag text
    ->setRoutePath('reports/report/download') // Controller route 
    ->setRouteParams([
        'file_name' => $fileName, // Custom param used in the route controller 
        'file' => $csvFile['value'], // Custom param used in the route controller
        'task_id' => $backgroundTask->getId(), // Custom param used in the route controller
        'target' => '_self', // <a> tag target
        '_secure' => true // Magento specific param to generate secure URL
    ]);

$backgroundTask->setActionLink($actionLink);

$this->backgroundTaskRepositoryFactory
    ->create()
    ->save($backgroundTask);
```

To understand how the link is being generated in the admin grid refer to the `action_link` grid column definition below.

```xml
<column name="action_link"
        class="Scandiweb\BackgroundTask\Ui\Component\Listing\Columns\ActionLink"
        component="Scandiweb_BackgroundTask/js/grid/columns/action_link">
    <settings>
        <filter>text</filter>
        <label translate="true">Action Link</label>
    </settings>
</column>
```

## API
It is possible to add the background task to the schedule via REST API.
The only handlers that will be executed are the ones that
implements `BackgroundTaskHandlerInterface` interface.

### API request sample
The request should be sent to the BackgroundTask endpoint `/V1/background-task/save`.
Refer to the example below.
```php
Request Type: POST
Request URL: http://localhost:81/rest/default/V1/background-task/save
Request Body:
{
    "task": {
        "name": "Test API call",
        "handler": "ExampleVendor\\BackgroundTask\\Catalog\\Model\\Product\\MassStatusHandler",
        "args": "{\"product_ids\":[\"1\",\"2\"],\"status\":1,\"store_id\":0}",
        "action_link": {
            "text": "",
            "route_path": "",
            "route_params": []
        }
    }
}
Headers:
Content-Type: application/json
Authorization: Bearer eyJraWQiOiIxIiwiYWxnIjoiSFMyNTYifQ.eyJ1aWQiOjEsInV0eXBpZCI6MiwiaWF0IjoxNjU1MTUwODgxLCJleHAiOjE2NTUxNTQ0ODF9.lEOMRTPtDBTEOfIYCcxHgpkZ2fpIEVYx4Mes27Kao_k

```