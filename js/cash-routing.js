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
            $.get('?module=backend', function (html) {
                $.cash.$content.html(html);
            });
        },
        oldAction: function (id) {
            var that = this;
            $.get('?module=backend&action=source', function (html) {
                $.cash.$content.html(html);
            });
        },
        preExecute: function () {
        },
        postExecute: function (actionName, hash) {
            if (actionName !== 'y') {
                var value = $.isArray(hash) ? hash.join('/') : '';
                $.storage.set('/cash/hash/' + this.options.user_id, value);
            }
            this.options.self.highlightSidebar();
        },
    };
}());