<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\MetalGrade */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="metal-grade-form">

    <?php $form = ActiveForm::begin(); ?>
    <?=$form->errorSummary($model); ?>
    <?= $form->field($model, 'metalGrade')->textInput(['maxlength' => 255]) ?>

    <?= $form->field($model, 'metalGradeDescription')->textInput(['maxlength' => 255]) ?>

    <?= $form->field($model, 'metalGradeScale')->textInput(['maxlength' => 255]) ?>
    <?php
    if(Yii::$app->user->can('adminPermission') && ($model->isNewRecord || $model->author_id==Yii::$app->user->id))
        echo $form->field($model, 'default')->dropDownList(['0'=>'No', '1'=>'Yes',]);
    ?>
    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Add') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success float-right' : 'btn btn-primary float-right']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
