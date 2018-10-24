<?php
//use yii\Aida;
use app\models\search\MetalStackSearch;
use app\models\MetalStack;
use yii\bootstrap\BootstrapPluginAsset;
/* @var $this yii\web\View */
$this->title = Yii::$app->name;
$this->registerMetaTag([
    'name' => 'description',
    'content' => 'this is another meta tag'
], 'description');

?>

    <div class="row mb-4">
        <?php
        $cssClasses = [
            0=>'primary',
            1=>'secondary',
            2=>'tertiary',
            3=>'quartenary',
        ];
        foreach ($todayHistories as $n=>$history) {
            ?>
            <div class="col-md-3 col-lg-3 col-xl-3">
                <section class="card card-featured-left card-featured-<?=$cssClasses[$n];?>">
                    <div class="card-body">
                        <div class="widget-summary">
                            <div class="widget-summary-col widget-summary-col-icon">
                                <div class="summary-icon bg-<?=$cssClasses[$n];?>">
                                    <i class="fa fa-usd"></i>
                                </div>
                            </div>
                            <div class="widget-summary-col">
                                <div class="summary">
                                    <h4 class="title" style="text-transform: uppercase;">SPOT - <?=$history->type->metalDescription;?></h4>
                                    <div class="info">
                                        <strong class="amount">$<?=$history->metalValue;?></strong> /
                                        <span class="text-primary">$<?=$yesterdayHistories[$n]->metalValue;?></span>
                                    </div>
                                </div>
                                <div class="summary-footer">
                                    <a class="text-muted text-uppercase" href="<?=\yii\helpers\Url::to(['history/index', 'HistorySearch[metalSymbol]'=>$history->metalSymbol,]);?>">(view history)</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
            <?php
        }
        ?>

    </div>


    <div class="row mt-4 mb-4">
        <div class="col-md-12 col-xs-12">
            <section class="card">
                <header class="card-header">
                    <div class="card-actions">
                        <a class="fa fa-caret-down" href="https://www.flewid.ca/dash/#"></a>
                        <a class="fa fa-times" href="https://www.flewid.ca/dash/#"></a>
                    </div>
                    <h2 class="card-title">TOTAL STACK VALUE</h2>
                    <p class="card-subtitle">Comparison of numismatic value, spot value and total cost. </p>
                </header>
                <div class="card-body">
                    <!-- Morris: Line -->
                    <div class="chart chart-md" id="morrisLine"></div>
                    <script type="text/javascript">
                        var morrisLineData = [
                            <?php
                            $a=0;
                            $b=0;
                            $c=0;
                            for($year=$minDate; $year<=$maxDate; $year++){
                                if(isset($yearStacks[$year])){
                                    $a+=$yearStacks[$year]->sumTotalPrice;
                                    $b+=$yearStacks[$year]->sumSpotPrice;
                                    $c+=$yearStacks[$year]->sumNumismaticPrice;
                                }
                                ?>
                                {
                                    y: '<?=$year;?>',
                                    a: <?=round($a,2);?>,
                                    b: <?=round($b,2);?>,
                                    c: <?=round($c,2);?>
                                },
                                <?php
                            }
                            ?>
                        ];
                        // See: assets/javascripts/ui-elements/examples.charts.js for more settings.
                    </script>
                </div>
            </section>
        </div>
    </div>


    <div class="row mt-4 mb-4">


        <div class="col-lg-6 col-xl-4" >


            <section class="card card-featured-left card-featured-primary mb-4">
                <div class="card-body">
                    <div class="widget-summary">
                        <div class="widget-summary-col widget-summary-col-icon">
                            <div class="summary-icon bg-primary">
                                <i class="fa fa-truck"></i>
                            </div>
                        </div>
                        <div class="widget-summary-col">
                            <div class="summary" >
                                <h4 class="title">Freight Cost</h4>
                                <div class="info">
                                    <strong class="amount">$<?=round($freight, 2);?></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
        <div class="col-lg-6 col-xl-4" >
            <section class="card card-featured-left card-featured-secondary mb-4">
                <div class="card-body">
                    <div class="widget-summary">
                        <div class="widget-summary-col widget-summary-col-icon">
                            <div class="summary-icon bg-secondary">
                                <i class="fas fa-money-bill"></i>
                            </div>
                        </div>
                        <div class="widget-summary-col">
                            <div class="summary">
                                <h4 class="title">Stack Cost</h4>
                                <div class="info">
                                    <strong class="amount">$<?=round($cost, 2);?></strong>

                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </section>
        </div>
        <div class="col-lg-6 col-xl-4" >
            <section class="card card-featured-left card-featured-tertiary mb-4">
                <div class="card-body">
                    <div class="widget-summary">
                        <div class="widget-summary-col widget-summary-col-icon">
                            <div class="summary-icon bg-tertiary">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                        </div>
                        <div class="widget-summary-col">
                            <div class="summary">
                                <h4 class="title">Total Cost</h4>
                                <div class="info">
                                    <strong class="amount">$<?=round($cost+$freight,2);?></strong>

                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </section>
        </div>


        <div class="col-lg-6 col-xl-4" >
            <section class="card card-featured-left card-featured-tertiary mb-4">
                <div class="card-body">
                    <div class="widget-summary">
                        <div class="widget-summary-col widget-summary-col-icon">
                            <div class="summary-icon bg-tertiary">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                        </div>
                        <div class="widget-summary-col">
                            <div class="summary" >
                                <h4 class="title">Spot Value</h4>
                                <div class="info">
                                    <strong class="amount">$<?=(float) round($spot, 2);?></strong>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </section>
        </div>
        <div class="col-lg-6 col-xl-4" >
            <section class="card card-featured-left card-featured-info mb-4">
                <div class="card-body">
                    <div class="widget-summary">
                        <div class="widget-summary-col widget-summary-col-icon">
                            <div class="summary-icon bg-info">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                        </div>
                        <div class="widget-summary-col">
                            <div class="summary" >
                                <h4 class="title">Numismatic</h4>
                                <div class="info">
                                    <strong class="amount"><?php

                                        $NMST = $numismatic - $spot;
                                        $NMST = round($NMST, 2);
                                        if($NMST = abs($NMST))
                                            echo "+$";
                                        else
                                            echo "-$";
                                        echo $NMST;
                                        ?></strong>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </section>
        </div>
        <div class="col-lg-6 col-xl-4" >
            <?php
            $totalPaid = $cost + $freight;
            if($spot<$totalPaid)
                $css = "warning";
            else
                $css = "success";
            ?>
            <section class="card card-featured-left card-featured-<?=$css;?> mb-4">
                <div class="card-body">
                    <div class="widget-summary">
                        <div class="widget-summary-col widget-summary-col-icon">
                            <div class="summary-icon bg-<?=$css;?>">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                        </div>
                        <div class="widget-summary-col">
                            <div class="summary" >
                                <h4 class="title">Gain/Loss</h4>
                                <div class="info">
                                    <strong class="amount"><?php
                                        if($spot<$totalPaid)
                                            echo '-$'.(round($totalPaid-$spot, 2));
                                        else
                                            echo '+$'.(round($spot - $totalPaid,2));
                                        ?></strong>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>





    <div class="row mt-4 mb-4">
        <div class="col-lg-6 mb-4 mb-lg-0">
            <section class="card">
                <header class="card-header">
                    <div class="card-actions">
                        <a class="fa fa-caret-down" href="https://www.flewid.ca/dash/#"></a>
                        <a class="fa fa-times" href="https://www.flewid.ca/dash/#"></a>
                    </div>
                    <h2 class="card-title">YEARLY TOTAL (COST / SPOT / NUMISMATIC)</h2>
                    <p class="card-subtitle">Comparison of numismatic value, spot value and total cost. </p>
                </header>
                <div class="card-body">

                    <div class="chart chart-md" id="morrisBarNumismatic"></div>
                    <script type="text/javascript">
                        var morrisBarDataNumismatic = [
                            <?php
                            foreach ($last4YearStacks as $year=>$last4YearStack) {
                                ?>
                            {
                                y: '<?php
                                if($year==date('Y'))
                                    echo date("$year(M", strtotime($year."-01-01")).date("-M)");
                                else
                                    echo $year;
                                ?>',
                                a: <?=(float) $last4YearStack['sumTotalPrice'];?>,
                                b: <?=(float) $last4YearStack['sumNumismaticPrice'];?>
                            },
                            <?php
                            }
                        ?>
                        ];
                        // See: assets/javascripts/ui-elements/examples.charts.js for more settings.
                    </script>
                </div>
            </section>
        </div>
        <div class="col-lg-6 mb-4 mb-lg-0">
            <section class="card">
                <header class="card-header">
                    <div class="card-actions">
                        <a class="fa fa-caret-down" href="https://www.flewid.ca/dash/#"></a>
                        <a class="fa fa-times" href="https://www.flewid.ca/dash/#"></a>
                    </div>
                    <h2 class="card-title">YEARLY TOTAL (COST VS SPOT)</h2>
                    <p class="card-subtitle">Total Spent (including freight) on stack, vs value of entire stack (all metals)</p>
                </header>
                <div class="card-body">

                    <div class="chart chart-md" id="morrisBar"></div>
                    <script type="text/javascript">
                        var morrisDataBar = [
                            <?php
                            foreach ($last4YearStacks as $year=>$last4YearStack) {
                                ?>
                            {
                                y: '<?php
                                if($year==date('Y'))
                                    echo date("$year(M", strtotime($year."-01-01")).date("-M)");
                                else
                                    echo $year;
                                ?>',
                                a: <?=(float) $last4YearStack['sumTotalPrice'];?>,
                                b: <?=(float) $last4YearStack['sumSpotPrice'];?>
                            },
                            <?php
                            }
                        ?>
                        ];
                        // See: assets/javascripts/ui-elements/examples.charts.js for more settings.
                    </script>
                </div>
            </section>
        </div>
    </div>



    <div class="row mt-4 mb-4">
    <div class="col-md-4">
        <section class="card">
            <header class="card-header">
                <div class="card-actions">
                        <a class="fa fa-caret-down" href="https://www.flewid.ca/dash/#"></a>
                        <a class="fa fa-times" href="https://www.flewid.ca/dash/#"></a>
                    </div>
                <h2 class="card-title">YEARLY (SPOT VALUE)</h2>
                <p class="card-subtitle">Shown per metal with spot value</p>
            </header>
            <div class="card-body">

                <!-- Flot: Basic -->
                <div style="position: relative; padding: 0px;" id="flotDashSpot" class="chart chart-md">

                </div>
                <script>

                    var flotDashDataSpot = [
                        <?php
                        $colors=['#0088cc', "#2baab1", "#734ba9", "#3c3c3c"];
                        foreach (\app\models\MetalType::find()->all() as $n=>$metalType)
                        {
                            ?>
                            {
                                data: [
                                    <?php
                                    for($year = $yearNumber-4; $year<=$yearNumber; $year++)
                                    {
                                        $val=0;
                                        foreach($yearSeparateStacks as $value)
                                            if($value->metalSymbol==$metalType->ID && $value->year==$year)
                                                $val = $value->sumSpotPrice;
                                        ?>
                                        [<?=$year;?>, <?=$val;?>],
                                        <?php
                                    }
                                    ?>
                                ],
                                label: "<?=$metalType->metalDescription;?>",
                                color: "<?=$colors[$n];?>"
                            },
                            <?php
                        }
                        ?>
                     ];

                    // See: assets/javascripts/dashboard/examples.dashboard.js for more settings.

                </script>

            </div>
        </section>
    </div>
    <div class="col-md-4">
        <section class="card">
            <header class="card-header">

                <div class="card-actions">
                        <a class="fa fa-caret-down" href="https://www.flewid.ca/dash/#"></a>
                        <a class="fa fa-times" href="https://www.flewid.ca/dash/#"></a>
                    </div>
                <h2 class="card-title">YEARLY (NUMISMATIC VALUE)</h2>
                <p class="card-subtitle">Shown per metal with numismatic value</p>
            </header>
            <div class="card-body">

                <!-- Flot: Basic -->
                <div style="position: relative; padding: 0px;" id="flotDashNumismatic" class="chart chart-md">
                </div>
                <script>

                    var flotDashDataNumismatic = [
                        <?php
                            $colors=['#0088cc', "#2baab1", "#734ba9", "#3c3c3c"];
                            foreach (\app\models\MetalType::find()->all() as $n=>$metalType)
                            {
                                ?>
                        {
                            data: [
                                <?php
                                for($year = $yearNumber-4; $year<=$yearNumber; $year++)
                                {
                                    $val=0;
                                    foreach($yearSeparateStacks as $value)
                                        if($value->metalSymbol==$metalType->ID && $value->year==$year)
                                            $val = $value->sumNumismaticPrice;
                                    ?>
                                [<?=$year;?>, <?=$val;?>],
                                <?php
                            }
                            ?>
                            ],
                            label: "<?=$metalType->metalDescription;?>",
                            color: "<?=$colors[$n];?>"
                        },
                        <?php
                    }
                        ?>
                    ];

                    // See: assets/javascripts/dashboard/examples.dashboard.js for more settings.

                </script>

            </div>
        </section>


    </div>
    <div class="col-md-4">
        <section class="card">
            <header class="card-header">
                <div class="card-actions">
                        <a class="fa fa-caret-down" href="https://www.flewid.ca/dash/#"></a>
                        <a class="fa fa-times" href="https://www.flewid.ca/dash/#"></a>
                    </div>
                <h2 class="card-title">YEARLY (METAL COST)</h2>
                <p class="card-subtitle">Not including Freight</p>
            </header>
            <div class="card-body">

                <!-- Flot: Basic -->
                <div style="position: relative; padding: 0px;" id="flotDashTotalCost" class="chart chart-md">

                </div>
                <script>

                    var flotDashDataTotalCost = [
                        <?php
                            $colors=['#0088cc', "#2baab1", "#734ba9", "#3c3c3c"];
                            foreach (\app\models\MetalType::find()->all() as $n=>$metalType)
                            {
                                ?>
                        {
                            data: [
                                <?php
                                for($year = $yearNumber-4; $year<=$yearNumber; $year++)
                                {
                                    $val=0;
                                    foreach($yearSeparateStacks as $value)
                                        if($value->metalSymbol==$metalType->ID && $value->year==$year)
                                            $val = $value->sumTotalPrice;
                                    ?>
                                [<?=$year;?>, <?=$val;?>],
                                <?php
                            }
                            ?>
                            ],
                            label: "<?=$metalType->metalDescription;?>",
                            color: "<?=$colors[$n];?>"
                        },
                        <?php
                    }
                    ?>
                    ];

                    // See: assets/javascripts/dashboard/examples.dashboard.js for more settings.

                </script>

            </div>
        </section>


    </div>
