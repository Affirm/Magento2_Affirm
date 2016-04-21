/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'Magento_Checkout/js/model/quote',
        'Magento_GiftWrapping/js/model/gift-wrapping',
        'Magento_Checkout/js/model/totals'
    ],
    function (quote, wrappingModel, totals) {
        'use strict';

        return function () {
            var giftTotalsSegment = totals.getSegment('giftwrapping')['extension_attributes'],
                wrappingInfo = wrappingModel('orderLevel'), existingWrappers = wrappingInfo.getWrappingItems(),
                wrappedItems = giftTotalsSegment.gw_item_ids, index, result, items = [];

            for (var i = 0; i < existingWrappers.length; i++) {
                if (giftTotalsSegment.gw_order_id) {
                    if (existingWrappers[i].id == giftTotalsSegment.gw_order_id) {
                        items[i] = {
                            "display_name": existingWrappers[i].label,
                            "sku": "gift-" + existingWrappers[i].id,
                            "unit_price": parseInt(existingWrappers[i].price * 100),
                            "qty": 1,
                            "item_image_url": existingWrappers[i].path
                        };
                    }
                }
            }
            if (wrappedItems) {
                for (var j=0; j < wrappedItems.length; j++) {
                    index = "gift-"+wrappedItems[j].gw_id;
                    result = false;
                    for (var k = 0; k < items.length; k++) {
                        if(items[k] && items[k].sku == index) {
                            items[k].qty++;
                            result = true;
                            break;
                        }
                    }
                    if (!result) {
                        for (var m = 0; m < existingWrappers.length; m++) {
                            if (existingWrappers[m].id == wrappedItems[j].gw_id) {
                                items.push({
                                    "display_name": existingWrappers[m].label,
                                    "sku": "gift-" + existingWrappers[m].id,
                                    "unit_price": parseInt(existingWrappers[m].price * 100),
                                    "qty": 1,
                                    "item_image_url": existingWrappers[m].path
                                });
                            }
                        }
                    }
                }
            }
            return items;
        };
    }
);
