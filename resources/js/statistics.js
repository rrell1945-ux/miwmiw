import ApexCharts from 'apexcharts';

window.BloomCharts = {
    charts: {},

    render(id, options) {
        if (!document.getElementById(id)) return;
        if (this.charts[id]) this.charts[id].destroy();

        const isDark = document.documentElement.classList.contains('dark');
        const base = {
            chart: {
                fontFamily: 'Poppins, sans-serif',
                foreColor: isDark ? '#d1d5db' : '#374151',
                toolbar: { show: false },
                animations: { enabled: true, speed: 700 },
            },
            dataLabels: { enabled: false },
            grid: {
                borderColor: isDark ? '#374151' : '#FCE7F3',
                strokeDashArray: 4,
            },
            theme: {
                palette: 'palette4',
            },
        };

        this.charts[id] = new ApexCharts(
            document.getElementById(id),
            this.mergeDeep(base, options)
        );
        this.charts[id].render();
    },

    mergeDeep(target, source) {
        if (!source) return target;
        const output = Array.isArray(target) ? [...target] : { ...target };
        for (const key of Object.keys(source)) {
            if (isObject(source[key]) && key in output) {
                output[key] = this.mergeDeep(output[key], source[key]);
            } else {
                output[key] = source[key];
            }
        }
        return output;
    },
};

function isObject(item) {
    return item && typeof item === 'object' && !Array.isArray(item);
}
