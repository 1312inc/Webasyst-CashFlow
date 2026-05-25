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

            const tmpl = options.new_automation_rule
                .replace(/%%RULE_ID%%/g, new_rule_id);
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
        })();


        // Link to delete a row
        $table_tbody.on('click', '.c-delete-rule', function (event) {
            event.preventDefault();

            let that = $(this).closest('tr');
            let rule_id = that.data('rule-id');
            $.post('?module=automationDelete', {rule_id: rule_id}, function () {
                that.remove();
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