</div>





    <div class="row mt-4 mb-4">
        <div class="col-md-6">
            <section class="card">
                <header class="card-header">
                    <div class="card-actions">
                        <a class="fa fa-caret-down" href="https://www.flewid.ca/dash/#"></a>
                        <a class="fa fa-times" href="https://www.flewid.ca/dash/#"></a>
                    </div>

                    <h2 class="card-title">TOTAL STACK ITEM WEIGHT</h2>
                    <p class="card-subtitle">The weight of your stack by metal, in Troy Ounce.</p>
                </header>
                <div class="card-body">

                    <!-- Flot: Bars -->
                    <div style="position: relative; padding: 0px;" id="flotPie2" class="chart chart-md">
                    </div>
                    <script type="text/javascript">

                        var flotPie2Data = [
                            <?php
                            $colors=['#0088cc', "#2baab1", "#734ba9", "#E36159"];
                            foreach ($weightStacks as $n=>$value)
                            {
                                ?>
                                {
                                    label: "<?=$value->type->metalDescription;?>",
                                    data: [
                                        [<?=$n;?>, <?=(float) $value->weight;?>]
                                    ],
                                    color: '<?=$colors[$n];?>'
                                },
                                <?php
                            }
                            ?>
                        ];


                        // See: assets/javascripts/ui-elements/examples.charts.js for more settings.

                    </script>

                </div>
            </section>
        </div>
        <div class="col-md-6">

            <section class="card">
                <header class="card-header">
                    <div class="card-actions">
                        <a class="fa fa-caret-down" href="https://www.flewid.ca/dash/#"></a>
                        <a class="fa fa-times" href="https://www.flewid.ca/dash/#"></a>
                    </div>

                    <h2 class="card-title">TOTAL STACK ITEM COUNT</h2>
                    <p class="card-subtitle">Percentages of metal owned</p>
                </header>
                <div class="card-body">

                    <!-- Flot: Pie -->
                    <div style="position: relative; padding: 0px;" id="flotPie" class="chart chart-md">

                    </div>
                    <script type="text/javascript">

                        var flotPieData = [
                            <?php
                            $colors=['#0088cc', "#2baab1", "#734ba9", "#E36159"];
                            foreach ($countStacks as $n=>$value) {
                                    ?>
                            {
                                label: "<?=$value->type->metalDescription;?>",
                                data: [
                                    [<?=$n;?>, <?=(float) $value->count;?>]
                                ],
                                color: '<?=$colors[$n];?>'
                            },
                                    <?php
                            }
                            ?>
                        ];

                        // See: assets/javascripts/ui-elements/examples.charts.js for more settings.

                    </script>

                </div>
            </section>

        </div>
    </div>

    <div class="row mt-4 mb-4">
        <div class="col-xl-6 col-lg-6">
            <section class="card">
                <header class="card-header">
                    <div class="card-actions">
                        <a class="fa fa-caret-down" href="https://www.flewid.ca/dash/#"></a>
                        <a class="fa fa-times" href="https://www.flewid.ca/dash/#"></a>
                    </div>
                    <h2 class="card-title">SELLER COUNTRIES</h2>
                </header>
                <div class="card-body">
                    <div style="height: 350px; width: 100%; position: relative; overflow: hidden;"
                         id="vectorMapCountry">
                    </div>
                </div>
            </section>
        </div>

        <div class="col-xl-6 col-lg-6">
            <section class="card">
                <header class="card-header">
                    <div class="card-actions">
                        <a class="fa fa-caret-down" href="https://www.flewid.ca/dash/#"></a>
                        <a class="fa fa-times" href="https://www.flewid.ca/dash/#"></a>
                    </div>
                    <h2 class="card-title">ALL MINTS IN STACK</h2>
                </header>
                <div class="card-body">
                    <div style="height: 350px; width: 100%; position: relative; overflow: hidden;"
                         id="vectorMapMints">
                    </div>
                </div>
            </section>
        </div>
    </div>



    <div class="row mt-4 mb-4">
        <div class="col-xl-12 col-lg-12">
            <section class="card">
                <header class="card-header">
                    <div class="card-actions">
                        <a class="fa fa-caret-down" href="https://www.flewid.ca/dash/#"></a>
                        <a class="fa fa-times" href="https://www.flewid.ca/dash/#"></a>
                    </div>
                    <h2 class="card-title">RECENT ACTIVITY LOG</h2>
                </header>
                <div class="card-body">

                    <div class="timeline timeline-simple mt-xlg mb-md">
                        <div class="tm-body">

                            <?php
                            $monthes=[];
                            foreach ($recentLogs as $log)
                            {
                                $month=Yii::$app->formatter->asDate($log->date, 'php:F Y');
                                $monthes[$month]=strtoupper($month);
                            }
                            foreach ($monthes as $key=>$month) {
                                ?>

                                <div class="tm-title">
                                    <h3 class="h5 text-uppercase"><?=$month;?></h3>
                                </div>
                                <ol class="tm-items">
                                    <?php
                                    foreach ($recentLogs as $log)
                                        if($key==Yii::$app->formatter->asDate($log->date, 'php:F Y'))
                                    {
                                        ?>
                                        <li>
                                            <div class="tm-box">
                                                <p class="text-muted mb-none"><?=Yii::$app->formatter->asDateTimeText($log->date);?></p>
                                                <p>
                                                    <?=$log->description;?>
                                                </p>
                                            </div>
                                        </li>
                                    <?php
                                    }
                                    ?>
                                </ol>
                                <?php
                            }
                            ?>
                        </div>
                    </div>

                </div>
            </section></div>
    </div>

    <script>
        var toz = function (label, series) {

            return '<div style="font-size:x-small;text-align:center;padding:2px;color:'+series.color+';">'+label+'<br>'+Math.round(series.percent)+' t oz</div>'

        }
    </script>

