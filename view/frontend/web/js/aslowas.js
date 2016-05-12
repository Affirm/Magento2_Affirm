/**
 * Copyright © 2015 Fastgento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint jquery:true*/
define(["jquery",
    "mage/translate",
    "jquery/ui"
], function ($, $t) {
    "use strict"

    $.widget('mage.aslowas',{
        options: {
            logo: '<img src="data:image/svg+xml;base64,PHN2ZyBpZD0iTGF5ZXJfMSIgZGF0YS1uYW1lPSJMYXllciAxIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA1NzEuNjQgMTY2LjA0Ij48ZGVmcz48c3R5bGU+LmNscy0xe2ZpbGw6IzJiYzJkZjt9PC9zdHlsZT48L2RlZnM+PHRpdGxlPmxvZ290eXBlX2JsdWU8L3RpdGxlPjxwYXRoIGNsYXNzPSJjbHMtMSIgZD0iTTI5OC4zOSwwQTE3LjU3LDE3LjU3LDAsMSwwLDMxNiwxNy41NywxNy41OSwxNy41OSwwLDAsMCwyOTguMzksMFoiIHRyYW5zZm9ybT0idHJhbnNsYXRlKDAgMCkiLz48cmVjdCBjbGFzcz0iY2xzLTEiIHg9IjI4My4zIiB5PSI0Ni42OCIgd2lkdGg9IjI5Ljk5IiBoZWlnaHQ9IjExOS4zMSIvPjxwYXRoIGNsYXNzPSJjbHMtMSIgZD0iTTQwMy40Nyw0My42MWMtMTUsMC0zMi4yNSwxMC44LTM3LjkzLDI0LjM0VjQ2LjY4SDMzNy4wOVYxNjZoMzBWMTEwLjU5YzAtMjMuNDUsOS0zNi41NCwyOC42MS0zNi45MUw0MTIuNDQsNDQuM0E2NC4xNyw2NC4xNywwLDAsMCw0MDMuNDcsNDMuNjFaIiB0cmFuc2Zvcm09InRyYW5zbGF0ZSgwIDApIi8+PHBhdGggY2xhc3M9ImNscy0xIiBkPSJNNTI0LDQzLjY5Yy0xMi44NywwLTI0LjQxLDQuODQtMzIuNSwxMy42MmwtMC40Mi40NS0wLjQxLS40NWMtOC04Ljc4LTE5LjQ4LTEzLjYyLTMyLjM2LTEzLjYyLTI3LjU4LDAtNDcuNiwyMC4xMS00Ny42LDQ3LjgxVjE2NmgyOS41MlY5MC44NmMwLTExLjYzLDcuMS0xOS4xNCwxOC4wOC0xOS4xNHMxOC4wOSw3LjUxLDE4LjA5LDE5LjE0VjE2Nkg1MDZWOTAuODZjMC0xMS42Myw3LjEtMTkuMTQsMTguMDktMTkuMTRzMTguMDksNy41MSwxOC4wOSwxOS4xNFYxNjZoMjkuNTFWOTEuNUM1NzEuNjQsNjMuOCw1NTEuNjIsNDMuNjksNTI0LDQzLjY5WiIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMCAwKSIvPjxwYXRoIGNsYXNzPSJjbHMtMSIgZD0iTTI0NywzNS42OWMwLTMuOTQuNTMtOC42NSwzLjktMTEuMjQsMy42OS0yLjg3LDkuMDktMi4zNCwxMy40Ni0yLjEybDUuODItMjEuNzItMy4xMS0uMTZjLTEyLjUyLS42Ny0yNS42OS0xLjYtMzYuNjcsNS42Ni05LjMxLDYuMTUtMTMuNDUsMTYuNzQtMTMuNDUsMjcuNjF2MTNIMTgxLjE4di0xMWMwLTMuOTIuNTItOC41OCwzLjgyLTExLjIsMy42OS0yLjkzLDkuMTYtMi4zOCwxMy41My0yLjE2bDUuODItMjEuNzItMy4xMS0uMTZjLTEyLjYyLS42OC0yNS45Mi0xLjYtMzYuOTEsNS44Ni05LjEyLDYuMTgtMTMuMTYsMTYuNjgtMTMuMTYsMjcuNDJ2MTNIMTM3LjY1VjY4LjM3aDEzLjUzVjE2NmgzMFY2OC4zN2gzNS43NlYxNjZoMzBWNjguMzdoMjAuNzdWNDYuNjhIMjQ3di0xMVoiIHRyYW5zZm9ybT0idHJhbnNsYXRlKDAgMCkiLz48cGF0aCBjbGFzcz0iY2xzLTEiIGQ9Ik0xMjIuNDMsMTY2bDAtMTA1LjM2YTE3LjMyLDE3LjMyLDAsMCwwLTE1LjMyLTE3Yy01LjczLS4zNy0xMS44MiwxLjczLTE1LjQxLDYuMzhMMCwxNjZIMjIuNmM5LDAsMTYuMTgtNC42OSwyMS42MS0xMS43TDk1LDkwLjA3VjE2NmgyNy40NVoiIHRyYW5zZm9ybT0idHJhbnNsYXRlKDAgMCkiLz48L3N2Zz4=" style="height:1em; margin:0 .3em .15em;vertical-align:bottom;">'
        },

        /**
         * Create as low as widget
         *
         * @private
         */
        _create: function() {
            var _self = this, options;
            options = {
                apr: this.options.apr,
                month: this.options.month,
                amount: this.options.amount
            };
            // Init options element
            this.options.element = document.getElementById('learn-more');
            affirm.ui.ready(function() {
                _self.updateAffirmAsLowAs(_self.options.amount);
            });
            affirm.ui.payments.get_estimate(options, _self.handleEstimateResponse);
        },

        /**
         * Update affirm as low as
         *
         * @param c This is amount in cents
         */
        updateAffirmAsLowAs: function(c) {
            if ((c == null) || (c > 175000) || (c < 5000)) {
                return;
            }
            if (c) {
                this.options.amount = c;
            }
        },

        /**
         * Handle estimate response
         */
        handleEstimateResponse: function(payment_estimate) {
            var dollars = payment_estimate.payment_string,
            content = $t("Starting at $") + dollars + $t(" a month with") + this.options.logo + $t("Learn more.");
            this.options.element.innerHTML = content;
            if (payment_estimate && payment_estimate.open_modal) {
                this.options.element.onclick = payment_estimate.open_modal;
            }
            this.options.element.style.visibility = "visible";
        }
    });
    return $.mage.aslowas
});
