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
use Scandiweb\BackgroundTask\Model\ResourceModel\BackgroundTask as BackgroundTaskResource;
use Magento\Framework\Model\AbstractModel;

class BackgroundTask extends AbstractModel implements BackgroundTaskInterface
{
    /**
     * @var string
     */
    protected $message;

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
        $args = $this->getData('args');
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
     * @return string|null
     */
    public function getMessage(): ?string
    {
        return $this->getData('message');
    }

    /**
     * @param string|null $message
     *
     * @return BackgroundTaskInterface
     */
    public function setMessage(?string $message): BackgroundTaskInterface
    {
        if (!$message) {
            $this->unsetData('message');
            $this->message = $message;
        } elseif ($this->message) {
            $this->message .= ' | ' . $message;
        } else {
            $this->message = $message;
        }

        $this->setData('message', $this->message);

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
}