<?php

$this->registerJs("



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
                        threshold: 0.001
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
        var plot = $.plot('#flotPie2', flotPie2Data, {
            series: {
                pie: {
                    show: true,
                    combine: {
                        color: '#999',
                        threshold: 0.001
                    },
                    label: {
                        show: true,
                        formatter: toz,
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


    /*
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
    */

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
    //values: sample_data,
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
    //values: sample_data,
    scaleColors: ['#83c6e8'],
    normalizeFunction: 'polynomial'
};

$('#vectorMapCountry').vectorMap(vectorMapDashOptionsCountry);






");

$mintCountries = MetalStackSearch::find()
    ->mine()
    ->notInRoll()
    ->open()
    ->joinWith('mint')
    ->joinWith('mint.country')
    ->where('metalmint.country_id')
    ->groupBy(['metalmint.country_id'])
;
foreach ($mintCountries->all() as $stack) {
    $this->registerJs("jQuery('#vectorMapMints').vectorMap('set', 'colors', { {$stack->mint->country->alpha2code}: '#005599'}); \n");
}
$vendorCountries = MetalStackSearch::find()
    ->mine()
    ->notInRoll()
    ->open()
    ->joinWith('vendor')
    ->joinWith('vendor.country')
    ->where('vendorCountry')
    ->groupBy(['vendorCountry'])
;
foreach ($vendorCountries->all() as $stack) {
    $this->registerJs("jQuery('#vectorMapCountry').vectorMap('set', 'colors', { {$stack->vendor->country->alpha2code}: '#005599'}); \n");
}

?>