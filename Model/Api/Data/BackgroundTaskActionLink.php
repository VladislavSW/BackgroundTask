<?php
/**
 * Scandiweb_BackgroundTask
 *
 * @category Scandiweb
 * @package  Scandiweb_BackgroundTask
 * @author   Vladislavs Piscikovs <vladislavs@scandiweb.com | info@scandiweb.com>
 */

declare(strict_types=1);

namespace Scandiweb\BackgroundTask\Model\Api\Data;

use Scandiweb\BackgroundTask\Api\Data\BackgroundTaskActionLinkInterface;
use Magento\Framework\DataObject;

class BackgroundTaskActionLink extends DataObject implements BackgroundTaskActionLinkInterface
{
    /**
     * Task action text getter
     *
     * @return string|null
     */
    public function getText(): ?string
    {
        return $this->getData('text');
    }

    /**
     * @param string|null $text
     *
     * @return BackgroundTaskActionLinkInterface
     */
    public function setText(?string $text): BackgroundTaskActionLinkInterface
    {
        $this->setData('text', $text);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getRoutePath(): ?string
    {
        return $this->getData('route_path');
    }

    /**
     * @param string|null $routePath
     *
     * @return $this
     */
    public function setRoutePath(?string $routePath): BackgroundTaskActionLinkInterface
    {
        $this->setData('route_path', $routePath);

        return $this;
    }

    /**
     * @return array|null
     */
    public function getRouteParams(): ?array
    {
        return $this->getData('route_params');
    }

    /**
     * @param array|null $params
     *
     * @return $this
     */
    public function setRouteParams(?array $params): BackgroundTaskActionLinkInterface
    {
        $this->setData('route_params', $params);

        return $this;
    }
}
