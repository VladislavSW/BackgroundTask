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
     * Task id getter
     *
     * @return int
     */
    public function getId();

    /**
     * Task id setter
     *
     * @param int $id
     *
     * @return $this
     */
    public function setId(int $id);

    /**
     * Task handler getter
     *
     * @return string
     */
    public function getHandler(): string;

    /**
     * Task handler setter
     *
     * @param string $handler
     *
     * @return $this
     */
    public function setHandler(string $handler): BackgroundTaskInterface;

    /**
     * Task handler arguments getter
     *
     * @return mixed
     */
    public function getArgs();

    /**
     * Task handler arguments setter
     *
     * @param mixed $args
     *
     * @return $this
     */
    public function setArgs($args): BackgroundTaskInterface;

    /**
     * Task status getter
     *
     * @return string|null
     */
    public function getStatus(): ?string;

    /**
     * Task status setter
     *
     * @param string|null $status
     *
     * @return $this
     */
    public function setStatus(?string $status): BackgroundTaskInterface;

    /**
     * Task execution message getter
     *
     * @return string|null
     */
    public function getMessage(): ?string;

    /**
     * Task execution message setter
     *
     * @param string|null $message
     *
     * @return BackgroundTaskInterface
     */
    public function setMessage(?string $message): BackgroundTaskInterface;

    /**
     * Task creation date getter
     *
     * @return string|null
     */
    public function getCreatedAt(): ?string;

    /**
     * Task creation date setter
     *
     * @param string|null $createdAt
     *
     * @return BackgroundTaskInterface
     */
    public function setCreatedAt(?string $createdAt): BackgroundTaskInterface;

    /**
     * Task execution date getter
     *
     * @return string|null
     */
    public function getExecutedAt(): ?string;

    /**
     * Task execution date setter
     *
     * @param string|null $executedAt
     *
     * @return BackgroundTaskInterface
     */
    public function setExecutedAt(?string $executedAt): BackgroundTaskInterface;

    /**
     * Finished task date getter
     *
     * @return string|null
     */
    public function getFinishedAt(): ?string;

    /**
     * Finished task date setter
     *
     * @param string|null $finishedAt
     *
     * @return BackgroundTaskInterface
     */
    public function setFinishedAt(?string $finishedAt): BackgroundTaskInterface;
}
