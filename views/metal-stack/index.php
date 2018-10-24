<?php

use yii\helpers\Html;
use yii\grid\GridView;
use \app\models\MetalType;
use \app\models\MetalGrade;
use \app\models\MetalShape;
use \app\models\MetalVendors;
use \yii\helpers\ArrayHelper;
use yii\bootstrap\Modal;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel app\models\search\MetalStackSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Metal Stacks');
$this->params['breadcrumbs'][] = $this->title;
?>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p class="float-right pClassInView">
        <?= Html::a(Yii::t('app', 'Add To Stack'), ['create'], ['class' => 'btn btn-success']) ?>
        <?= Html::a(Yii::t('app', 'Download stacks'), ['download'],
            [
                'class' => 'btn btn-primary '.(Yii::$app->user->identity->offline_mode ? 'downloadStacks':''),
                'data' => [
                    //'confirm' => 'Download stacks',
                ],
            ]) ?>
        <?= Html::a(Yii::t('app', 'Upload stacks'), ['upload'],
            [
                'class' => 'btn btn-primary',
            ]) ?>
    </p>

    <?php
    $actionColumn= [
        'class' => 'yii\grid\ActionColumn',
        'headerOptions' => ['width' => '17%'],
        'template'=>'<div class="text-center">{view} {update} {delete} <br/> {sold} {trade} {lost}</div>',
        'buttons' => [
            'view' => function ($url, $model,$key) {
                    if(Yii::$app->user->can('updateOwnModel', ['model' => $model]))
                        return Html::a('<i class="far fa-eye"></i>', $url, [
                            'title' => Yii::t('app', 'lead-view'),
                            'data-pjax' => '0'
                        ]);
            },
            'update' => function ($url, $model, $key)
                {
                    if(Yii::$app->user->can('updateOwnModel', ['model' => $model]))
                        return Html::a('<i class="fas fa-pencil-alt"></i>', $url, [
                            'title' => Yii::t('yii', 'Update'),
                            'data-pjax' => '0',
                        ]);
                },
            'delete' => function ($url, $model, $key) {
                    if(Yii::$app->user->can('updateOwnModel', ['model' => $model]))
                        return Html::a('<i class="far fa-trash-alt"></i>', $url, [
                            'title' => Yii::t('yii', 'Delete'),
                            'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
                            'data-method' => 'post',
                            'data-pjax' => '0',
                        ]);
                },
            'sold' => function ($url, $model, $key) {
                    if(Yii::$app->user->can('updateOwnModel', ['model' => $model]))
                        return Html::a('Sold', $url, [
                            'title' => Yii::t('yii', 'Sold'),
                            'data-id' => $model->ID,
                            'class' => 'btn btn-primary btn-xs soldButton',
                            'data-pjax' => '0',
                        ]);
                },
            'trade' => function ($url, $model, $key) {
                    if(Yii::$app->user->can('updateOwnModel', ['model' => $model]))
                        return Html::a('Trade', $url, [
                            'title' => Yii::t('yii', 'Trade'),
                            'data-id' => $model->ID,
                            'class' => 'btn btn-primary btn-xs tradeButton',
                            'data-pjax' => '0',
                        ]);
                },
            'lost' => function ($url, $model, $key) {
                    if(Yii::$app->user->can('updateOwnModel', ['model' => $model]))
                        return Html::a('Lost', $url, [
                            'title' => Yii::t('yii', 'Lost'),
                            'class' => 'btn btn-primary btn-xs',
                            'data-confirm' => Yii::t('yii', 'Are you sure you want this item lost?'),
                            'data-method' => 'post',
                            'data-pjax' => '0',
                        ]);
                },
        ],

    ];
    ?>
    <?= GridView::widget([
        'tableOptions'=>['class'=>'table table-responsive-md mb-0',],
        'dataProvider' => $dataProvider,
        'pager' => [
            "pageCssClass"=>"page-item",
            "linkOptions"=>["class"=>"page-link"],
            "disabledListItemSubTagOptions" => ['tag' => 'a', 'class' => 'page-link',"href"=>"#"]
        ],
        'filterModel' => $searchModel,
        'columns' => [
            //'title',
            //'ID',
    'metalPurchaseDate',
            [
                'attribute'=>'roll',
                'header'=>'Roll',
                'format'=>'boolean',
                'filter'=>['0'=>'No', '1'=>'Yes',],
            ],
                         'metalPurchasePrice',

            [
                'attribute'=>'roll',
                'header'=>'Roll contents',
                'format'=>'raw',
                'value'=>function ($model)
                    {
                        if($model->kids)
                        {
                            $loading="<img style=\"display:none; position: absolute;\" src=\"".Yii::$app->request->baseUrl."/static/images/loading.gif\" />";
                            return Html::a('View roll', 'javascript:void(0)', [
                                'title' => Yii::t('yii', 'Down'),
                                'data-id' => $model->ID,
                                'data-pjax' => '0',
                                'class' => 'down btn btn-primary btn-xs',
                            ]).$loading;
                        }
                    },
                //'filter'=>false,
            ],
            'metalDescription',
            [
                'attribute'=>'metalSymbol',
                'value'=>function($data) { return $data->type->metalDescription; },
                'filter'=>ArrayHelper::map(MetalType::find()->asArray()->all(), 'ID', 'metalDescription'),
            ],
            //[
            //  'attribute'=>'metalVendor',
            //    'value'=>function($data) { return $data->vendor->vendorName; },
            //    'filter'=>ArrayHelper::map(MetalVendors::find()->asArray()->all(), 'ID', 'vendorName'),
            //],
            [
                'attribute'=>'metalShape',
                'value'=>function($data) { return $data->shape->shape; },
                'filter'=>ArrayHelper::map(MetalShape::find()->asArray()->all(), 'ID', 'shape'),
            ],
            [
                'attribute'=>'metalGrade',
                'value'=>function($data) { return $data->grade->metalGrade; },
                'filter'=>ArrayHelper::map(MetalGrade::find()->asArray()->all(), 'ID', 'metalGrade'),
            ],
            [
                'attribute'=>'author_id',
                'value'=>function($data) { return $data->author->name; },
                'filter'=>\yii\helpers\ArrayHelper::map(app\models\User::find()->asArray()->all(), 'id', 'name'),
                'visible'=>Yii::$app->user->can('adminPermission'),
            ],
            /*
            'metalPurchaseDate',
            'metalTotalPaid',
            'numismatic_value',
            */
            $actionColumn,
        ],
    ]);?>

