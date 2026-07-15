/**
 *
 * @names automation
 * @method automationInit
 */
$.extend($.automation = $.automation || {}, {

    Init: function (options) {
        let that = this;
        let new_rule_id = -1;
        let $form = $('#c-automation-rules-form');
        const $table_tbody = $('#c-automation-rules tbody').first();

        // Add new rule group when user clicks the Add link
        $('#c-automation-add-rule').on('click', function (event) {
            event.preventDefault();

            const tmpl = options.new_automation_rule.replace(/%%RULE_ID%%/g, new_rule_id);
            $table_tbody.prepend(tmpl);
            new_rule_id--;
        });

        (function () {
            let new_condition_id = 0;
            $table_tbody.on('change', '.add-condition-selector', function () {
                let rule_id = $(this).closest('tr').data('rule-id');
                const tmpl = options.new_condition
                    .replace(/%%RULE_ID%%/g, rule_id)
                    .replace(/%%CONDITION_ID%%/g, new_condition_id)
                    .replace(/%%RULE_NAME%%/g, $(this).find('option:selected').text());
                const $new_condition = $(tmpl);
                $new_condition.find('.'+ $(this).val()).prop('disabled', false).removeClass('hidden');
                $(this).closest('.wa-select').before($new_condition);

                $(this).val('');
                new_condition_id++;
            });

            $table_tbody.on('change', '.c-action-selector', function () {
                let plugin_id = $(this).find('option:selected').data('plugin-id');
                let action_id = $(this).val();
                if (plugin_id) {
                    $(this).closest('td').find('.c-action-plugin-id').prop('disabled', false).val(plugin_id);
                } else {
                    $(this).closest('td').find('.c-action-plugin-id').prop('disabled', true).val('');
                }
                $(this).closest('td').find('.c-automaton-action').addClass('hidden');
                $(this).closest('td').find('[data-action-id="'+ action_id +'"]').removeClass('hidden');

                $(this).closest('td').find('.c-automaton-action input, .c-automaton-action select, .c-automaton-action textarea').prop('disabled', true);
                $(this).closest('td').find('[data-action-id="'+ action_id +'"] input, [data-action-id="'+ action_id +'"] select, [data-action-id="'+ action_id +'"] textarea').prop('disabled', false);
            });
        })();

        $table_tbody.on('click', '.c-log-rule', function (event) {
            event.preventDefault();

            let that = $(this).closest('tr');
            let rule_id = that.data('rule-id');
            $.post('?module=automationLog', {rule_id: rule_id}, function (data) {
                let $new_dialog_log = $(options.dialog_log);
                if (data.status !== 'ok') {
                    console.warn('get automation log fail', data);
                } else if (data.data) {
                    if (data.data.length) {
                        $new_dialog_log.find('.js-empty-log').remove();
                    }
                    data.data.forEach((_automation, _index, _array) => {
                        $new_dialog_log.find('table tbody').append(
                            '<tr>' +
                            '<td>'+ _automation.datetime +'</td>' +
                            '<td>'+ _automation.type +'</td>' +
                            '<td>'+ _automation.automation_event +'</td>' +
                            '<td>'+ _automation.automation_action +'</td>' +
                            '<td>'+ _automation.description +'</td>' +
                            '<td>'+ (_automation.plugin_id ? _automation.plugin_id : '') +'</td>' +
                            '</tr>'
                        );
                    });
                }

                $.waDialog({
                    html: $new_dialog_log,
                    onOpen: function($dialog, dialog_instance) {
                        $dialog.find('.dialog-body').css('top', '3%');
                        $dialog.find('.dialog-body').css('left', '20%');
                        $dialog.find('.dialog-body').css('width', '70%');
                        $dialog.find('.dialog-content').css('height', '75vh');
                    }
                });
            });
        });

        // Link to delete a row
        $table_tbody.on('click', '.c-delete-rule', function (event) {
            event.preventDefault();

            let that = $(this).closest('tr');
            let rule_id = that.data('rule-id');

            $.waDialog.confirm({
                title: options.confirm,
                text: '<i class="fas fa-exclamation-triangle fa-xs state-error"></i> '+ options.confirmation_deletion,
                success_button_class: 'danger',
                success_button_title: options.confirm,
                cancel_button_title: options.cancel,
                onSuccess: function () {
                    $.post('?module=automationDelete', {rule_id: rule_id}, function () {
                        that.remove();
                    });
                }
            });
        });

        $table_tbody.on('click', '.c-delete-condition', function (event) {
            event.preventDefault();

            $(this).closest('div.js-condition-block').remove();
            $('.js-form-submit').removeClass('green').addClass('yellow');
        });

        $form.on('change', function (event) {
            event.preventDefault();
            $('.js-form-submit').removeClass('green').addClass('yellow');
        });

        $form.on('submit', function (event) {
            event.preventDefault();

            const form_data = $form.serializeArray();

            $form.find(':submit').append('<span class="s-msg-after-button"><i class="fas fa-spinner fa-spin"></i></span>');
            $.post($form.attr('action'), form_data, function () {
                window.location.reload();
            });
        });

        // Make existing rows sortable
        $table_tbody.sortable({
            handle:'.fa-grip-vertical.automation-rows-handle',
            update: function (event) {
                let sort = 0;
                $.each($('.js-sort-rule'), function (key, value) {
                    $(value).val(sort);
                    sort++;
                });
                $('form').trigger('change');
            }
        });
    }
});
