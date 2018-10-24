<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use \app\models\MetalType;
use \app\models\MetalGrade;
use \app\models\MetalShape;
use \app\models\MetalVendors;
use \yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $model app\models\MetalStack */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="metal-stack-form">

    <?php $form = ActiveForm::begin(
        [
            'options' => ['class' => 'form-horizontal'],
            'fieldConfig' => [
                'template' => "{label}\n<div class=\"col-lg-4\">{input}</div>\n<div class=\"col-lg-5\">{error}</div>",
                'labelOptions' => ['class' => 'col-lg-3 control-label'],
            ],
        ]
    );
    ?>
    <?=$form->errorSummary($model); ?>
    <?php
    $author_id = Yii::$app->user->id;
    ?>


    <?= $form->field($model, 'metalSymbol')->dropDownList(ArrayHelper::map(MetalType::find()->asArray()->all(), 'ID', 'metalDescription'),  array('prompt'=>'Choose')) ?>
    <?= $form->field($model, 'metalVendor')->dropDownList(ArrayHelper::map(MetalVendors::find()->asArray()->all(), 'ID', 'vendorName'),  array('prompt'=>'Choose')) ?>
    <?= $form->field($model, 'metalShape')->dropDownList(ArrayHelper::map(MetalShape::find()->asArray()->all(), 'ID', 'shape'),  array('prompt'=>'Choose')) ?>
    <?= $form->field($model, 'metalGrade')->dropDownList(ArrayHelper::map(MetalGrade::find()->asArray()->all(), 'ID', 'metalGrade'),  array('prompt'=>'Choose')) ?>
    <?= $form->field($model, 'metalOriginMint')->dropDownList(ArrayHelper::map(\app\models\MetalMint::find()->asArray()->all(), 'id', 'title'),  array('prompt'=>'Choose')) ?>
    <?= $form->field($model, 'metalDescription')->textarea() ?>
    <?php
    //echo \yii\Aida::$qwe;
    /*
    echo $model->metalPurchaseDate;
    $date = \yii\jui\DatePicker::widget([
        'model' => $model,
        'attribute' => 'metalPurchaseDate',
        //'language' => 'ru',
        'dateFormat' => 'yyyy-MM-dd',
        'options'=>array('class'=>'col-lg-6',),
    ]);
    */
    ?>


    <?= $form->field($model, 'metalPurchasePrice', ['template' => "{label}\n
                                                                    <div class=\"col-lg-2\">
                                                                        <div class=\"input-group input-group-icon\">
                                                                            {input}
                                                                            <span class=\"input-group-addon\">
                                                                                <span class=\"icon\"><i class=\"fa fa-dollar\"></i></span>
                                                                            </span>
                                                                        </div>
                                                                    </div>\n
                                                                    <div style='float: left; margin-top: 5px; margin-left: -10px;'>/piece</div>
                                                                    <div class=\"col-lg-5\">{error}</div>",])->textInput() ?>

    <?= $form->field($model, 'metalQuantity', ['template' => "{label}\n<div class=\"col-lg-2\">
                                                                        {input}
                                                                    </div>\n
                                                                    <div class=\"col-lg-5\">{error}</div>",])->textInput() ?>

    <?= $form->field($model, 'metalShippingCost', ['template' => "{label}\n<div class=\"col-lg-2\">
                                                                        <div class=\"input-group input-group-icon\">
                                                                            {input}
                                                                            <span class=\"input-group-addon\">
                                                                                <span class=\"icon\"><i class=\"fa fa-dollar\"></i></span>
                                                                            </span>
                                                                        </div>
                                                                    </div>\n
                                                                    <div class=\"col-lg-5\">{error}</div>",])->textInput() ?>

    <?= $form->field($model, 'metalTotalPaid', ['template' => "{label}\n<div class=\"col-lg-2\">
                                                                        <div class=\"input-group input-group-icon\">
                                                                            {input}
                                                                            <span class=\"input-group-addon\">
                                                                                <span class=\"icon\"><i class=\"fa fa-dollar\"></i></span>
                                                                            </span>
                                                                        </div>
                                                                    </div>\n
                                                                    <div class=\"col-lg-5\">{error}</div>",
                                                'inputOptions'=>['readonly'=>'readonly', 'class'=>'form-control',],
                                                ])->textInput() ?>

    <?php

    $model->metalPurchaseDate = date('d-m-Y',strtotime($model->metalPurchaseDate));
    ?>
    <?= $form->field($model, 'metalPurchaseDate',
        [
            'template' => "{label}\n<div class=\"col-lg-2\">{input}</div>\n<div class=\"col-lg-5\">{error}</div>",
            'inputOptions' => ['class' => 'datepicker form-control'],
        ]); ?>
    <?= $form->field($model, 'weight', ['template' => "{label}\n<div class=\"col-lg-2\">
                                                                        {input}
                                                                    </div>\n
                                                                    <div style='float: left; margin-top: 5px; margin-left: -10px;'>/troy ounce</div>
                                                                    <div class=\"col-lg-5\">{error}</div>",])->textInput() ?>
    <?= $form->field($model, 'spotPrice', ['template' => "{label}\n<div class=\"col-lg-2\">
                                                                        <div class=\"input-group input-group-icon\">
                                                                            {input}
                                                                            <span class=\"input-group-addon\">
                                                                                <span class=\"icon\"><i class=\"fa fa-dollar\"></i></span>
                                                                            </span>
                                                                        </div>
                                                                    </div>\n
                                                                    <div style='float: left; margin-top: 5px; margin-left: -10px;'>/troy ounce</div>
                                                                    <div class=\"col-lg-5\">{error}</div>",])->textInput(['readonly'=>'readonly',]) ?>

    <?= $form->field($model, 'totalSpotPrice', ['template' => "{label}\n<div class=\"col-lg-2\">
                                                                        <div class=\"input-group input-group-icon\">
                                                                            {input}
                                                                            <span class=\"input-group-addon\">
                                                                                <span class=\"icon\"><i class=\"fa fa-dollar\"></i></span>
                                                                            </span>
                                                                        </div>
                                                                    </div>\n
                                                                    <div style='float: left; margin-top: 5px; margin-left: -10px;'>/whole weight</div>
                                                                    <div class=\"col-lg-5\">{error}</div>",])->textInput(['readonly'=>'readonly',]) ?>


    <?= $form->field($model, 'currentPrice', ['template' => "{label}\n<div class=\"col-lg-2\" style='width: auto'>
                                                                        <div class='form-control' style='border: none; box-shadow:none; padding-left: 0;padding-right: 0;'>
                                                                            <span class='currentPriceSpan' >$model->currentPrice</span>
                                                                            <span class=\"icon\"><i class=\"fa fa-dollar\"></i></span>
                                                                        </div>
                                                                    </div>\n
                                                                    <div style='float: left; margin-top: 5px; margin-left: -10px;'>/troy ounce</div>
                                                                    <div class=\"col-lg-5\">{error}</div>",])->textInput() ?>

    <?= $form->field($model, 'numismatic_value', ['template' => "{label}\n<div class=\"col-lg-2\">
                                                                        <div class=\"input-group input-group-icon\">
                                                                            {input}
                                                                            <span class=\"input-group-addon\">
                                                                                <span class=\"icon\"><i class=\"fa fa-dollar\"></i></span>
                                                                            </span>
                                                                        </div>
                                                                    </div>\n
                                                                    <div style='float: left; margin-top: 5px; margin-left: -10px;'>/piece</div>
                                                                    <div class=\"col-lg-5\">{error}</div>",])->textInput() ?>

    <?= $form->field($model, 'total_numismatic_value', ['template' => "{label}\n<div class=\"col-lg-2\">
                                                                        <div class=\"input-group input-group-icon\">
                                                                            {input}
                                                                            <span class=\"input-group-addon\">
                                                                                <span class=\"icon\"><i class=\"fa fa-dollar\"></i></span>
                                                                            </span>
                                                                        </div>
                                                                    </div>\n
                                                                    <div style='float: left; margin-top: 5px; margin-left: -10px;'>/whole weight</div>
                                                                    <div class=\"col-lg-5\">{error}</div>",])->textInput(['readonly'=>'readonly',]) ?>


    <?= $form->field($model, 'mintMark')->textInput() ?>
    <?= $form->field($model, 'roll')->radioList( ['0'=>'No', '1'=>'Yes',],
        [
            'class' => 'btn-group',
            'data-toggle' => 'buttons',
            'item'=>function ($index, $label, $name, $checked, $value)
                {
                    return '<label class="btn btn-default' . ($checked ? ' active' : '') . '">' .
                    Html::radio($name, $checked, ['value' => $value, 'class' => 'project-status-btn']) . $label . '</label>';
                },
        ]
    );
    ?>
    <?= $form->field($model, 'roll_id')->dropDownList(ArrayHelper::map(\app\models\MetalStack::find()->mine()->bigRolls()->all(), 'ID', 'title'),  array('prompt'=>'Choose')) ?>

    <div class="form-group" >
        <div class="col-lg-offset-3 col-lg-4" >
            <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Add') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
