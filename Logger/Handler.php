<?php
/**
 * Scandiweb_BackgroundTask
 *
 * @category Scandiweb
 * @package  Scandiweb_BackgroundTask
 * @author   Vladislavs Piscikovs <vladislavs@scandiweb.com | info@scandiweb.com>
 */

declare(strict_types=1);

namespace Scandiweb\BackgroundTask\Logger;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;

/**
 * {@inheritdoc}
 */
class Handler extends Base
{
    /**
     * @var int
     */
    protected $loggerType = Logger::ERROR;

    /**
     * @var string
     */
    protected $fileName = '/var/log/scandiweb/background_task.log';
}
