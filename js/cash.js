(function ($) {
    'use strict';
    $.storage = new $.store();
    $.cash = {
        $loading: $('<i class="icon16 loading">'),
        $wa: null,
        $content: null,
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
            var self = this;

            $.get('?module=backend&action=sidebar', function (html) {
                self.$sidebar.html(html);
            })
        },
        init: function (o) {
            var self = this;
            self.options = $.extend({}, self.defaults, o);
            self.$wa = $('#wa-app');
            self.$content = $('#cash-content');
            self.$sidebar = $('#cash-left-sidebar');

            self.handlers();
        },
        handlers: function () {
            var self = this;

            self.$sidebar.on('click', '[data-cash-action="account-dialog"]', function (e) {
                e.preventDefault();
                var $this = $(this),
                    accountId = $this.data('cash-account-id');

                $('#cash-transaction-dialog').waDialog({
                    'height': '250px',
                    'width': '600px',
                    'url': '?module=account&action=dialog&account_id=' + accountId,
                    onLoad: function () {
                        var d = this,
                            $dialogWrapper = $(d);

                        $dialogWrapper
                            .on('click', '[data-cash-action="delete-account"]', function (e) {
                                e.preventDefault();
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

                        setTimeout(function () {
                            $dialogWrapper.find('[name="account[name]"]').trigger('focus');
                        }, 13.12);
                    },
                    onSubmit: function (d) {
                        d.find('.dialog-buttons input[type="button"]').after($.cash.$loading);
                        $.post('?module=account&action=save', d.find('form').serialize(), function (r) {
                            $.cash.$loading.remove();
                            if (r.status === 'ok') {
                                d.trigger('close');
                                // if (!pocketId) {
                                window.location.hash = '#/account/' + r.data.id;
                                // }
                                $.cash.reloadSidebar();
                            } else {

                            }
                        }, 'json');
                        return false;
                    }
                });
            })
        }
    }
}(jQuery));
