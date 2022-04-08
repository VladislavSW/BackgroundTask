/**
 * Scandiweb_BackgroundTask
 *
 * @category Scandiweb
 * @package  Scandiweb_BackgroundTask
 * @author   Vladislavs Piscikovs <vladislavs@scandiweb.com | info@scandiweb.com>
 */

define([
    'Magento_Ui/js/grid/columns/column'
], function (Column) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'Scandiweb_BackgroundTask/ui/grid/cells/status'
        },
        getTaskStatusColor: function (row)
        {
            let statusClass = 'Status';

            switch (row.status) {
                case 'pending':
                    statusClass += '--pending';
                    break;
                case 'running':
                    statusClass += '--running';
                    break;
                case 'success':
                    statusClass += '--success';
                    break;
                case 'error':
                    statusClass += '--error';
                    break;
            }

            return statusClass;
        }
    });
});
