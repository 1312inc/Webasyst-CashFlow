var CashTransactionDialog = (function ($) {
    function initDialog(transactionId, filterType, filterId, categoryType) {
        filterType = filterType || '';
        filterId = filterId || 0;
        categoryType = categoryType || '';
        var action = 'dialog';
        if (categoryType) {
            var actionCategory = categoryType;
            // action += (actionCategory.charAt(0).toUpperCase() + actionCategory.slice(1));
        }

        $('#cash-transaction-dialog').waDialog({
            'height': '440px',
            'width': '650px',
            'url': '?module=transaction&action=' + action + '&transaction_id=' + transactionId + '&category_type=' + categoryType + '&filter_id=' + filterId + '&filter_type=' + filterType,
            onLoad: function () {
                var d = this,
                    $dialogWrapper = $(d),
                    $account = $dialogWrapper.find('[name="transaction[account_id]"]'),
                    $transferAccount = $dialogWrapper.find('[name="transfer[account_id]"]'),
                    $contractor = $dialogWrapper.find('[name="transaction[contractor_contact_id]"]'),
                    $contractorAutocomplete = $dialogWrapper.find('[data-cash-contractor]');

                $dialogWrapper
                    .on('click.cash', '[data-cash-action="delete-transaction"]', function (e) {
                        e.preventDefault();

                        if (!confirm($_('The transaction will be permanently deleted. Are you sure?'))) {
                            return;
                        }

                        var id = $dialogWrapper.find('form input[name="transaction[id]"]').val(),
                            all = $dialogWrapper.find('form :checkbox[name="repeating[apply_to_all_in_future]"]').is(':checked');
                        $.post(
                            '?module=transaction&action=delete',
                            {id: id, all: all},
                            function (r) {
                                if (r.status === 'ok') {
                                    $dialogWrapper.trigger('close');
                                    $.cash_routing.redispatch();
                                    $.cash.reloadSidebar();
                                }
                            }
                        );
                    })
                    .on('change.cash', '[name="repeating[interval]"]:first', function (e) {
                        var $selected = $(this).find(':selected'),
                            $customFrequency = $dialogWrapper.find('[data-cash-repeating-transaction-frequency-custom]'),
                            interval = $selected.data('cash-repeating-transaction-interval');

                        if (interval === 'custom') {
                            $customFrequency.show().find(':input').prop('disabled', false);
                        } else {
                            $customFrequency.hide().find(':input').prop('disabled', true);
                        }

                        if (!$selected.val()) {
                            $dialogWrapper.find('[name="repeating[end_type]"]').closest('.field').hide();
                        } else {
                            $dialogWrapper.find('[name="repeating[end_type]"]').closest('.field').show();
                        }
                    })
                    .on('change.cash', '[name="repeating[end_type]"]', function (e) {
                        var $this = $(this),
                            $selected = $this.find(':selected'),
                            typeEnd = $selected.data('cash-repeating-transaction-end'),
                            $repeatEnd = $dialogWrapper.find('[data-cash-repeating-transaction-end-' + typeEnd + ']');

                        $this.siblings().hide();
                        $repeatEnd.show();
                    })
                    .on('click.cash', '[name="repeating[apply_to_all_in_future]"]', function (e) {
                        // $dialogWrapper.find('[data-cash-repeating-settings]').toggle();
                        var $submit = $dialogWrapper.find('[type="submit"]'),
                            $delete = $dialogWrapper.find('[data-cash-action="delete-transaction"]'),
                            $date = $dialogWrapper.find('[name="transaction[date]"]'),
                            oldDate = $date.data('cash-date');

                        if ($(this).is(':checked')) {
                            $submit.val($submit.data('cash-repeating-transaction-text'));
                            $delete.find('span').text($delete.data('cash-repeating-transaction-text'));
                            $date
                                .prop('disabled', true)
                                .val(oldDate)
                                .trigger('change.cash');
                        } else {
                            $submit.val($submit.data('cash-transaction-text'));
                            $delete.find('span').text($delete.data('cash-transaction-text'));
                            $date.prop('disabled', false);
                        }
                    })
                    .on('change.cash', '[name="transaction[amount]"]', function (e) {
                        var $this = $(this),
                            value = $this.val(),
                            $transferValue = $dialogWrapper.find('[name="transfer[incoming_amount]"]'),
                            accountCurrency = $account.find(':selected').data('cash-account-currency-code'),
                            transferAccountCurrency = $transferAccount.find(':selected').data('cash-account-currency-code');

                        if (accountCurrency == transferAccountCurrency && $transferValue.val() != value) {
                            $transferValue.val(value);
                        }
                    })
                    .on('change.cash', '[data-cash-element-account-with-sign]', function (e) {
                        var $this = $(this),
                            sign = $this.find(':selected').data('cash-account-currency-sign'),
                            code = $this.find(':selected').data('cash-account-currency-code'),
                            $sign = $dialogWrapper.find('[' + $this.data('cash-element-account-with-sign') + '-sign]'),
                            $code = $dialogWrapper.find('[' + $this.data('cash-element-account-with-sign') + '-code]');

                        $sign.html(sign);
                        $code.html(code);
                    })
                    .on('change.cash', '[name="transaction[account_id]"], [name="transfer[account_id]"]', function (e) {
                        var accountCurrency = $account.find(':selected').data('cash-account-currency-code'),
                            transferAccountCurrency = $transferAccount.find(':selected').data('cash-account-currency-code'),
                            $transferValue = $dialogWrapper.find('[name="transfer[incoming_amount]"]'),
                            $transferAmount = $dialogWrapper.find('[data-cash-transaction-dialog-transfer-amount]');

                        $transferAmount.toggle(accountCurrency != transferAccountCurrency);
                        if (accountCurrency == transferAccountCurrency) {
                            $transferValue.val($dialogWrapper.find('[name="transaction[amount]"]').val());
                        }
                    })
                ;

                $dialogWrapper.find('select').trigger('change.cash');

                if ($contractorAutocomplete.length) {
                    var newContact = false,
                        somethingFound = true,
                        selectedContact = '',
                        $pic = $contractorAutocomplete.closest('.value').find('i.userpic20'),
                        $hint = $contractorAutocomplete.closest('.value').find('.hint'),
                        newContactHint = $_('New contact will be created'),
                        searchContactHint = $_('Search for existing contact or enter any new contact name.'),
                        selectItem = function (event, ui) {
                            $pic.css('background-image', 'url(' + ui.item.photo_url + ')');
                            newContact = false;
                            selectedContact = ui.item.name;
                            $contractor.val(ui.item.id);
                            $hint.text('');
                            this.value = ui.item.name;

                            return false;
                        };

                    $contractorAutocomplete.autocomplete({
                        source: function (request, response) {
                            $.getJSON('?module=json&action=contactAutocomplete', request, function (r) {
                                response(r.data);
                                somethingFound = !!r.data.length;
                                $contractorAutocomplete.trigger('change.cash');
                            });
                        },
                        delay: 300,
                        minLength: 1,
                        select: selectItem,
                        focus: selectItem
                    });
                    $contractorAutocomplete.on('keyup.cash, change.cash', function (e) {
                        var val = $(this).val().trim();

                        if (val !== selectedContact && !somethingFound) {
                            selectedContact = '';
                            $contractor.val(0);
                            newContact = true;
                            $hint.text(newContactHint);
                            $pic.css('background-image', 'url(' + $pic.data('cash-default-url') + ')');
                        }

                        if (!val) {
                            newContact = false;
                            $hint.text(searchContactHint);
                        }
                    });
                    $contractorAutocomplete.data('ui-autocomplete')._renderItem = function (ul, item) {
                        return $('<li class="c-autocomplete-item">')
                            .append(item.label)
                            .appendTo(ul);
                    };
                }

                $dialogWrapper.find('#c-transaction-type').iButton({
                    labelOn: '',
                    labelOff: '',
                    classContainer: 'ibutton-container mini',
                    change: function ($input) {
                        var $w = $input.closest('.menu-h'),
                            $left = $dialogWrapper.find('li:first'),
                            $right = $dialogWrapper.find('li:last'),
                            $repeating = $dialogWrapper.find('[data-cash-repeating-settings]'),
                            $date = $dialogWrapper.find('[name="transaction[date]"]').closest('.field').find('.name');

                        $left.find('span').toggleClass('gray');
                        $right.find('span').toggleClass('gray');

                        if ($input.is(':checked')) {
                            $repeating.show();
                            $date.text($_('Start repeat'));
                        } else {
                            $repeating.hide();
                            $date.text($_('Date'));
                        }
                    }
                });

                var initDatepicker = function () {
                    var datepicker_options = {
                            changeMonth: true,
                            changeYear: true,
                            shortYearCutoff: 2,
                            dateShowWeek: false,
                            showOtherMonths: true,
                            selectOtherMonths: true,
                            stepMonths: 1,
                            numberOfMonths: 1,
                            gotoCurrent: true,
                            constrainInput: false,
                            dateFormat: "yy-mm-dd",
                        },
                        $datePicker = $dialogWrapper.find('[name="transaction[date]"]');


                    $datePicker.datepicker(datepicker_options);
                    $datePicker
                        .on('change.cash', function (e) {
                            var $this = $(this);
                            $.post('?module=json&action=whichDateIs', {
                                date: $this.val()
                            }, function (r) {
                                if (r.status === 'ok') {
                                    $this.closest('.value').find('.hint').text(r.data);
                                }
                            }, 'json')
                        })
                        .trigger('change.cash');
                    $dialogWrapper.find('[name="repeating[end][ondate]"]').datepicker(datepicker_options);
                };

                initDatepicker();

                setTimeout(function () {
                    $dialogWrapper.find('[name="transaction[amount]"]').trigger('focus');
                }, 13.12);
            },
            onSubmit: function (d) {
                var $errormsg = d.find('.errormsg');

                $errormsg.hide();
                d.find('.dialog-buttons input[type="button"]').after($.cash.$loading);
                $.post('?module=transaction&action=save', d.find('form').serialize(), function (r) {
                    $.cash.$loading.remove();
                    if (r.status === 'ok') {
                        d.trigger('close');
                        // if (!pocketId) {
                        // window.location.hash = '#/project/' + r.data.id;
                        // }
                        $.cash_routing.redispatch();
                        $.cash.reloadSidebar();
                    } else {
                        $errormsg
                            .show()
                            .text(r.errors.join('<br>'));
                    }
                }, 'json');
                return false;
            }
        });
    }

    return initDialog;
})(jQuery)
