<?php
/**
 * Scandiweb_BackgroundTask
 *
 * @category Scandiweb
 * @package  Scandiweb_BackgroundTask
 * @author   Vladislavs Piscikovs <vladislavs@scandiweb.com | info@scandiweb.com>
 */

declare(strict_types=1);

namespace Scandiweb\BackgroundTask\Model\Handler;

use Scandiweb\BackgroundTask\Api\Data\BackgroundTaskInterface;

/**
 * Background task handler interface.
 */
interface BackgroundTaskHandlerInterface
{
    /**
     * Get task name
     *
     * @return string
     */
    public static function getTaskName(): string;

    /**
     * Background task handler
     *
     * @param BackgroundTaskInterface $backgroundTask
     *
     * @return void
     */
    public function runTask(BackgroundTaskInterface $backgroundTask): void;
}
