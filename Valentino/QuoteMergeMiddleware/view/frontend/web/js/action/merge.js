/**
 * 
 */

define([    
    'jquery',
    'mage/storage',
    'mage/url'
], function ($,storage,urlBuilder) {
    'use strict';

    return function (deferred, quoteIds) {
        return storage.post(
           'rest/V1/quotemiddleware/mergequotes',
           JSON.stringify({quoteIds: quoteIds}),false
        ).done(function (isQuoteMerged) {
            console.log('merged',isQuoteMerged);
            if (isQuoteMerged) {
                deferred.resolve();
            } else {
                deferred.reject();
            }
        }).fail(function () {
            deferred.reject();
        });
    };
});
