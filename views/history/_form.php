<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\datetime\DateTimePicker;

/* @var $this yii\web\View */
/* @var $model app\models\History */
/* @var $form yii\widgets\ActiveForm */
?>


<div class="history-form">

    <?php $form = ActiveForm::begin(); ?>

    <?php
    /*$input = \yii\jui\DatePicker::widget([
        'model' => $model,
        'attribute' => 'metalValueDate',
        'dateFormat' => 'yyyy-MM-dd',
        'options'=>array('class'=>'col-lg-6',),
    ]);*/


    $input = DateTimePicker::widget([
        'model' => $model,
        'attribute' => 'metalValueDate',
        'options' => ['placeholder' => 'Select operating time ...'],
        'convertFormat' => true,
        'pluginOptions' => [
            'format' => 'yyyy-MM-dd H:i',
            //'format' => 'd-M-Y g:i A',
            //'startDate' => '01-Mar-2014 12:00 AM',
            'todayHighlight' => true
        ]
    ]);

    ?>
    <?= $form->field($model, 'metalValueDate', ['template' => "{label}\n<div class=\"col-lg-4\" style='float: none; padding: 0;'>
                                                                        {$input}
                                                                    </div>\n
                                                                    {error}",
                                                'inputOptions'=>['class'=>'datepicker1 form-control',],])->textInput() ?>
    <?= $form->field($model, 'metalSymbol', ['template' => "{label}\n<div class=\"col-lg-2\" style='float: none; padding: 0;'>
                                                                        {input}
                                                                    </div>\n
                                                                    {error}",])
        ->dropDownList(\yii\helpers\ArrayHelper::map(\app\models\MetalType::find()->asArray()->all(), 'ID', 'metalDescription'),  array('prompt'=>'Choose',
            'data-plugin-selecttwo'=>'',)) ?>
    <?= $form->field($model, 'metalValue', ['template' => "{label}\n<div class=\"col-lg-2\" style='float: none; padding: 0;'>
                                                                        {input}
                                                                    </div>\n
                                                                    {error}",])->textInput() ?>
    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
