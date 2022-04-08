<?php
/**
 * Scandiweb_BackgroundTask
 *
 * @category Scandiweb
 * @package  Scandiweb_BackgroundTask
 * @author   Vladislavs Piscikovs <vladislavs@scandiweb.com | info@scandiweb.com>
 */

declare(strict_types=1);

namespace Scandiweb\BackgroundTask\Model;

use Scandiweb\BackgroundTask\Api\Data\BackgroundTaskInterface;

interface BackgroundTaskHandlerInterface
{
    /**
     * Background task handler
     *
     * @param BackgroundTaskInterface $backgroundTask
     *
     * @return void
     */
    public function execute(BackgroundTaskInterface $backgroundTask): void;
}
