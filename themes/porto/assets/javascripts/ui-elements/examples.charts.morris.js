
(function( $ ) {

	'use strict';


	Morris.Line({
		resize: true,
		element: 'morrisLine',
		data: morrisLineData,
		xkey: 'y',
		ykeys: ['a', 'b', 'c'],
		labels: ['Total cost', 'Spot value', 'Numismatic value'],
		hideHover: true,
		lineColors: ['#0088cc', '#734ba9', '#3b2cc6']
	});


    Morris.Bar({
        resize: true,
        element: 'morrisBarNumismatic',
        data: morrisBarDataNumismatic,
        xkey: 'y',
        ykeys: ['a', 'b'],
        labels: ['Total Spent', 'Numismatic Value'],
        hideHover: true,
        barColors: ['#60BD68', '#DECF3F']
    });
    Morris.Bar({
        resize: true,
        element: 'morrisBar',
        data: morrisDataBar,
        xkey: 'y',
        ykeys: ['a', 'b'],
        labels: ['Total Spent', 'Spot Value'],
        hideHover: true,
        barColors: ['#ffb247', '#2baab1']
    });



    (function() {
        var plot = $.plot('#flotPie', flotPieData, {
            series: {
                pie: {
                    show: true,
                    combine: {
                        color: '#999',
                        threshold: 0.1
                    }
                }
            },
            legend: {
                show: false
            },
            grid: {
                hoverable: true,
                clickable: true
            }
        });
    })();



    (function() {
        var plot = $.plot('#flotBars', [flotBarsData], {
            colors: ['#8CC9E8'],
            series: {
                bars: {
                    show: true,
                    barWidth: 0.7,
                    align: 'center',
                    margin: 10
                }
            },
            xaxis: {
                mode: 'categories',
                tickLength: 0
            },
            grid: {
                hoverable: true,
                clickable: true,
                borderColor: 'rgba(0,0,0,0.1)',
                borderWidth: 1,
                labelMargin: 15,
                backgroundColor: 'transparent'
            },
            tooltip: true,
            tooltipOpts: {
                content: '%y',
                shifts: {
                    x: -10,
                    y: 20
                },
                defaultTheme: false
            }
        });
    })();

}).apply( this, [ jQuery ]);


var flotDashSpot = $.plot('#flotDashSpot', flotDashDataSpot, {
    series: {
        lines: {
            show: true,
            fill: true,
            lineWidth: 1,
            fillColor: {
                colors: [{
                    opacity: 0.45
                }, {
                    opacity: 0.45
                }]
            }
        },
        points: {
            show: true
        },
        shadowSize: 0
    },
    grid: {
        hoverable: true,
        clickable: true,
        borderColor: 'rgba(0,0,0,0.1)',
        borderWidth: 1,
        labelMargin: 15,
        backgroundColor: 'transparent'
    },
    yaxis: {
        min: 0,
        color: 'rgba(0,0,0,0.1)'
    },
    xaxis: {
        color: 'rgba(0,0,0,0)',
        tickDecimals:0
    },
    tooltip: true,
    tooltipOpts: {
        content: '%s: Value of %x is %y',
        shifts: {
            x: -60,
            y: 25
        },
        defaultTheme: false
    }
});

var flotDashNumismatic = $.plot('#flotDashNumismatic', flotDashDataNumismatic, {
    series: {
        lines: {
            show: true,
            fill: true,
            lineWidth: 1,
            fillColor: {
                colors: [{
                    opacity: 0.45
                }, {
                    opacity: 0.45
                }]
            }
        },
        points: {
            show: true
        },
        shadowSize: 0
    },
    grid: {
        hoverable: true,
        clickable: true,
        borderColor: 'rgba(0,0,0,0.1)',
        borderWidth: 1,
        labelMargin: 15,
        backgroundColor: 'transparent'
    },
    yaxis: {
        min: 0,
        //max: 400,
        color: 'rgba(0,0,0,0.1)'
    },
    xaxis: {
        color: 'rgba(0,0,0,0)',
        tickDecimals:0
    },
    tooltip: true,
    tooltipOpts: {
        content: '%s: Value of %x is %y',
        shifts: {
            x: -60,
            y: 25
        },
        defaultTheme: false
    }
});


var flotDashTotalCost = $.plot('#flotDashTotalCost', flotDashDataTotalCost, {
    series: {
        lines: {
            show: true,
            fill: true,
            lineWidth: 1,
            fillColor: {
                colors: [{
                    opacity: 0.45
                }, {
                    opacity: 0.45
                }]
            }
        },
        points: {
            show: true
        },
        shadowSize: 0
    },
    grid: {
        hoverable: true,
        clickable: true,
        borderColor: 'rgba(0,0,0,0.1)',
        borderWidth: 1,
        labelMargin: 15,
        backgroundColor: 'transparent'
    },
    yaxis: {
        min: 0,
        //max: 400,
        color: 'rgba(0,0,0,0.1)'
    },
    xaxis: {
        color: 'rgba(0,0,0,0)',
        tickDecimals:0
    },
    tooltip: true,
    tooltipOpts: {
        content: '%s: Value of %x is %y',
        shifts: {
            x: -60,
            y: 25
        },
        defaultTheme: false
    }
});




var vectorMapDashOptionsMints = {
    map: 'world_en',
    backgroundColor: null,
    color: '#FFFFFF',
    hoverOpacity: 0.7,
    selectedColor: '#005599',
    enableZoom: true,
    borderWidth:1,
    showTooltip: true,
    values: sample_data,
    scaleColors: ['#83c6e8'],
    normalizeFunction: 'polynomial'
};

$('#vectorMapMints').vectorMap(vectorMapDashOptionsMints);

var vectorMapDashOptionsCountry = {
    map: 'world_en',
    backgroundColor: null,
    color: '#FFFFFF',
    hoverOpacity: 0.7,
    selectedColor: '#005599',
    enableZoom: true,
    borderWidth:1,
    showTooltip: true,
    values: sample_data,
    scaleColors: ['#83c6e8'],
    normalizeFunction: 'polynomial'
};

$('#vectorMapCountry').vectorMap(vectorMapDashOptionsCountry);


