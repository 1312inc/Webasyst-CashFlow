
function toLocaleDate (string) {
    return $.datepicker.formatDate($.datepicker._defaults.dateFormat, new Date($.datepicker.parseDate("yy-mm-dd", string)));
}

function isValidDate (string) {
    return !Number.isNaN(Date.parse(string));
}

export default function (selector, rangesDays, callback) {

    const $dropdownToggle = $(`${selector} .dropdown-toggle`);

    $(`${selector} #range-dropdown`).waDropdown({
        items: ".menu > li > a",
        hide: false,
        ready: function () {
            let from = $(`${selector} [name="from"]`).val();
            let to = $(`${selector} [name="to"]`).val();
            const nextMode = from === new Date().toISOString().slice(0, 10);

            if (nextMode) {
                [from, to] = [to, from];
            }

            if (
                isValidDate(from) &&
                isValidDate(to)
            ) {
                for (const days of rangesDays) {
                    const targetButton = $(`${selector} [data-range="${days}"]`)[0];
                    if (
                        to === new Date().toISOString().slice(0, 10) &&
                        from === new Date(new Date().setDate(new Date().getDate() - days)).toISOString().slice(0, 10) &&
                        targetButton
                    ) {
                        $dropdownToggle.text(targetButton.innerText);
                        return;
                    }
                }
                $(`${selector} .dateFrom`).val(toLocaleDate(from));
                $(`${selector} .dateTo`).val(toLocaleDate(to));
                $(`${selector} .range-manual`).show();
            }
        },
        change: function (event, target, dropdown) {
            const days = Number($(target).data("range"));
            if (days) {
                const oldValue = $(`${selector} [name="from"]`).val();
                $(`${selector} .range-manual`).hide();
                const from = new Date(new Date().setDate(new Date().getDate() - days)).toISOString().slice(0, 10);
                const to = new Date().toISOString().slice(0, 10);
                const nextMode = Date.parse(from) - Date.parse(to) > 0;
                $(`${selector} [name="from"]`).val(nextMode ? to : from);
                $(`${selector} [name="to"]`).val(nextMode ? from : to);

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