<?php
Modal::begin([
'id'=>'customModal',
'header' => 'Download stacks',
    //'clientOptions' => ['show' => true]
]);
    ?>
    <?=Html::a('Download backup', ['download', 'download'=>1,], ['class'=>'btn btn-primary',]);?>
    <?php
        echo Html::a('Download backup + delete database', ['download-delete'], ['class'=>'btn btn-primary downloadDeleteLink',]);
    ?>
    <div class="checkbox-custom checkbox-primary" >
        <?=Html::activeCheckbox(Yii::$app->user->identity, 'offline_mode', ['label'=>null, 'class'=>'offline_modeUpdate',]);?>
        <label id="offline_mode_label" for="user-offline_mode"><?=Yii::$app->user->identity->getAttributeLabel('offline_mode');?></label>
        <img src="<?=Yii::$app->request->baseUrl;?>/static/images/loading.gif" id="loading" style="display: none">
    </div>
    <?php
Modal::end();

    //$this->beginBlock('modalContainer');


    //$this->registerCssFile(Yii::$app->request->baseUrl."/static/plugins/selectpicker/bootstrap-select.css", ['depends' => \yii\bootstrap\BootstrapAsset::className() ]);
    //$this->registerJsFile(Yii::$app->request->baseUrl."/static/plugins/selectpicker/bootstrap-select.js", ['depends' => \yii\bootstrap\BootstrapPluginAsset::className() ]);

    Modal::begin([
    'id'=>'tradeModal',
    'header' => 'Trade stack',
    //'clientOptions' => ['show' => true]
]);
?>
    <?php
    $model = new \app\models\Form();
    $form = ActiveForm::begin(
        [
            'action' => ['trade'],
            'enableAjaxValidation'=>true,
            'enableClientValidation'=>false,
            //'options' => ['class' => 'form-horizontal'],
        ]
    );
    ?>
    <?php //echo $form->errorSummary($model); ?>
    <?= $form->field($model, 'stack_id', ['template'=>'{input}',])->hiddenInput() ?>
    <?= $form->field($model, 'trade_radio', ['template'=>'{input}{error}',])->radioList( ['stack'=>'Trade to other stack', 'other'=>'Trade to other thing',],
        [
            'item'=>function ($index, $label, $name, $checked, $value)
                {
                    return '<div class="radio-custom radio-primary">
                                '.Html::radio($name, $checked, ['value' => $value, 'id'=>'radio-'.$index,]).'
                                <label for="radio-'.$index.'">'.$label.'</label>
                            </div>';
                },
        ]
    );
    ?>

    <?= $form->field($model, 'trade_text', ['options'=>['style'=>'display:none;',],])->textInput() ?>

    <?php
    $create = Html::a("Create stack", ['metal-stack/create'], ['class'=>'btn btn-primary btn-xs', 'target'=>'_blank', 'style'=>'margin:-12px 0 0 5px;',]);
    $refresh = Html::a("Refresh stack list", 'javascript:void(0);', ['class'=>'btn btn-primary btn-xs refreshStacks','style'=>'margin:-12px 0 0 5px;']);
    ?>
    <?= $form->field($model, 'trade_id', ['template'=>"Traded for another item in my stack<br>{input} $create $refresh {error}", 'options'=>['style'=>'display:none;',],])->dropDownList(ArrayHelper::map(\app\models\MetalStack::find()->mine()->notTrade()->all(), 'ID', 'title'),
        [
            //'prompt'=>Yii::t('app', 'Select the stack'),
            'data-width' => 200,
            'data-live-search'=>1,
            'data-header'=>Yii::t('app', 'Select'),
            'title'=>Yii::t('app', 'Select'),
            'class'=>'selectpicker',
        ]) ?>

    <div class="form-group" >
            <?= Html::submitButton('Save', ['class' => 'btn btn-success float-right']) ?>
    </div>

