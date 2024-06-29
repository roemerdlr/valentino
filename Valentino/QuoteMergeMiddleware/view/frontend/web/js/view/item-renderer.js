/**
 * 
 */
define([
    'jquery',
    'uiComponent'
], function($, Component) {
    'use strict';

    return Component.extend({

        initialize: function() {
           

            return this._super();
        },

        /**
         * Prepare the product name value to be rendered as HTML
         *
         * @param {String} productName
         * @return {String}
         */
        getProductNameUnsanitizedHtml: function(productName) {
            // product name has already escaped on backend
            return productName;
        },

        /**
         * Prepare the given option value to be rendered as HTML
         *
         * @param {String} optionValue
         * @return {String}
         */
        getOptionValueUnsanitizedHtml: function(optionValue) {
            // option value has already escaped on backend
            return optionValue;
        }
    });
});
