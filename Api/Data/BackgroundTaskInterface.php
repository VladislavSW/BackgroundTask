<?php
/**
 * Scandiweb_BackgroundTask
 *
 * @category Scandiweb
 * @package  Scandiweb_BackgroundTask
 * @author   Vladislavs Piscikovs <vladislavs@scandiweb.com | info@scandiweb.com>
 */

declare(strict_types=1);

namespace Scandiweb\BackgroundTask\Api\Data;

/**
 * Background task interface.
 * Provides data about background task.
 *
 * @api
 */
interface BackgroundTaskInterface
{
    /**
     * Task statuses
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_RUNNING = 'running';
    public const STATUS_SUCCESS = 'success';
    public const STATUS_ERROR = 'error';

    /**
     * XML configuration paths
     */
    public const CLEANING_FREQUENCY_CONFIG_PATH = 'system/background_task/cleaning_frequency';
    public const IS_DISABLED_CONFIG_PATH = 'system/background_task/is_disabled';

    /**
     * Get task name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Set task name
     *
     * @param string $name
     *
     * @return \Scandiweb\BackgroundTask\Api\Data\BackgroundTaskInterface
     */
    public function setName(string $name): BackgroundTaskInterface;

    /**
     * Get task handler
     *
     * @return string
     */
    public function getHandler(): string;

    /**
     * Set task handler
     *
     * @param string $handler
     *
     * @return \Scandiweb\BackgroundTask\Api\Data\BackgroundTaskInterface
     */
    public function setHandler(string $handler): BackgroundTaskInterface;

    /**
     * Get task handler arguments
     *
     * @return mixed
     */
    public function getArgs(): array;

    /**
     * Set task handler arguments
     *
     * @param mixed $args
     *
     * @return \Scandiweb\BackgroundTask\Api\Data\BackgroundTaskInterface
     */
    public function setArgs($args): BackgroundTaskInterface;

    /**
     * Get task action link
     *
     * @return \Scandiweb\BackgroundTask\Api\Data\BackgroundTaskActionLinkInterface
     */
    public function getActionLink(): BackgroundTaskActionLinkInterface;

    /**
     * Set task action link
     *
     * @param BackgroundTaskActionLinkInterface|null $actionLink
     *
     * @return \Scandiweb\BackgroundTask\Api\Data\BackgroundTaskInterface
     */
    public function setActionLink(?BackgroundTaskActionLinkInterface $actionLink): BackgroundTaskInterface;
}