<?php ActiveForm::end(); ?>

<?php
Modal::end();

//$this->endBlock()


Modal::begin([
    'id'=>'soldModal',
    'header' => 'Sell stack',
    //'clientOptions' => ['show' => true]
]);
?>
<?php
$model = new \app\models\SoldForm();
$form = ActiveForm::begin(
    [
        'id'=>'soldForm',
        'action' => ['sold'],
        'enableAjaxValidation'=>true,
        'enableClientValidation'=>true,
        'options' => ['class' => 'form-horizontal'],
    ]
);
?>
<?php //echo $form->errorSummary($model); ?>

<?= $form->field($model, 'stack_id', ['template'=>'{input}',])->hiddenInput() ?>
<?= $form->field($model, 'sold_price', ['template' => "{label}\n<div class=\"col-lg-2\" style='padding: 0;' >
                                                                        {input}
                                                                    </div>\n
                                                                    <div class=\"col-lg-8\">{error}</div>
                                                                    ",
                                        'labelOptions' => ['class' => 'col-md-3 control-label'],
                                        ])->textInput() ?>


<div class="form-group" style="margin: 0">
    <?= Html::submitButton('Save', ['class' => 'btn btn-success float-right']) ?>
</div>

<?php ActiveForm::end(); ?>

<?php
Modal::end();
?>


<?php
/*$this->registerJsFile($this->theme->baseUrl."/simplescript.js");
$this->registerJsFile($this->theme->baseUrl."/qwe.js",[], $this->theme->baseUrl."/qwe.js");
$this->registerJsFile($this->theme->baseUrl."/assets/javascripts/ui-elements/examples.charts.morris.js", ['depends' => \yii\bootstrap\BootstrapPluginAsset::className()]);
foreach ($this->assetBundles as $bundles)
    $bundles->registerAssetFiles($this);
echo '<pre>';
print_r($this->jsFiles);
echo '</pre>';*/

echo $this->registerJs("
    $('.selectpicker').selectpicker('refresh'); \n
", \yii\web\View::POS_READY, 'selectpicker');
?>
