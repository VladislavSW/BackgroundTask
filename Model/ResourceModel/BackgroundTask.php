<?php
/**
 * Scandiweb_BackgroundTask
 *
 * @category Scandiweb
 * @package  Scandiweb_BackgroundTask
 * @author   Vladislavs Piscikovs <vladislavs@scandiweb.com | info@scandiweb.com>
 */

declare(strict_types=1);

namespace Scandiweb\BackgroundTask\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * {@inheritdoc}
 */
class BackgroundTask extends AbstractDb
{
    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init('scandiweb_background_task', 'id');
    }
}
