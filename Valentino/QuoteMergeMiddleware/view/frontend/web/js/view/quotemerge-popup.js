/**
 * 
 */

define([
    'jquery',
    'ko',
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'Valentino_QuoteMergeMiddleware/js/model/quotemerge-popup',
    'Valentino_QuoteMergeMiddleware/js/action/merge'
], function ($, ko, Component, customerData, quotemergePopup, mergeAction) {
    'use strict';

    return Component.extend({
        isLoading: ko.observable(false),
        items: ko.observableArray([]),

        /**
         * Init
         */
        initialize: function () {

            this._super();

            const quoteMergeWatch = customerData.get('customer_active_quote');
            if (!_.isEmpty(quoteMergeWatch()) && !_.isEmpty(quoteMergeWatch()['items'])) {
                this.items(quoteMergeWatch()['items']);
                this.showModal();
            }

            quoteMergeWatch.subscribe(function(newItems) {
                if (!_.isEmpty(newItems) && !_.isEmpty(newItems['items'])) {
                    this.items(newItems['items']);
                    this.showModal();
                } else {
                    this.items([]);
                }
            }, this);         
        },

        /**
         * Sets modal on given HTML element with on demand initialization.
         */
        setModalElement: function (element) {           
            this.createPopup(element);
            if(this.items().length) {
                this.showModal();
            }        
        },

        /**
         * Initializes authentication modal on given HTML element.
         */
        createPopup: function (element) {
            if (quotemergePopup.modalWindow === null) {
                quotemergePopup.createPopUp(element);
            }
        },

        /** Show login popup window */
        showModal: function () {
            if (quotemergePopup.modalWindow !== null) {
                quotemergePopup.showModal();
            }
        }, 

        merge: function() {
            const mergeCompleted = $.Deferred();
            
            const quotes = [];
            $('.quotemerge-items-wrapper .quotemerge-items input:checkbox:checked').each(function() {
                quotes.push($(this).val());
            });

            this.isLoading(true);
            mergeAction(mergeCompleted, quotes);
            
            $.when(mergeCompleted).done(function () {
                
            }.bind(this)).fail(function () {
               
            }.bind(this)).always(function () {
                this.isLoading(false);
            }.bind(this));
        }
    });
});
