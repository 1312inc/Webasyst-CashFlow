
function toLocaleDate (string) {
    return $.datepicker.formatDate($.datepicker._defaults.dateFormat, new Date($.datepicker.parseDate("yy-mm-dd", string)));
}

function isValidDate (string) {
    return !Number.isNaN(Date.parse(string));
}

export default function (selector, rangesDays, callback) {

    const from = $(`${selector} [name="from"]`).val();
    const to = $(`${selector} [name="to"]`).val();

    $(`${selector} #range-dropdown`).waDropdown({
        items: ".menu > li > a",
        ready: function () {
            if (
                isValidDate(from) &&
                isValidDate(to)
            ) {
                for (const days of rangesDays) {
                    if (
                        to === new Date().toISOString().slice(0, 10) &&
                        from === new Date(new Date().setDate(new Date().getDate() - days)).toISOString().slice(0, 10) &&
                        $(`${selector} [data-range="${days}"]`)[0]
                    ) {
                        $(`${selector} [data-range="${days}"]`).trigger('click');
                        return;
                    }
                }
                $(`${selector} .dateFrom`).val(toLocaleDate(from));
                $(`${selector} .dateTo`).val(toLocaleDate(to));
                $(`${selector} [data-range="custom"]`).trigger('click');
            }
        },
        change: function (event, target, dropdown) {
            const days = Number($(target).data("range"));
            if (days) {
                const oldValue = $(`${selector} [name="from"]`).val();
                $(`${selector} .range-manual`).hide();
                const from = new Date(new Date().setDate(new Date().getDate() - days)).toISOString().slice(0, 10);
                const to = new Date().toISOString().slice(0, 10);
                $(`${selector} [name="from"]`).val(from);
                $(`${selector} [name="to"]`).val(to);

                if (oldValue !== from) {
                    if (typeof callback === 'function') {
                        callback();
                    }
                }

            } else {
                $(`${selector} .range-manual`).show();
                $(`${selector} .dateFrom`).datepicker({
                    altField: '[name="from"]',
                    altFormat: "yy-mm-dd"
                });
                $(`${selector} .dateTo`).datepicker({
                    altField: '[name="to"]',
                    altFormat: "yy-mm-dd"
                });
            }
        }
    });

}