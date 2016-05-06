/**
 * Astound
 * NOTICE OF LICENSE
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to codemaster@astoundcommerce.com so we can send you a copy immediately.
 *
 * @category  Affirm
 * @package   Astound_Affirm
 * @copyright Copyright (c) 2016 Astound, Inc. (http://www.astoundcommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

define([
    'jquery',
    'Magento_Ui/js/modal/confirm'
], function ($, confirm) {
    'use strict';

    $('#save').on('click', 'span.ui-button-text', function(){
        var msg = $.mage.__('Are you sure you want to do this?');
        confirm({
            'content': msg,
            'actions': {
                /**
                 * 'Confirm' action handler.
                 */
                confirm: function () {
                    getForm().submit();
                }
            }
        });

        return false;
    });

    /**
     * Get address form
     *
     * @returns {Object}
     */
    function getForm() {
        return $('#edit_form');
    }
});
