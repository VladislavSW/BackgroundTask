<?php
/**
 * Scandiweb_BackgroundTask
 *
 * @category Scandiweb
 * @package  Scandiweb_BackgroundTask
 * @author   Vladislavs Piscikovs <vladislavs@scandiweb.com | info@scandiweb.com>
 */

declare(strict_types=1);

namespace Scandiweb\BackgroundTask\Model\ResourceModel\BackgroundTask;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Scandiweb\BackgroundTask\Model\Api\Data\BackgroundTask;
use Scandiweb\BackgroundTask\Model\ResourceModel\BackgroundTask as BackgroundTaskResource;

/**
 * {@inheritdoc}
 */
class Collection extends AbstractCollection
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(BackgroundTask::class, BackgroundTaskResource::class);
    }
}
