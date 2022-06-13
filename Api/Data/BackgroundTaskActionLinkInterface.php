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
 * Background task action link interface.
 * Used for action links that will be generated in the admin grid.
 *
 * @api
 */
interface BackgroundTaskActionLinkInterface
{
    /**
     * Get task action link text
     *
     * @return string
     */
    public function getText(): string;

    /**
     * Set task action link text
     *
     * @param string|null $text
     *
     * @return \Scandiweb\BackgroundTask\Api\Data\BackgroundTaskActionLinkInterface
     */
    public function setText(?string $text): BackgroundTaskActionLinkInterface;

    /**
     * Get task action link route path
     *
     * @return string
     */
    public function getRoutePath(): string;

    /**
     * Set task action link route path
     *
     * @param string|null $routePath
     *
     * @return \Scandiweb\BackgroundTask\Api\Data\BackgroundTaskActionLinkInterface
     */
    public function setRoutePath(?string $routePath): BackgroundTaskActionLinkInterface;

    /**
     * Get task action link route params
     *
     * @return mixed
     */
    public function getRouteParams(): array;

    /**
     * Set task action link route params
     *
     * @param array|null $params
     *
     * @return \Scandiweb\BackgroundTask\Api\Data\BackgroundTaskActionLinkInterface
     */
    public function setRouteParams(?array $params): BackgroundTaskActionLinkInterface;
}
