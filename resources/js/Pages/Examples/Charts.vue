<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';

// This is ApexChart Demo Data
const generateData = (count, yrange) => {
    var i = 0;
    var series = [];
    while (i < count) {
        var x = (i + 1).toString();
        var y =
            Math.floor(Math.random() * (yrange.max - yrange.min + 1)) + yrange.min;

        series.push({
            x: x,
            y: y
        });
        i++;
    }
    return series;
}

const sparklineData = [47, 45, 54, 38, 56, 24, 65, 31, 37, 39, 62, 51, 35, 41, 35, 27, 93, 53, 61, 27, 54, 43, 19, 46];


const randomizeArray = () => {

    var array = sparklineData.slice();
    var currentIndex = array.length,
        temporaryValue,
        randomIndex;

    while (0 !== currentIndex) {
        randomIndex = Math.floor(Math.random() * currentIndex);
        currentIndex -= 1;

        temporaryValue = array[currentIndex];
        array[currentIndex] = array[randomIndex];
        array[randomIndex] = temporaryValue;
    }

    return array;
}

const optionsOne = {
    chart: {
        id: 'vuechart-example'
    },
    xaxis: {
        categories: [1991, 1992, 1993, 1994, 1995, 1996, 1997, 1998]
    }
};
const seriesOne = [{
    name: 'series-1',
    data: [30, 40, 45, 50, 49, 60, 70, 91]
}]

const seriesHeatMap = [{
    name: 'Metric1',
    data: generateData(18, {
        min: 0,
        max: 90
    })
},
{
    name: 'Metric2',
    data: generateData(18, {
        min: 0,
        max: 90
    })
},
{
    name: 'Metric3',
    data: generateData(18, {
        min: 0,
        max: 90
    })
},
{
    name: 'Metric4',
    data: generateData(18, {
        min: 0,
        max: 90
    })
},
{
    name: 'Metric5',
    data: generateData(18, {
        min: 0,
        max: 90
    })
},
{
    name: 'Metric6',
    data: generateData(18, {
        min: 0,
        max: 90
    })
},
{
    name: 'Metric7',
    data: generateData(18, {
        min: 0,
        max: 90
    })
},
{
    name: 'Metric8',
    data: generateData(18, {
        min: 0,
        max: 90
    })
},
{
    name: 'Metric9',
    data: generateData(18, {
        min: 0,
        max: 90
    })
}
];

const chartOptionsHeatMap = {
    chart: {
        height: 350,
        type: 'heatmap',
    },
    dataLabels: {
        enabled: false
    },
    colors: ["#008FFB"],
    title: {
        text: 'HeatMap Chart (Single color)'
    },
};

// sparkline


const seriesSpark = [{
    data: randomizeArray()
}];
const chartOptionsSpark = {
    chart: {
        type: 'area',
        height: 160,
        sparkline: {
            enabled: true
        },
    },
    stroke: {
        curve: 'straight'
    },
    fill: {
        opacity: 0.3,
    },
    yaxis: {
        min: 0
    },
    colors: ['#DCE6EC'],
    title: {
        text: '$424,652',
        offsetX: 0,
        style: {
            fontSize: '24px',
        }
    },
    subtitle: {
        text: 'Sales',
        offsetX: 0,
        style: {
            fontSize: '14px',
        }
    }
};

const seriesSpark2 = [{
    data: randomizeArray()
}];

const chartOptionsSpark2 = {
    chart: {
        type: 'area',
        height: 160,
        sparkline: {
            enabled: true
        },
    },
    stroke: {
        curve: 'straight'
    },
    fill: {
        opacity: 0.3,
    },
    yaxis: {
        min: 0
    },
    colors: ['#DCE6EC'],
    title: {
        text: '$235,312',
        offsetX: 0,
        style: {
            fontSize: '24px',
        }
    },
    subtitle: {
        text: 'Expenses',
        offsetX: 0,
        style: {
            fontSize: '14px',
        }
    }
};

