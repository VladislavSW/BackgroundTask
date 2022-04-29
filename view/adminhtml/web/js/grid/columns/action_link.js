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
            bodyTmpl: 'Scandiweb_BackgroundTask/ui/grid/cells/action_link'
        },
        isLinkAvailable: function (row)
        {
            let result = false;
            const actionLinkData = row.action_link;

            if (actionLinkData) {
                const actionLinkObj = JSON.parse(actionLinkData);

                result = actionLinkObj.url && actionLinkObj.text;
            }

            return result;
        },
        getActionUrl: function (row)
        {
            const actionLinkObj = JSON.parse(row.action_link);
            return actionLinkObj.url;
        },
        getActionText: function (row)
        {
            const actionLinkObj = JSON.parse(row.action_link);
            return actionLinkObj.text;
        },
        getTarget: function (row)
        {
            const actionLinkObj = JSON.parse(row.action_link);
            return actionLinkObj.target || '_self';
        }
    });
});
