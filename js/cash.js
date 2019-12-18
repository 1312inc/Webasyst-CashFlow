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
                var hash = self.routing.getHash(),
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

            $.cash_routing.init(self.options.routingOptions);
        }
    }
}(jQuery));
