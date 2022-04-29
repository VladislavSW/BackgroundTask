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

use Magento\Framework\Message\CollectionFactory;
use Magento\Framework\Message\ExceptionMessageFactoryInterface;
use Magento\Framework\Message\Factory;
use Magento\Framework\Message\Session;
use Magento\Framework\UrlInterface;
use Magento\Framework\Message\Manager;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface;

class MessageManager extends Manager
{
    /**
     * @var UrlInterface
     */
    protected $urlInterface;

    /**
     * @param Session $session
     * @param Factory $messageFactory
     * @param CollectionFactory $messagesFactory
     * @param EventManager $eventManager
     * @param LoggerInterface $logger
     * @param UrlInterface $urlInterface
     * @param string $defaultGroup
     * @param ExceptionMessageFactoryInterface|null $exceptionMessageFactory
     */
    public function __construct(
        Session $session,
        Factory $messageFactory,
        CollectionFactory $messagesFactory,
        EventManager $eventManager,
        LoggerInterface $logger,
        UrlInterface $urlInterface,
        $defaultGroup = Manager::DEFAULT_GROUP,
        ExceptionMessageFactoryInterface $exceptionMessageFactory = null
    ) {
        parent::__construct(
          $session,
          $messageFactory,
          $messagesFactory,
          $eventManager,
          $logger,
          $defaultGroup,
          $exceptionMessageFactory
        );
        $this->urlInterface = $urlInterface;
    }

    /**
     * @param mixed $taskId
     * @param string $additionalMessage
     *
     * @return void
     */
    public function addBackgroundTaskNotice($taskId, string $additionalMessage = ''): void
    {
        $backgroundTaskListUrl = $this->urlInterface->getUrl(
            'scandiweb_backgroundtask/tasks/view',
            ['_secure' => true]
        );

        $this->addComplexNoticeMessage('backgroundTaskNotice', [
            'task_id' => $taskId,
            'background_task_list_url' => $backgroundTaskListUrl,
            'additional_message' => $additionalMessage
        ]);
    }
}