const seriesSpark3 = [{
    data: randomizeArray()
}];
const chartOptionsSpark3 = {
    chart: {
        type: 'area',
        height: 160,
        sparkline: {
            enabled: true
        },
    },
    stroke: {
        curve: 'straight'
    },
    fill: {
        opacity: 0.3
    },
    xaxis: {
        crosshairs: {
            width: 1
        },
    },
    yaxis: {
        min: 0
    },
    title: {
        text: '$135,965',
        offsetX: 0,
        style: {
            fontSize: '24px',
        }
    },
    subtitle: {
        text: 'Profits',
        offsetX: 0,
        style: {
            fontSize: '14px',
        }
    }
};

const series1 = [{
    data: [25, 66, 41, 89, 63, 25, 44, 12, 36, 9, 54]
}];
const chartOptions1 = {
    chart: {
        type: 'line',
        width: 100,
        height: 35,
        sparkline: {
            enabled: true
        }
    },
    tooltip: {
        fixed: {
            enabled: false
        },
        x: {
            show: false
        },
        y: {
            title: {
                formatter: function (seriesName) {
                    return ''
                }
            }
        },
        marker: {
            show: false
        }
    }
};

const series2 = [{
    data: [12, 14, 2, 47, 42, 15, 47, 75, 65, 19, 14]
}];

const chartOptions2 = {
    chart: {
        type: 'line',
        width: 100,
        height: 35,
        sparkline: {
            enabled: true
        }
    },
    tooltip: {
        fixed: {
            enabled: false
        },
        x: {
            show: false
        },
        y: {
            title: {
                formatter: function (seriesName) {
                    return ''
                }
            }
        },
        marker: {
            show: false
        }
    }
};

const series3 = [43, 32, 12, 9];
const chartOptons3 = {
    chart: {
        type: 'pie',
        width: 40,
        height: 40,
        sparkline: {
            enabled: true
        }
    },
    stroke: {
        width: 1
    },
    tooltip: {
        fixed: {
            enabled: false
        },
    }
};

const series4 = [43, 32, 12, 9];
const chartOptons4 = {
    chart: {
        type: 'donut',
        width: 40,
        height: 40,
        sparkline: {
            enabled: true
        }
    },
    stroke: {
        width: 1
    },
    tooltip: {
        fixed: {
            enabled: false
        },
    }
};

const series5 = [{
    data: [25, 66, 41, 89, 63, 25, 44, 12, 36, 9, 54]
}];
const chartOptions5 = {
    chart: {
        type: 'bar',
        width: 100,
        height: 35,
        sparkline: {
            enabled: true
        }
    },
    plotOptions: {
        bar: {
            columnWidth: '80%'
        }
    },
    labels: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11],
    xaxis: {
        crosshairs: {
            width: 1
        },
    },
    tooltip: {
        fixed: {
            enabled: false
        },
        x: {
            show: false
        },
        y: {
            title: {
                formatter: function (seriesName) {
                    return ''
                }
            }
        },
        marker: {
            show: false
        }
    }
};

const series6 = [{
    data: [12, 14, 2, 47, 42, 15, 47, 75, 65, 19, 14]
}];
const chartOptions6 = {
    chart: {
        type: 'bar',
        width: 100,
        height: 35,
        sparkline: {
            enabled: true
        }
    },
    plotOptions: {
        bar: {
            columnWidth: '80%'
        }
    },
    labels: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11],
    xaxis: {
        crosshairs: {
            width: 1
        },
    },
    tooltip: {
        fixed: {
            enabled: false
        },
        x: {
            show: false
        },
        y: {
            title: {
                formatter: function (seriesName) {
                    return ''
                }
            }
        },
        marker: {
            show: false
        }
    }
};

const series7 = [45];
const chartOptons7 = {
    chart: {
        type: 'radialBar',
        width: 50,
        height: 50,
        sparkline: {
            enabled: true
        }
    },
    dataLabels: {
        enabled: false
    },
    plotOptions: {
        radialBar: {
            hollow: {
                margin: 0,
                size: '50%'
            },
            track: {
                margin: 0
            },
            dataLabels: {
                show: false
            }
        }
    }
};

