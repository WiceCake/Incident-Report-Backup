/**
 * Dashboard Analytics
 */

'use strict';

async function getDevices() {
    try {
        const response = await axios.get('/api/v1/devices')
        return response
    } catch (error) {
        console.error(error)
    }
}

async function getWeeklyThreatData() {
    try {
        const response = await axios.get('/api/v1/detection/weekly')
        return response
    } catch (error) {
        console.error(error)
    }
}

function getWeekOfMonth(date) {
    const dayOfMonth = date.getDate(); // Get the day of the month
    const firstDayOfMonth = new Date(date.getFullYear(), date.getMonth(), 1); // First day of the month
    const dayOfWeek = firstDayOfMonth.getDay(); // Get the weekday (0 for Sunday, 1 for Monday, etc.)

    // Calculate the adjusted day index (account for the week start)
    const adjustedDate = dayOfMonth + dayOfWeek;

    // Return the week of the month by dividing adjusted date by 7 and rounding up
    return Math.ceil(adjustedDate / 7);
}

function filterDayData(data, week, day) {
    return data.filter(function (data) {
        return data.day == day && data.week_of_the_month == week
    })
}

const getMonth = () => {
  const months = [
    "January", "February", "March", "April", "May", "June",
    "July", "August", "September", "October", "November", "December"
  ];

  const currentMonth = new Date().getMonth(); // getMonth() returns 0-based month (0 = January, 11 = December)
  return months[currentMonth];
};

(async function () {
    let cardColor, headingColor, legendColor, labelColor, shadeColor, borderColor;

    cardColor = config.colors.cardColor;
    headingColor = config.colors.headingColor;
    legendColor = config.colors.bodyColor;
    labelColor = config.colors.textMuted;
    borderColor = config.colors.borderColor;

    let devices = await getDevices()

    devices = devices.data
    let mobileCount = 0, desktopCount = 0

    if(devices.length){
        mobileCount = devices.filter(data => data.device === 'Mobile').length
        desktopCount = devices.filter(data => data.device === 'Desktop').length
    }


    // Order Statistics Chart
    // --------------------------------------------------------------------
    const chartOrderStatistics = document.querySelector('#orderStatisticsChart'),
        orderChartConfig = {
            chart: {
                height: 145,
                width: 110,
                type: 'donut'
            },
            labels: ['Desktop', 'Mobile'],
            series: [desktopCount, mobileCount],
            colors: [config.colors.success, config.colors.primary],
            stroke: {
                width: 5,
                colors: [cardColor]
            },
            dataLabels: {
                enabled: false,
                formatter: function (val, opt) {
                    return parseInt(val) + '%';
                }
            },
            legend: {
                show: false
            },
            grid: {
                padding: {
                    top: 0,
                    bottom: 0,
                    right: 15
                }
            },
            states: {
                hover: {
                    filter: { type: 'none' }
                },
                active: {
                    filter: { type: 'none' }
                }
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '75%',
                        labels: {
                            show: true,
                            value: {
                                fontSize: '18px',
                                fontFamily: 'Public Sans',
                                fontWeight: 500,
                                color: headingColor,
                                offsetY: -17,
                                formatter: function (val) {
                                    return parseInt(val);
                                }
                            },
                            name: {
                                offsetY: 17,
                                fontFamily: 'Public Sans'
                            },
                            total: {
                                show: true,
                                fontSize: '13px',
                                color: legendColor,
                                label: 'Devices',
                                formatter: function (w) {
                                    return mobileCount + desktopCount;
                                }
                            }
                        }
                    }
                }
            }
        };
    if (typeof chartOrderStatistics !== undefined && chartOrderStatistics !== null) {
        const statisticsChart = new ApexCharts(chartOrderStatistics, orderChartConfig);
        statisticsChart.render();
    }

    const today = new Date()
    const weekOfMonth = getWeekOfMonth(today)

    let weeklyThreatData = await getWeeklyThreatData()
    weeklyThreatData = weeklyThreatData.data
    let monData, tueData, wedData, thuData, friData, satData, sunData
    monData = filterDayData(weeklyThreatData, weekOfMonth, 'Mon')
    tueData = filterDayData(weeklyThreatData, weekOfMonth, 'Tue')
    wedData = filterDayData(weeklyThreatData, weekOfMonth, 'Wed')
    thuData = filterDayData(weeklyThreatData, weekOfMonth, 'Thu')
    friData = filterDayData(weeklyThreatData, weekOfMonth, 'Fri')
    satData = filterDayData(weeklyThreatData, weekOfMonth, 'Sat')
    sunData = filterDayData(weeklyThreatData, weekOfMonth, 'Sat')

    // Income Chart - Area chart
    // --------------------------------------------------------------------
    const incomeChartEl = document.querySelector('#incomeChart'),
        incomeChartConfig = {
            series: [
                {
                    data: [
                        monData.length ? monData[0].content.length : 0,
                        tueData.length ? tueData[0].content.length : 0,
                        wedData.length ? wedData[0].content.length : 0,
                        thuData.length ? thuData[0].content.length : 0,
                        friData.length ? friData[0].content.length : 0,
                        satData.length ? satData[0].content.length : 0,
                        sunData.length ? sunData[0].content.length : 0,
                    ]
                }
            ],
            chart: {
                height: 232,
                parentHeightOffset: 0,
                parentWidthOffset: 0,
                toolbar: {
                    show: false
                },
                type: 'area'
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                width: 3,
                curve: 'smooth'
            },
            legend: {
                show: false
            },
            markers: {
                size: 6,
                colors: 'transparent',
                strokeColors: 'transparent',
                strokeWidth: 4,
                discrete: [
                    {
                        fillColor: config.colors.white,
                        seriesIndex: 0,
                        dataPointIndex: 6,
                        strokeColor: config.colors.primary,
                        strokeWidth: 2,
                        size: 6,
                        radius: 8
                    }
                ],
                hover: {
                    size: 7
                }
            },
            colors: [config.colors.danger],
            fill: {
                type: 'gradient',
                gradient: {
                    shade: shadeColor,
                    shadeIntensity: 0.6,
                    opacityFrom: 0.5,
                    opacityTo: 0.25,
                    stops: [0, 95, 100]
                }
            },
            grid: {
                borderColor: borderColor,
                strokeDashArray: 8,
                padding: {
                    top: -20,
                    bottom: -8,
                    left: 0,
                    right: 8
                }
            },
            xaxis: {
                categories: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                axisBorder: {
                    show: false
                },
                axisTicks: {
                    show: false
                },
                labels: {
                    show: true,
                    style: {
                        fontSize: '13px',
                        colors: labelColor
                    }
                }
            },
            yaxis: {
                labels: {
                    show: false
                },
                min: 10,
                max: 50,
                tickAmount: 4
            }
        };
    if (typeof incomeChartEl !== undefined && incomeChartEl !== null) {
        const incomeChart = new ApexCharts(incomeChartEl, incomeChartConfig);
        incomeChart.render();
    }

})();
