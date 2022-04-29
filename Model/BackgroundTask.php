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
use Scandiweb\BackgroundTask\Api\Data\BackgroundTaskActionLinkInterface;
use Scandiweb\BackgroundTask\Model\Api\Data\BackgroundTaskActionLinkFactory;
use Scandiweb\BackgroundTask\Model\ResourceModel\BackgroundTask as BackgroundTaskResource;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Registry;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;

class BackgroundTask extends AbstractModel implements BackgroundTaskInterface
{
    /**
     * @var BackgroundTaskActionLinkFactory
     */
    protected $backgroundTaskActionLinkFactory;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param BackgroundTaskActionLinkFactory $backgroundTaskActionLinkFactory
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        BackgroundTaskActionLinkFactory $backgroundTaskActionLinkFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->backgroundTaskActionLinkFactory = $backgroundTaskActionLinkFactory;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(BackgroundTaskResource::class);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->getData('name');
    }

    /**
     * @param string $name
     *
     * @return BackgroundTaskInterface
     */
    public function setName(string $name): BackgroundTaskInterface
    {
        $this->setData('name', $name);

        return $this;
    }

    /**
     * @return string
     */
    public function getHandler(): string
    {
        return $this->getData('handler');
    }

    /**
     * @param string $handler
     *
     * @return BackgroundTaskInterface
     */
    public function setHandler(string $handler): BackgroundTaskInterface
    {
        $this->setData('handler', $handler);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getArgs()
    {
        $args = $this->getData('args') ?? '';
        $result = json_decode($args, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $result = $args;
        }

        return $result;
    }

    /**
     * @param $args
     *
     * @return BackgroundTaskInterface
     */
    public function setArgs($args): BackgroundTaskInterface
    {
        if (is_string($args)) {
            $decodedArgs = json_decode($args, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $this->setData('args', json_encode($decodedArgs));
            } else {
                $this->setData('args', json_encode($args));
            }
        } else {
            $this->setData('args', json_encode($args));
        }

        return $this;
    }

    /**
     * @return string|null
     */
    public function getStatus(): ?string
    {
        return $this->getData('status');
    }

    /**
     * @param string|null $status
     *
     * @return BackgroundTaskInterface
     */
    public function setStatus(?string $status): BackgroundTaskInterface
    {
        $this->setData('status', $status);

        return $this;
    }

    /**
     * @param string $message
     *
     * @return BackgroundTaskInterface
     */
    public function addMessage(string $message): BackgroundTaskInterface
    {
        if ($this->getMessages()) {
            $this->setMessages([$this->getMessages(), $message]);
        } else {
            $this->setMessages([$message]);
        }

        return $this;
    }

    /**
     * @return string|null
     */
    public function getMessages(): ?string
    {
        return $this->getData('messages');
    }

    /**
     * @param string[]|null $messages
     *
     * @return BackgroundTaskInterface
     */
    public function setMessages(?array $messages): BackgroundTaskInterface
    {
        if ($messages === null) {
            $this->unsetData('messages');
        } else {
            $this->setData('messages', implode(' | ', $messages));
        }

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCreatedAt(): ?string
    {
        return $this->getData('created_at');
    }

    /**
     * @param string|null $createdAt
     *
     * @return BackgroundTaskInterface
     */
    public function setCreatedAt(?string $createdAt): BackgroundTaskInterface
    {
        $this->setData('created_at', $createdAt);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getExecutedAt(): ?string
    {
        return $this->getData('executed_at');
    }

    /**
     * @param string|null $executedAt
     *
     * @return BackgroundTaskInterface
     */
    public function setExecutedAt(?string $executedAt): BackgroundTaskInterface
    {
        $this->setData('executed_at', $executedAt);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFinishedAt(): ?string
    {
        return $this->getData('finished_at');
    }

    /**
     * @param string|null $finishedAt
     *
     * @return BackgroundTaskInterface
     */
    public function setFinishedAt(?string $finishedAt): BackgroundTaskInterface
    {
        $this->setData('finished_at', $finishedAt);

        return $this;
    }

    /**
     * @return BackgroundTaskActionLinkInterface
     */
    public function getActionLink(): BackgroundTaskActionLinkInterface
    {
        $actionLinkData = $this->getData('action_link');

        if ($actionLinkData === null) {
            $actionLinkData = [
                'text' => null,
                'route_path' => null,
                'route_params' => null
            ];
        } elseif (is_string($actionLinkData)) {
            $actionLinkData = json_decode($actionLinkData, true);
        }

        $actionLink = $this->backgroundTaskActionLinkFactory->create();
        $actionLink->setText($actionLinkData['text'])
            ->setRoutePath($actionLinkData['route_path'])
            ->setRouteParams($actionLinkData['route_params']);

        return $actionLink;
    }

    /**
     * @param BackgroundTaskActionLinkInterface|null $actionLink
     *
     * @return BackgroundTaskInterface
     */
    public function setActionLink(?BackgroundTaskActionLinkInterface $actionLink): BackgroundTaskInterface
    {
        $actionLink = $actionLink ?? $this->backgroundTaskActionLinkFactory->create();

        $this->setData('action_link', json_encode([
            'text' => $actionLink->getText(),
            'route_path' => $actionLink->getRoutePath(),
            'route_params' => $actionLink->getRouteParams()
        ]));

        return $this;
    }
}
