(function ($) {
    'use strict';
    $.storage = new $.store();
    $.cash_routing = {
        options: {
            self: null,
                user_id: 0,
        },
        init: function (options) {
            var that = this;
            that.options = $.extend({}, that.options, options);
            if (typeof ($.History) != "undefined") {
                $.History.bind(function () {
                    that.dispatch();
                });
            }

            var hash = window.location.hash;
            if (hash === '#/' || !hash) {
                hash = $.storage.get('/status/hash/' + that.options.user_id);
                if (hash && hash !== null && hash !== undefined) {
                    $.wa.setHash('#/' + hash);
                } else {
                    this.dispatch();
                }
            } else {
                $.wa.setHash(hash);
            }
        },
        // dispatch() ignores the call if prevHash == hash
        prevHash: null,
            skipScrollToTop: false,
            hash: null,
            /** Current hash. No URI decoding is performed. */
            getHash: function () {
            return this.cleanHash();
        },
        /** Make sure hash has a # in the begining and exactly one / at the end.
         * For empty hashes (including #, #/, #// etc.) return an empty string.
         * Otherwise, return the cleaned hash.
         * When hash is not specified, current hash is used. No URI decoding is performed. */
        cleanHash: function (hash) {
            if (typeof hash == 'undefined') {
                // cross-browser way to get current hash as is, with no URI decoding
                hash = window.location.toString().split('#')[1] || '';
            }

            if (!hash) {
                return '';
            } else if (!hash.length) {
                hash = '' + hash;
            }
            while (hash.length > 0 && hash[hash.length - 1] === '/') {
                hash = hash.substr(0, hash.length - 1);
            }
            hash += '/';

            if (hash[0] != '#') {
                if (hash[0] != '/') {
                    hash = '/' + hash;
                }
                hash = '#' + hash;
            } else if (hash[1] && hash[1] != '/') {
                hash = '#/' + hash.substr(1);
            }

            if (hash == '#/') {
                return '';
            }

            return hash;
        },
        // if this is > 0 then this.dispatch() decrements it and ignores a call
        skipDispatch: 0,
        /** Cancel the next n automatic dispatches when window.location.hash changes */
        stopDispatch: function (n) {
            this.skipDispatch = n;
        },
        /** Implements #hash-based navigation. Called every time location.hash changes. */
        dispatch: function (hash) {
            if (this.skipDispatch > 0) {
                this.skipDispatch--;
                return false;
            }
            if (hash === undefined || hash === null) {
                hash = window.location.hash;
            }
            hash = hash.replace(/(^[^#]*#\/*|\/$)/g, '');
            /* fix syntax highlight*/
            if (this.hash !== null) {
                this.prevHash = this.hash;
            }
            this.hash = hash;
            if (hash) {
                hash = hash.split('/');
                if (hash[0]) {
                    var actionName = "";
                    var attrMarker = hash.length;
                    var lastValidActionName = null;
                    var lastValidAttrMarker = null;
                    for (var i = 0; i < hash.length; i++) {
                        var h = hash[i];
                        if (i < 2) {
                            if (i === 0) {
                                actionName = h;
                            } else if (parseInt(h, 10) != h && h.indexOf('=') == -1) {
                                actionName += h.substr(0, 1).toUpperCase() + h.substr(1);

                            } else {
                                break;
                            }
                            if (typeof (this[actionName + 'Action']) == 'function') {
                                lastValidActionName = actionName;
                                lastValidAttrMarker = i + 1;
                            }
                        } else {
                            break;
                        }
                    }
                    attrMarker = i;

                    if (typeof (this[actionName + 'Action']) !== 'function' && lastValidActionName) {
                        actionName = lastValidActionName;
                        attrMarker = lastValidAttrMarker;
                    }

                    var attr = hash.slice(attrMarker);
                    if (typeof (this[actionName + 'Action']) == 'function') {
                        this.preExecute(actionName);
                        console.info('dispatch', [actionName + 'Action', attr]);
                        this[actionName + 'Action'].apply(this, attr);

                        this.postExecute(actionName, hash);
                    } else {
                        console.info('Invalid action name:', actionName + 'Action');
                    }
                } else {
                    this.preExecute();
                    this.defaultAction();
                    this.postExecute('default', hash);
                }
            } else {
                this.preExecute();
                this.defaultAction();
                this.postExecute('', hash);
            }
        },
        redispatch: function () {
            this.prevHash = null;
            this.dispatch();
        },
        defaultAction: function () {
            this.accountAction(0);
        },
        shopscriptAction: function () {
            var that = this;
            $.get('?module=shop&action=settings', function (html) {
                that.setContent(html);
            });
        },
        importAction: function () {
            var that = this;
            $.get('?module=import', function (html) {
                that.setContent(html);
            });
        },
        accountAction: function (id, start_date, end_date, start, limit) {
            var that = this;
            start_date = start_date || '';
            end_date = end_date || '';
            start = start || '';
            limit = limit || '';

            $.get('?module=transaction', {
                    action: 'page',
                    filter: 'account',
                    id: id,
                    start_date: start_date,
                    end_date: end_date,
                    start: start,
                    limit: limit
                }, function (html) {
                    that.setContent(html);
                });
        },
        categoryAction: function (id, start_date, end_date, start, limit) {
            var that = this;
            start_date = start_date || '';
            end_date = end_date || '';
            start = start || '';
            limit = limit || '';

            $.get('?module=transaction', {
                    action: 'page',
                    filter: 'category',
                    id: id,
                    start_date: start_date,
                    end_date: end_date,
                    start: start,
                    limit: limit
                }, function (html) {
                    that.setContent(html);
                });
        },
        importViewAction: function (provider ,id) {
            var that = this;
            $.get('?module=import&action=' + provider + 'View&id=' + id, function (html) {
                that.setContent(html);
            });
        },
        sourceAction: function (id) {
            var that = this;
            $.get('?module=backend&action=source', function (html) {
                that.setContent(html);
            });
        },
        reportAction: function (report, type, year) {
            type = type || 'category';
            var that = this,
                url = '?module=report&action=dds&type=' + type + '&year=' + year,
                loadReport = function () {
                    url += '&action=' + report;
                    $.get(url, function (html) {
                        that.setContent(html, $.cash.$content.find('#c-report'));
                    });
                };

            // if (!$.cash.$content.find('#c-reports-menu').length) {
                $.get(url, function (html) {
                    that.setContent(html);
                    // if (report) {
                    //     loadReport();
                    // }
                });
            // }
        },
        setContent: function (content, $w) {
            if ($w) {
                $w.html(content);
            } else {
                $.cash.$content.html(content);
            }
            $.cash.$wa.trigger('postExecute.cash', {html: content});
        },
        preExecute: function () {
            var $h1 = $.cash.$content.find('h1:first');

            if ($h1.length) {
                $('html, body').animate({ scrollTop: 0 }, 131.2);
                $h1.append('<i class="icon16 loading"></i>');
            }
        },
        postExecute: function (actionName, hash) {
            var value = $.isArray(hash) ? hash.join('/') : '';
            $.storage.set('/cash/hash/' + this.options.user_id, value);

            $.cash.highlightSidebar();
        },
    };
}(jQuery));