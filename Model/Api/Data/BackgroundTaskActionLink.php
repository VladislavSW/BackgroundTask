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

/**
 * {@inheritdoc}
 */
class BackgroundTaskActionLink extends DataObject implements BackgroundTaskActionLinkInterface
{
    /**
     * {@inheritdoc}
     */
    public function getText(): ?string
    {
        return $this->getData('text');
    }

    /**
     * {@inheritdoc}
     */
    public function setText(?string $text): BackgroundTaskActionLinkInterface
    {
        $this->setData('text', $text);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoutePath(): ?string
    {
        return $this->getData('route_path');
    }

    /**
     * {@inheritdoc}
     */
    public function setRoutePath(?string $routePath): BackgroundTaskActionLinkInterface
    {
        $this->setData('route_path', $routePath);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteParams(): ?array
    {
        return $this->getData('route_params');
    }

    /**
     * {@inheritdoc}
     */
    public function setRouteParams(?array $params): BackgroundTaskActionLinkInterface
    {
        $this->setData('route_params', $params);

        return $this;
    }
}
