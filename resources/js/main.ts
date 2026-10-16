import Chart from 'chart.js/auto';

declare global {
    interface Window {
        Chart: typeof Chart;
    }
}

window.Chart = Chart;
