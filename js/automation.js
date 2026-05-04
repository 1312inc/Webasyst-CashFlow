/**
 *
 * @names automation
 * @method automationInit
 */
$.extend($.automation = $.automation || {}, {

    Init: function (options) {
        let that = this;
        let new_rule_id = -1;
        let new_condition_id = 0;

        let $form = $('#c-automation-rules-form');
        const $table_tbody = $('#c-automation-rules tbody').first();

        // Add new rule group when user clicks the Add link
        $('#c-automation-add-rule').on('click', function (event) {
            event.preventDefault();

            const tmpl = options.new_automation_rule.replace(/%%RULE_ID%%/g, new_rule_id).replace(/%%CONDITION_KEY%%/g, new_condition_id);
            $table_tbody.prepend(tmpl);
            new_rule_id--;
            new_condition_id++;
        });

        (function () {
            $table_tbody.on('change', '.c-condition-selector', function () {
                let val_cond = $(this).val();
                let $tr = $(this).closest('tr');

                $tr.find('input').removeClass('hidden');
                $tr.find('select[data-condition-id]').closest('div').addClass('hidden');
                $tr.find('select[data-condition-id]').prop('disabled', true);
                $tr.find('select[data-condition-id="'+ val_cond +'"]').closest('div').removeClass('hidden');
                $tr.find('select[data-condition-id="'+ val_cond +'"]').prop('disabled', false);
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
