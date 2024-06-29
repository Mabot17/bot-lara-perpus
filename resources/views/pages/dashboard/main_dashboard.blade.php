@extends('layout.main_layout')

@section('content')
<div class="col-12">
    <div class="row">
        <div class="col-md-6 col-xl-3 mb-4">
            <div class="card shadow bg-primary text-white border-0">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-3 text-center">
                            <span class="circle circle-sm bg-primary-light">
                                <i class="fe fe-16 fe-shopping-bag text-white mb-0"></i>
                            </span>
                        </div>
                        <div class="col pr-0">
                            <p class="small text-muted mb-0">Monthly Sales</p>
                            <span class="h3 mb-0 text-white" id="monthlySales"></span>
                            <span class="small text-muted">+5.5%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3 mb-4">
            <div class="card shadow border-0">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-3 text-center">
                            <span class="circle circle-sm bg-primary">
                                <i class="fe fe-16 fe-shopping-cart text-white mb-0"></i>
                            </span>
                        </div>
                        <div class="col pr-0">
                            <p class="small text-muted mb-0">Orders</p>
                            <span class="h3 mb-0" id="totalOrders"></span>
                            <span class="small text-success">+16.5%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3 mb-4">
            <div class="card shadow border-0">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-3 text-center">
                            <span class="circle circle-sm bg-primary">
                                <i class="fe fe-16 fe-filter text-white mb-0"></i>
                            </span>
                        </div>
                        <div class="col">
                            <p class="small text-muted mb-0">Conversion</p>
                            <div class="row align-items-center no-gutters">
                                <div class="col-auto">
                                    <span class="h3 mr-2 mb-0" id="conversionRate"></span>
                                </div>
                                <div class="col-md-12 col-lg">
                                    <div class="progress progress-sm mt-2" style="height:3px">
                                        <div class="progress-bar bg-success" role="progressbar" id="conversionProgressBar" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3 mb-4">
            <div class="card shadow border-0">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-3 text-center">
                            <span class="circle circle-sm bg-primary">
                                <i class="fe fe-16 fe-activity text-white mb-0"></i>
                            </span>
                        </div>
                        <div class="col">
                            <p class="small text-muted mb-0">AVG Orders</p>
                            <span class="h3 mb-0" id="avgOrderValue"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end section -->
    <div class="row align-items-center my-2">
        <div class="col-auto ml-auto">
            <form class="form-inline">
                <div class="form-group">
                    <label for="reportrange" class="sr-only">Date Ranges</label>
                    <div id="reportrange" class="px-2 py-2 text-muted">
                        <i class="fe fe-calendar fe-16 mx-2"></i>
                        <span class="small"></span>
                    </div>
                </div>
                <div class="form-group">
                    <button type="button" class="btn btn-sm"><span class="fe fe-refresh-ccw fe-12 text-muted"></span></button>
                    <button type="button" class="btn btn-sm"><span class="fe fe-filter fe-12 text-muted"></span></button>
                </div>
            </form>
        </div>
    </div>
    <!-- charts-->
    <div class="row my-4">
        <div class="col-md-12">
            <div class="chart-box">
                <div id="columnChart"></div>
            </div>
        </div>
        <!-- .col -->
    </div>
    <!-- end section -->
    <div class="col-md-12">
        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="card-title">
                    <strong>Sales by Category</strong>
                </div>
                <div id="pieChart"></div>
            </div>
        </div>
    </div>
