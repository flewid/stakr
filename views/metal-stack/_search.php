<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\search\MetalStackSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="metal-stack-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'ID') ?>

    <?= $form->field($model, 'metalSymbol') ?>

    <?= $form->field($model, 'metalVendor') ?>


    <?php // echo $form->field($model, 'metalShippingCost') ?>

    <?php // echo $form->field($model, 'metalGrade') ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('app', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
