
let activeCurrency;

function createCurrencyToggler (containerSelector, currencies, callback) {

    // Render Buttons
    for (const currency of currencies) {
        const button = document.createElement('span');
        button.innerText = currency;
        document.querySelector(containerSelector).appendChild(button);
    }

    // Init waToggle
    if (window.jQuery) {
        $(`${containerSelector} span:first-child`).addClass('selected');
        $(containerSelector).show().waToggle({
            change: (event, target, toggle) => {
                activeCurrency = $(target).text();
                callback(activeCurrency);
            }
        });
    }

}

export {
    activeCurrency,
    createCurrencyToggler
};
