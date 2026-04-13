import { AMCHARTS_LICENSE } from './constants.js';
import { createCurrencyToggler } from './currencyToggle.js';
import { chartCols, chartDonut } from "./amcharts.js";

export default function (data, language, year, categoriesList) {

    // State
    const currencySigns = Object.entries({
        ...data.all_income, ...data.all_expense
    }).reduce((acc, c) => {
        acc[c[0]] = c[1].helpers.currencySign;
        return acc;
    }, {});

    const currencies = Object.keys({
        ...data.all_income, ...data.all_expense
    });

    let activeCurrency = currencies[0];
    const categoriesById = categoriesList.reduce((acc, category) => {
        acc[category.id] = category;
        return acc;
    }, {});

    // Run Initial render
    am4core.ready(() => {
        am4core.addLicense(AMCHARTS_LICENSE);
        am4core.useTheme(am4themes_animated);
        if (document.documentElement.dataset.theme === 'dark') {
            am4core.useTheme(am4themes_dark);
        }
        if (currencies.length > 1) {
            createCurrencyToggler('.currencies-container', currencies, (currency) => {
                activeCurrency = currency;
                renderCharts();
            });
        }
        renderCharts();
    });

    function renderCharts () {

        am4core.disposeAllCharts();

        function donutAdapter (data) {
            const total = data[activeCurrency].total;
            const columns = data[activeCurrency].data.columns;
            const rootItems = [];
            const childrenByParentId = {};

            const flatItems = Object.entries(total).map(([id, value]) => {
                if (!columns[id]) {
                    return null;
                }

                const category = categoriesById[id];
                return {
                    id,
                    name: columns[id][0],
                    color: data[activeCurrency].data.colors[columns[id][0]][0],
                    value,
                    isProfit: +category?.is_profit === 1,
                    isActive: false
                };
            });

            flatItems.forEach((item) => {
                if (!item) {
                    return;
                }

                const category = categoriesById[item.id];
                const parentId = category?.category_parent_id ? String(category.category_parent_id) : null;
                const hasParent = parentId && categoriesById[parentId];

                if (hasParent) {
                    if (!childrenByParentId[parentId]) {
                        childrenByParentId[parentId] = [];
                    }
                    childrenByParentId[parentId].push(item);
                } else {
                    rootItems.push(item);
                }
            });

            return {
                rootData: rootItems.map((item) => ({
                    ...item,
                    hasChildren: !!childrenByParentId[item.id]?.length
                })),
                childrenByParentId
            };
        }

        function buildDonutData (rootData, childrenByParentId, expandedParentId) {
            if (!expandedParentId || !childrenByParentId[expandedParentId]?.length) {
                return rootData;
            }

            const nextData = [];
            rootData.forEach((item) => {
                if (item.id === expandedParentId) {
                    childrenByParentId[expandedParentId].forEach((child) => {
                        nextData.push({
                            ...child,
                            parentId: expandedParentId,
                            isActive: true
                        });
                    });
                } else {
                    nextData.push(item);
                }
            });

            return nextData;
        }

        // Renders Donuts

        document.querySelectorAll('.c-charts').forEach(e => e.style.display = 'none');

        ;['income', 'expense'].forEach(type => {
            if (data[`all_${type}`][activeCurrency]) {
                const el = document.querySelector(`#chartdiv_${type}`);
                el.parentElement.style.display = 'block';
                const { rootData, childrenByParentId } = donutAdapter(data[`all_${type}`]);
                let expandedParentId = null;
                const donut = chartDonut({
                    el,
                    data: rootData,
                    currency: currencySigns[activeCurrency],
                    onSliceClick: (slice) => {
                        if (slice.parentId) {
                            expandedParentId = null;
                            donut.setData(rootData);
                            return;
                        }

                        if (slice.id && childrenByParentId[slice.id]?.length) {
                            expandedParentId = expandedParentId === slice.id ? null : slice.id;
                            donut.setData(buildDonutData(rootData, childrenByParentId, expandedParentId));
                        }
                    },
                    onChartClick: () => {
                        expandedParentId = null;
                        donut.setData(rootData);
                    }
                }, language);
            }
        });

        // Render Columns Charts

        const container = document.querySelector('#cols');
        container.innerHTML = '';

        ;['income', 'expense'].forEach(type => {
            if (data[`all_${type}`][activeCurrency]) {

                const currencyData = data[`all_${type}`][activeCurrency].data;
                const columns = currencyData.columns;

                for (const id in columns) {

                    const el = document.createElement('div');
                    container.appendChild(el);

                    const cols = columns[id].slice(1, -2).map((e, i) => {
                        return {
                            date: new Date(year, i, 1),
                            value: e
                        };
                    });

                    chartCols({
                        el: el,
                        name: columns[id][0],
                        color: currencyData.colors[columns[id][0]][0],
                        data: cols,
                        currency: currencySigns[activeCurrency]
                    }, language);

                }

            }
        });

    }

}