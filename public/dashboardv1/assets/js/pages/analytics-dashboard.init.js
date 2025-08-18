"use strict";

// Website Traffic Chart
var options = {
    series: [{ name: "Desktops", data: [35, 78, 40, 90, 56, 80, 15] }],
    chart: {
        height: 45,
        type: "area",
        sparkline: { enabled: true },
        animations: { enabled: false },
        dropShadow: {
            enabled: true,
            top: 10,
            left: 0,
            bottom: 10,
            blur: 2,
            color: "#f0f4f7",
            opacity: 0.3
        }
    },
    colors: ["#c26316"],
    fill: {
        type: "gradient",
        gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.5,
            opacityTo: 0.1,
            stops: [0, 90, 100]
        }
    },
    tooltip: { enabled: false },
    dataLabels: { enabled: false },
    grid: { show: false },
    xaxis: {
        labels: { show: false },
        axisBorder: { show: false },
        axisTicks: { show: false }
    },
    yaxis: { show: false },
    stroke: { curve: "smooth", width: 2 }
};
var chartOne = new ApexCharts(document.querySelector("#website-traffic"), options);
chartOne.render();

// Conversion Visitors Chart
options = {
    series: [{ name: "Desktops", data: [25, 55, 20, 60, 35, 60, 15] }],
    chart: { height: 45, type: "area", sparkline: { enabled: true }, animations: { enabled: false }, dropShadow: { enabled: true, top: 10, left: 0, bottom: 10, blur: 2, color: "#f0f4f7", opacity: 0.3 } },
    colors: ["#E7366B"],
    fill: { type: "gradient", gradient: { shadeIntensity: 1, opacityFrom: 0.5, opacityTo: 0.1, stops: [0, 90, 100] } },
    tooltip: { enabled: false },
    dataLabels: { enabled: false },
    grid: { show: false },
    xaxis: { labels: { show: false }, axisBorder: { show: false }, axisTicks: { show: false } },
    yaxis: { show: false },
    stroke: { curve: "smooth", width: 2 }
};
chartOne = new ApexCharts(document.querySelector("#conversion-visitors"), options);
chartOne.render();

// Session Duration Chart
options = {
    series: [{ name: "Desktops", data: [25, 68, 2, 50, 25, 55, 89] }],
    chart: { height: 45, type: "area", sparkline: { enabled: true }, animations: { enabled: false }, dropShadow: { enabled: true, top: 10, left: 0, bottom: 10, blur: 2, color: "#f0f4f7", opacity: 0.3 } },
    colors: ["#287F71"],
    fill: { type: "gradient", gradient: { shadeIntensity: 1, opacityFrom: 0.5, opacityTo: 0.1, stops: [0, 90, 100] } },
    tooltip: { enabled: false },
    dataLabels: { enabled: false },
    grid: { show: false },
    xaxis: { labels: { show: false }, axisBorder: { show: false }, axisTicks: { show: false } },
    yaxis: { show: false },
    stroke: { curve: "smooth", width: 2 }
};
chartOne = new ApexCharts(document.querySelector("#session-duration"), options);
chartOne.render();

// Active Users Chart
options = {
    series: [{ name: "Desktops", data: [36, 78, 36, 58, 35, 65, 55] }],
    chart: { height: 45, type: "area", sparkline: { enabled: true }, animations: { enabled: false }, dropShadow: { enabled: true, top: 10, left: 0, bottom: 10, blur: 2, color: "#f0f4f7", opacity: 0.3 } },
    colors: ["#108dff"],
    fill: { type: "gradient", gradient: { shadeIntensity: 1, opacityFrom: 0.5, opacityTo: 0.1, stops: [0, 90, 100] } },
    tooltip: { enabled: false },
    dataLabels: { enabled: false },
    grid: { show: false },
    xaxis: { labels: { show: false }, axisBorder: { show: false }, axisTicks: { show: false } },
    yaxis: { show: false },
    stroke: { curve: "smooth", width: 2 }
};
chartOne = new ApexCharts(document.querySelector("#active-users"), options);
chartOne.render();



// Total Leads Chart
options = {
    series: [
        { name: "Created", data: [48, 32, 42, 28, 15, 32, 20] },
        { name: "Converted", data: [32, 33, 39, 42, 72, 55, 60] }
    ],
    chart: {
        type: "bar",
        height: 367,
        stacked: true,
        foreColor: "#adb0bb",
        parentHeightOffset: 0,
        toolbar: { show: false }
    },
    plotOptions: {
        bar: {
            horizontal: false,
            columnWidth: "20%",
            endingShape: "rounded",
            startingShape: "rounded"
        }
    },
    dataLabels: { enabled: false },
    xaxis: { categories: ["Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug"] },
    grid: {
        borderColor: "rgba(0,0,0,0.1)",
        strokeDashArray: 3,
        xaxis: { lines: { show: false } },
        yaxis: { lines: { show: true } }
    },
    colors: ["#c26316", "#D49664"],
    legend: { position: "bottom" },
    fill: { opacity: 1 }
};
chart = new ApexCharts(document.querySelector("#totalleads"), options);
chart.render();