const series8 = [53, 67];
const chartOptons8 = {
    chart: {
        type: 'radialBar',
        width: 40,
        height: 40,
        sparkline: {
            enabled: true
        }
    },
    dataLabels: {
        enabled: false
    },
    plotOptions: {
        radialBar: {
            hollow: {
                margin: 0,
                size: '50%'
            },
            track: {
                margin: 1
            },
            dataLabels: {
                show: false
            }
        }
    }
};
</script>

<template>
    <AppLayout title="Example Charts">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Example Charts
            </h2>
        </template>
        <div class="p-4">


            <div class="grid sm:grid-cols-3 grid-cols-1 gap-2">
                <div class="sm:col-span-3 col-span-1 bg-white shadow-sm px-2 text-sm text-gray-600">
                    Using ApexCharts <a class="underline" href="https://apexcharts.com/docs/vue-charts/"
                        target="_blank">https://apexcharts.com/docs/vue-charts/</a>
                </div>

                <!-- sparkline -->

                <div class="sm:col-span-3 col-span-1 bg-white p-4 rounded-lg shadow-lg text-center">
                    <div class="grid sm:grid-cols-3 grid-cols-1 gap-4">
                        <div class="col-md-4">
                            <div id="chart-spark1">
                                <apexchart type="area" height="160" :options="chartOptionsSpark" :series="seriesSpark">
                                </apexchart>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div id="chart-spark2">
                                <apexchart type="area" height="160" :options="chartOptionsSpark2"
                                    :series="seriesSpark2">
                                </apexchart>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div id="chart-spark3">
                                <apexchart type="area" height="160" :options="chartOptionsSpark3"
                                    :series="seriesSpark3">
                                </apexchart>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 flow-root">
                        <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                            <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                                <table class="min-w-full divide-y divide-gray-300">
                                    <thead>
                                        <th>Total Value</th>
                                        <th>Percentage of Portfolio</th>
                                        <th>Last 10 days</th>
                                        <th>Volume</th>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>$32,554</td>
                                            <td>15%</td>
                                            <td>
                                                <div id="chart-1">
                                                    <apexchart type="line" height="35" width="100"
                                                        :options="chartOptions1" :series="series1"></apexchart>
                                                </div>
                                            </td>
                                            <td>
                                                <div id="chart-5">
                                                    <apexchart type="bar" height="35" width="100"
                                                        :options="chartOptions5" :series="series5"></apexchart>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>$23,533</td>
                                            <td>7%</td>
                                            <td>
                                                <div id="chart-2">
                                                    <apexchart type="line" height="35" width="100"
                                                        :options="chartOptions2" :series="series2"></apexchart>
                                                </div>
                                            </td>
                                            <td>
                                                <div id="chart-6">
                                                    <apexchart type="bar" height="35" width="100"
                                                        :options="chartOptions6" :series="series6"></apexchart>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>$54,276</td>
                                            <td>9%</td>
                                            <td>
                                                <div id="chart-3">
                                                    <apexchart type="pie" height="40" width="40"
                                                        :options="chartOptions3" :series="series3"></apexchart>
                                                </div>
                                            </td>
                                            <td>
                                                <div id="chart-7">
                                                    <apexchart type="radialBar" height="50" width="50"
                                                        :options="chartOptions7" :series="series7"></apexchart>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>$11,533</td>
                                            <td>2%</td>
                                            <td>
                                                <div id="chart-4">
                                                    <apexchart type="donut" height="40" width="40"
                                                        :options="chartOptions4" :series="series4"></apexchart>
                                                </div>
                                            </td>
                                            <td>
                                                <div id="chart-8">
                                                    <apexchart type="radialBar" height="40" width="40"
                                                        :options="chartOptions8" :series="series8"></apexchart>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- end sparkline -->
               <div class="grid sm:grid-cols-2 grid-cols-1 col-span-3 gap-4 ">
                    <div class="col-span-1 bg-white p-4 rounded-lg shadow-lg text-center">
                        <apexchart type="bar" :options="optionsOne" :series="seriesOne"></apexchart>
                    </div>

                    <div class="col-span-1 bg-white p-4 rounded-lg shadow-lg">
                        <apexchart type="heatmap" :options="chartOptionsHeatMap" :series="seriesHeatMap"></apexchart>
                    </div>
               </div>

            </div>
        </div>
    </AppLayout>
</template>
