<?php
/**
 * Scandiweb_BackgroundTask
 *
 * @category Scandiweb
 * @package  Scandiweb_BackgroundTask
 * @author   Vladislavs Piscikovs <vladislavs@scandiweb.com | info@scandiweb.com>
 */

declare(strict_types=1);

use Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(
    ComponentRegistrar::MODULE,
    'Scandiweb_BackgroundTask',
    __DIR__
);
