/**
 * Copyright © Thuiswinkel.org. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Ui/js/lib/validation/utils'
], function ($, utils) {
    'use strict';

    return function (validator) {
        validator.addRule(
            'required-entry-if-bewust-bezorgen-three-legs',
            function (value) {
                return !($('select[name="product[bewustbezorgd_legs]"]').val() === 2 && utils.isEmpty(value));
            },
            $.mage.__('"This is a required field when "Bewustbezorgd Legs" is chosen as "Regular Package"')
        );

        return validator;
    };
});