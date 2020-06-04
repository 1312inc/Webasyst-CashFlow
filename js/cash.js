(function ($) {
    'use strict';
    $.storage = new $.store();
    $.cash = {
        $loading: $('<i class="icon16 loading">'),
        $wa: null,
        $content: null,
        $welcome: null,
        $sidebar: null,
        defaults: {
            isAdmin: false,
            routingOptions: {},
            userId: 0
        },
        options: {},
        skipHighlightSidebar: false,
        highlightSidebar: function ($li, href) {
            if (this.skipHighlightSidebar) {
                return;
            }

            var self = this;

            var $all_li = self.$sidebar.find('li');
            if ($li) {
                $all_li.removeClass('selected');
                $li.addClass('selected');
            } else if (href) {
                $all_li.removeClass('selected');
                $li = self.$sidebar.find('a[href^="' + href + '"]').first().closest('li');
                $li.addClass('selected');
            } else {
                var hash = $.cash_routing.getHash(),
                    $a = self.$sidebar.find('a[href="' + hash + '"]');

                if (hash) {
                    $all_li.removeClass('selected');
                }
                if ($a.length) { // first find full match
                    $a.closest('li').addClass('selected');
                } else { // more complex hash
                    hash = hash.split("/");
                    if (hash[1]) {
                        while (hash.length) {
                            hash.pop();
                            var href = hash.join('/');

                            var $found_li = self.$sidebar.find('a[href^="' + href + '"]').first().closest('li');
                            if ($found_li.length) {
                                $found_li.addClass('selected');
                                break;
                            }
                        }
                    } else {
                        $all_li.removeClass('selected')
                            .first().addClass('selected');
                    }
                }
            }
        },
        reloadSidebar: function () {
            $.get('?module=backend&action=sidebar', function (html) {
                $.cash.$sidebar.html(html);
                $.cash.highlightSidebar();
                $.cash.sortable($.cash.$sidebar.find('[data-sortable-type]'));
            });
        },
        throttle: function(fn, wait) {
            var time = Date.now(),
                wait = wait || 300;
            return function() {
                if ((time + wait - Date.now()) < 0) {
                    fn.call(this);
                    time = Date.now();
                }
            }
        },
        log: function(msg) {
            console.log('[CASH]', msg);
        },
        init: function (o) {
            var self = this;
            self.options = $.extend({}, self.defaults, o);
            self.$wa = $('#wa-app');
            self.$content = $('#cash-content');
            self.$sidebar = $('#cash-left-sidebar');
            self.$welcome = $('#cash-welcome');

            self.handlers();
            self.sortable(self.$sidebar.find('[data-sortable-type]'));

            function scrollActions() {
                var $this = $('.c-actions-menu');

                if ($this.length) {
                    var $w = $this.parent(),
                        $window = $(window),
                        elementTop = $w.offset().top,
                        elementBottom = elementTop + $w.outerHeight(),
                        viewportTop = $window.scrollTop(),
                        viewportBottom = viewportTop + $window.height(),
                        isInViewport = elementBottom > viewportTop;

                    isInViewport ? $this.removeClass('fixed') : $this.addClass('fixed');
                }
            }

            setInterval(scrollActions, 250);
            // window.addEventListener('scroll', $.cash.throttle(scrollActions, 500));
        },
        handlers: function () {
            var self = this;

            self.$wa
                .on('click.cash', '[data-cash-action="account-dialog"]', function (e) {
                e.preventDefault();
                var $this = $(this),
                    accountId = $this.data('cash-account-id');

                $('#cash-transaction-dialog').waDialog({
                    'height': '400px',
                    'width': '600px',
                    'url': '?module=account&action=dialog&account_id=' + accountId,
                    onLoad: function () {
                        var d = this,
                            $dialogWrapper = $(d);

                        $dialogWrapper
                            .on('click', '[data-cash-action="delete-account"]', function (e) {
                                e.preventDefault();

                                if(!confirm($_('DANGER: This will permanently delete the entire account and ALL TRANSACTIONS without the ability to restore. Are you sure?'))) {
                                    return;
                                }

                                var id = $dialogWrapper.find('form input[name="account[id]"]').val();
                                $.post(
                                    '?module=account&action=delete',
                                    { id: id },
                                    function (r) {
                                        if (r.status === 'ok') {
                                            $dialogWrapper.trigger('close');
                                            $.cash_routing.dispatch('#/account/0');
                                            $.cash.reloadSidebar();
                                        }
                                    }
                                );
                            })
                        ;

                        $dialogWrapper.on('click', '[data-cash-account-icon]', function (e) {
                            e.preventDefault();
                            var $this = $(this);

                            $this.addClass('selected')
                                .siblings().removeClass('selected');

                            $dialogWrapper.find('[name="account[icon]"]').val($this.data('cash-account-icon'));
                            $dialogWrapper.find('[name="account[icon_link]"]').val('');
                        });

                        setTimeout(function () {
                            $dialogWrapper.find('[name="account[name]"]').trigger('focus');
                        }, 13.12);
                    },
                    onSubmit: function (d) {
                        var $errorMsg = d.find('.errormsg');

                        $errorMsg.hide();
                        d.find('.dialog-buttons input[type="button"]').after($.cash.$loading);
                        $.post('?module=account&action=save', d.find('form').serialize(), function (r) {
                            $.cash.$loading.remove();
                            if (r.status === 'ok') {
                                d.trigger('close');
                                var newHash = '#/account/' + r.data.id;
                                if (window.location.hash === newHash) {
                                    $.cash_routing.redispatch();
                                } else {
                                    window.location.hash = newHash;
                                }
                                setTimeout($.cash.reloadSidebar, 1312)
                            } else {
                                $errorMsg.text(r.errors.join('<br>')).show();
                            }
                        }, 'json');
                        return false;
                    }
                });
            })
            .on('click.cash', '[data-cash-action="category-dialog"]', function (e) {
                e.preventDefault();
                var $this = $(this),
                    categoryId = $this.data('cash-category-id'),
                    categoryType = $this.data('cash-category-type'),
                    afterSave = $this.data('cash-category-after-save');

                $('#cash-transaction-dialog').waDialog({
                    'height': '300px',
                    'width': '600px',
                    'url': '?module=category&action=dialog&category_id=' + categoryId,
                    onLoad: function () {
                        var d = this,
                            $dialogWrapper = $(d);

                        $dialogWrapper
                            .on('click', '[data-cash-action="delete-category"]', function (e) {
                                e.preventDefault();

                                if(!confirm($_('DANGER: This will permanently delete the entire category and ALL TRANSACTIONS without the ability to restore. Are you sure?'))) {
                                    return;
                                }

                                var id = $dialogWrapper.find('form input[name="category[id]"]').val();
                                $.post(
                                    '?module=category&action=delete',
                                    { id: id },
                                    function (r) {
                                        if (r.status === 'ok') {
                                            $dialogWrapper.trigger('close');
                                            if (afterSave === 'redispatch') {
                                                $.cash_routing.redispatch();
                                            } else {
                                                $.cash_routing.dispatch('#/account/0');
                                            }
                                            $.cash.reloadSidebar();
                                        }
                                    }
                                );
                            })
                            .on('click', '[data-cash-category-color]', function (e) {
                                e.preventDefault();
                                var $this = $(this);

                                $this.addClass('selected')
                                    .siblings().removeClass('selected');

                                $dialogWrapper.find('[name="category[color]"]').val($this.data('cash-category-color'));
                        });

                        if (categoryType) {
                            $dialogWrapper.find('[name="category[type]"] option[value="' + categoryType + '"]').prop('selected', true);
                        }

                        setTimeout(function () {
                            $dialogWrapper.find('[name="category[name]"]').trigger('focus');
                        }, 13.12);
                    },
                    onSubmit: function (d) {
                        var $errorMsg = d.find('.errormsg');

                        $errorMsg.hide();
                        d.find('.dialog-buttons input[type="button"]').after($.cash.$loading);
                        $.post('?module=category&action=save', d.find('form').serialize(), function (r) {
                            $.cash.$loading.remove();
                            if (r.status === 'ok') {

                                d.trigger('close');
                                var newHash = '#/category/' + r.data.id;
                                if (afterSave === 'redispatch') {
                                    $.cash_routing.redispatch();
                                } else {
                                    if (window.location.hash === newHash) {
                                        $.cash_routing.redispatch();
                                    } else {
                                        window.location.hash = newHash;
                                    }
                                }
                                setTimeout($.cash.reloadSidebar, 1312);
                            } else {
                                $errorMsg.text(r.errors.join('<br>')).show();
                            }
                        }, 'json');
                        return false;
                    }
                });
            })

            $('body')
                .on('change.cash', '[data-cash-element-account-with-sign]', function (e) {
                    var $select = $(this),
                        $dialog = $select.closest('.dialog'),
                        $selected = $select.find(':selected'),
                        sign = $selected.data('cash-account-currency-sign');

                    $dialog
                        .find('[data-cash-transaction-account-currency]')
                        .text(sign);
                })
                .on('change.cash', '[data-cash-element-account-with-icon]', function (e) {
                    var $select = $(this),
                        $wrapper = $select.closest('.field'),
                        $selected = $select.find(':selected'),
                        icon = $selected.data('cash-account-icon'),
                        iconLink = $selected.data('cash-account-icon-link');

                    if (icon) {
                        $wrapper.find('[data-cash-transaction-account-icon]')
                            .removeClass().addClass('icon16 ' + icon).removeAttr('style');
                    } else {
                        $wrapper.find('[data-cash-transaction-account-icon]')
                            .removeClass().addClass('icon16')
                            .css({
                                'background-image': 'url(' + iconLink + ')',
                                'background-size': '16px 16px'
                            });
                    }
                })
                .on('change.cash', '[data-cash-element-category-with-color]', function (e) {
                    var $select = $(this),
                        $wrapper = $select.closest('.field');

                    $wrapper
                        .find('[data-cash-transaction-category]')
                        .css('background', $select.find(':selected').data('cash-category-color'))
                });

            self.$sidebar
                .on('click.cash', '[data-cash-action="imports-delete"]', function (e) {
                e.preventDefault();

                if (!confirm($_('Clear import history (don’t worry, imported transactions will not be affected)?'))) {
                    return;
                }

                $.post(
                    '?module=import&action=deleteAll',
                    function (r) {
                        if (r.status === 'ok') {
                            self.reloadSidebar();
                        } else {
                            self.log(r.errors.join("\n"));
                        }
                    },
                    'json'
                );
            })
        },
        sortable: function ($w) {
            var self = this;
            if (!self.options.isAdmin) {
                return;
            }

            $w.sortable({
                items: '[data-id]',
                distance: 5,
                placeholder: 'pl-list-placeholder',
                opacity: 0.75,
                appendTo: 'body',
                tolerance: 'pointer',
                classes: {
                    'ui-sortable-helper': 'shadowed'
                },
                start: function (e, ui) {
                    ui.placeholder.height(ui.helper.outerHeight());
                },
                stop: function (event, ui) {
                    var $wrapper = ui.item.closest('[data-sortable-type]'),
                        type = $wrapper.data('sortable-type');

                    var getIds = function () {
                        var data = [];
                        $wrapper.find('li').each(function (i) {
                            var $this = $(this);
                            // color = $this.attr('class').match(/pl-(.*)/);
                            data.push($this.data('id'));
                        });
                        return data;
                    };

                    var updateSort = function () {
                        $.post(
                            '?module='+type+'&action=sort',
                            {
                                data: getIds()
                            },
                            function (r) {
                                if (r.status === 'ok') {
                                } else {
                                    alert(r.errors);
                                }
                            },
                            'json'
                        );
                    };

                    updateSort();
                }
            });
        },
        loadTransactions: function (startDate, endDate, filterId, filterType, $htmlW) {
            $.get('?module=transaction&action=list', {
                'start_date': startDate,
                'end_date': endDate,
                'id': filterId,
                'filter': filterType
            }, function (html) {
                $htmlW.html(html);
            });
        },
        loadGraphData: function (startDate, endDate, filterId, filterType, bindToSelector) {
            $.get('?module=transaction&action=graphData', {
                'start_date': startDate,
                'end_date': endDate,
                'id': filterId,
                'filter': filterType
            }, function (r) {
                if (r.status === 'ok' && !r.data.empty) {
                    var graph = r.data;

                    if (graph.axis['y']) {
                        graph.axis.y = $.extend({}, graph.axis.y, {
                            tick: {
                                format: function (d) { return ('' + d3.format('~s')(d)).toUpperCase(); }
                            }
                        });
                    }

                    if (graph.axis['y2']) {
                        graph.axis.y2 = $.extend({}, graph.axis.y2, {
                            tick: {
                                format: function (d) { return ('' + d3.format('~s')(d)).toUpperCase(); }
                            }
                        });
                    }

                    var chart = c3.generate({
                        bindto: bindToSelector,
                        data: graph.data,
                        axis: graph.axis,
                        grid: graph.grid,
                        line: graph.line,
                        regions: graph.regions,
                        legend: graph.legend,
                        tooltip: {
                            format: {
                                value: function (value, ratio, id, index, t) {
                                    return ('' + d3.format(',')(value)).replace(',',' ') + ' ' + graph.helpers.currencyNames[id].sign;
                                }
                            }
                        }
                    });

                    $(bindToSelector).data('c3-chart', chart);
                }
            })
        },
        capitalizeFirstLetter: function(string) {
            // https://stackoverflow.com/questions/1026069/how-do-i-make-the-first-letter-of-a-string-uppercase-in-javascript
            return string.charAt(0).toUpperCase() + string.slice(1);
        }
    }
}(jQuery));