</div>
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const token = localStorage.getItem('token');
    const currentTheme = localStorage.getItem('mode') || 'light'; // Default ke 'light' jika tidak ada mode yang disimpan

    const darkTheme = {
        chart: {
            backgroundColor: '#2a2a2b',
            style: {
                fontFamily: 'Arial, sans-serif'
            }
        },
        title: {
            style: {
                color: '#E0E0E3'
            }
        },
        xAxis: {
            labels: {
                style: {
                    color: '#E0E0E3'
                }
            },
            lineColor: '#707073',
            tickColor: '#707073'
        },
        yAxis: {
            gridLineColor: '#707073',
            labels: {
                style: {
                    color: '#E0E0E3'
                }
            },
            lineColor: '#707073',
            tickColor: '#707073',
            title: {
                style: {
                    color: '#A0A0A3'
                }
            }
        },
        legend: {
            itemStyle: {
                color: '#E0E0E3'
            },
            itemHoverStyle: {
                color: '#FFF'
            },
            itemHiddenStyle: {
                color: '#606063'
            }
        },
        plotOptions: {
            series: {
                dataLabels: {
                    color: '#B0B0B3'
                },
                marker: {
                    lineColor: '#333'
                }
            },
            boxplot: {
                fillColor: '#505053'
            },
            candlestick: {
                lineColor: 'white'
            },
            errorbar: {
                color: 'white'
            }
        },
        tooltip: {
            backgroundColor: 'rgba(0, 0, 0, 0.85)',
            style: {
                color: '#F0F0F0'
            }
        }
    };

    const lightTheme = {
        chart: {
            backgroundColor: '#FFFFFF',
            style: {
                fontFamily: 'Arial, sans-serif'
            }
        },
        title: {
            style: {
                color: '#333333'
            }
        },
        xAxis: {
            labels: {
                style: {
                    color: '#333333'
                }
            },
            lineColor: '#E0E0E3',
            tickColor: '#E0E0E3'
        },
        yAxis: {
            gridLineColor: '#E0E0E3',
            labels: {
                style: {
                    color: '#333333'
                }
            },
            lineColor: '#E0E0E3',
            tickColor: '#E0E0E3',
            title: {
                style: {
                    color: '#666666'
                }
            }
        },
        legend: {
            itemStyle: {
                color: '#333333'
            },
            itemHoverStyle: {
                color: '#000000'
            },
            itemHiddenStyle: {
                color: '#CCCCCC'
            }
        },
        plotOptions: {
            series: {
                dataLabels: {
                    color: '#333333'
                },
                marker: {
                    lineColor: '#666666'
                }
            },
            boxplot: {
                fillColor: '#F0F0F0'
            },
            candlestick: {
                lineColor: '#000000'
            },
            errorbar: {
                color: '#000000'
            }
        },
        tooltip: {
            backgroundColor: 'rgba(255, 255, 255, 0.85)',
            style: {
                color: '#000000'
            }
        }
    };

    // Set Highcharts theme based on currentTheme
    Highcharts.setOptions(currentTheme === 'dark' ? darkTheme : lightTheme);

    $.ajax({
        url: `/api/dashboard/grafik-bar-netsales`,
        method: 'GET',
        headers: {
            'Authorization': 'Bearer ' + token
        },
        success: function(response) {
            console.log(response);
            // Update the stats
            $('#monthlySales').text('$' + response.total_sales.toFixed(2));
            $('#totalOrders').text(response.total_orders);
            $('#conversionRate').text(response.conversion_rate.toFixed(2) + '%');
            $('#conversionProgressBar').css('width', response.conversion_rate.toFixed(2) + '%');
            $('#avgOrderValue').text('$' + response.avg_order_value.toFixed(2));

            var categories = response.chart_data.map(item => item.penjualan_tanggal);
            var totalOrders = response.chart_data.map(item => item.total_order);
            var totalItems = response.chart_data.map(item => item.total_item);

            Highcharts.chart('columnChart', {
                chart: {
                    type: 'column'
                },
                title: {
                    text: 'Penjualan'
                },
                xAxis: {
                    categories: categories,
                    crosshair: true
                },
                yAxis: {
                    min: 0,
                    title: {
                        text: 'Jumlah'
                    }
                },
                plotOptions: {
                    column: {
                        pointPadding: 0.2,
                        borderWidth: 0
                    }
                },
                series: [{
                    name: 'Total Order',
                    data: totalOrders
                }, {
                    name: 'Total Item',
                    data: totalItems
                }]
            });

            var pieData = response.dataSalesKategori.map(item => {
                return { name: item.kategori_nama, y: item.total_item };
            });

            Highcharts.chart('pieChart', {
                chart: {
                    type: 'pie'
                },
                title: {
                    text: 'Sales by Category'
                },
                series: [{
                    name: 'Total Item',
                    colorByPoint: true,
                    data: pieData
                }]
            });
        },
        error: function(xhr, status, error) {
            console.error('Error fetching data:', error);
            alert('Terjadi kesalahan saat mengambil data.');
        }
    });
});
</script>
@endsection
