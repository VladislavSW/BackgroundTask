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

interface BackgroundTaskActionLinkInterface
{
    /**
     * Task action text getter
     *
     * @return string|null
     */
    public function getText(): ?string;

    /**
     * Task action text setter
     *
     * @param string|null $text
     *
     * @return $this
     */
    public function setText(?string $text): BackgroundTaskActionLinkInterface;

    /**
     * Task action link route path getter
     *
     * @return string|null
     */
    public function getRoutePath(): ?string;

    /**
     * Task action link route path setter
     *
     * @param string|null $routePath
     *
     * @return $this
     */
    public function setRoutePath(?string $routePath): BackgroundTaskActionLinkInterface;

    /**
     * Task action link route params getter
     *
     * @return array|null
     */
    public function getRouteParams(): ?array;

    /**
     * Task action link route params setter
     *
     * @param array|null $params
     *
     * @return $this
     */
    public function setRouteParams(?array $params): BackgroundTaskActionLinkInterface;
}
