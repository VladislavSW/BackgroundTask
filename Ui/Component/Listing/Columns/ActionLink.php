<?php
/**
 * Scandiweb_BackgroundTask
 *
 * @category Scandiweb
 * @package  Scandiweb_BackgroundTask
 * @author   Vladislavs Piscikovs <vladislavs@scandiweb.com | info@scandiweb.com>
 */

declare(strict_types=1);

namespace Scandiweb\BackgroundTask\Ui\Component\Listing\Columns;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class ActionLink extends Column
{
    /**
     * @var UrlInterface
     */
    protected $url;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $url
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $url,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->url = $url;
    }

    /**
     * @inheritdoc
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item[$this->getData('name')])
                    && $item[$this->getData('name')] !== ''
                ) {
                    $url = '';
                    $actionLink = json_decode($item[$this->getData('name')], true);

                    if (isset($actionLink['route_path'], $actionLink['route_params'])) {
                        $url = $this->url->getUrl(
                            $actionLink['route_path'],
                            $actionLink['route_params']
                        );
                    }

                    $actionLink['url'] = $url;
                    $item[$this->getData('name')] = json_encode($actionLink);
                }
            }
        }

        return $dataSource;
    }
}
