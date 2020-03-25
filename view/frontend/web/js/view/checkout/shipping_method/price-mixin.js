/**
 * Copyright © Thuiswinkel.org. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'jquery',
    'knockout',
    'Magento_Checkout/js/model/shipping-service',
    'uiRegistry',
], function (
    _,
    $,
    ko,
    shippingService,
    registry,
) {
    'use strict';

    function getSvgLogoUrl() {
        return window.checkoutConfig.thuiswinkelBewustBezorgd.shippingMethods.emission_logo_url_svg;
    }

    function getPngLogoUrl() {
        return window.checkoutConfig.thuiswinkelBewustBezorgd.shippingMethods.emission_logo_url_png;
    }

    function canShowLogo() {
        return window.checkoutConfig.thuiswinkelBewustBezorgd.shippingMethods.can_show_logo;
    }

    function findMethodLabel(methodTable, method) {
        var methodLabel = methodTable.find('[id="label_method_' + method.method_code + '_' + method.carrier_code + '"]');
        if (!methodLabel.length) {
            methodLabel = methodTable.find('[id="s_method_' + method.carrier_code + '_' + method.method_code + '"]');
        }

        return methodLabel
    }

    function findMethodRow(methodTable, method) {
        var methodLabel = findMethodLabel(methodTable, method);

        return methodLabel.closest('tr')
    }

    function appendIconColumn(viewModel, methodTable) {
        if ($(methodTable).find('tr td.col-emission').length === 0) {
            var heading = $('<th class="col col-emission" data-bind="i18n: \'\'"></th>');
            heading.appendTo(methodTable.find('thead tr'));
            var column = $('<td class="col col-emission"></td>');
            column.appendTo(methodTable.find('tbody tr, tfoot tr'));
            ko.applyBindings(viewModel, heading[0]);
        }
    }

    function appendMethodIcons(viewModel, methodTable) {
        if (viewModel.rates().length > 1) {
            _.each(viewModel.rates(), function (method) {
                // Can't use ID selection, must use attr selection, because methods may have special chars
                var row = findMethodRow(methodTable, method);
                if (row.length && method.extension_attributes && method.extension_attributes.most_efficient) {
                    row.find('.col-emission').remove(); // Delete previous tooltip if exists
                    var icon = $('' +
                        '<td class="col col-emission">\n' +
                        '    <img class="emission-logo-img" src="' + getSvgLogoUrl() + '" width="30"\n' +
                        '         onerror="this.onerror=null; this.src=\'' + getPngLogoUrl() + '\'"/>\n' +
                        '</td>');
                    icon.appendTo(row);
                    ko.applyBindings(method, icon[0]);
                }
            });
        }
    }

    function appendEmissionData() {
        if (!canShowLogo()) {
            return true;
        }
        var methodTbl = $('#opc-shipping_method .table-checkout-shipping-method');
        var shippingVM = registry.get("checkout.steps.shipping-step.shippingAddress");

        if (methodTbl.length && methodTbl.find('tbody tr').length && shippingVM) {
            appendIconColumn(shippingVM, methodTbl);
            appendMethodIcons(shippingVM, methodTbl);

            return true;
        }

        return false;
    }

    return function (target) {
        appendEmissionData();
        shippingService.getShippingRates().subscribe(appendEmissionData);
        $(document).on('ajaxComplete', function (event, xhr, settings) {
            if (xhr.readyState === 4) {
                appendEmissionData();
            }
        });

        return target;
    };
});
