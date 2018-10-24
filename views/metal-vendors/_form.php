<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\MetalVendors */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="metal-vendors-form">

    <?php $form = ActiveForm::begin(); ?>
    <?=$form->errorSummary($model); ?>
    <?= $form->field($model, 'vendorName')->textInput(['maxlength' => 128]) ?>

    <?= $form->field($model, 'vendorSite')->textInput(['maxlength' => 255]) ?>

    <?= $form->field($model, 'vendorCountry')->dropDownList(\yii\helpers\ArrayHelper::map(\app\models\Country::find()->asArray()->all(), 'id', 'title'),  array('prompt'=>'Choose')) ?>
    <?php
    if(Yii::$app->user->identity->role==\app\models\User::ROLE_ADMIN && ($model->isNewRecord || $model->author_id==Yii::$app->user->id))
        echo $form->field($model, 'default')->dropDownList(['0'=>'No', '1'=>'Yes',]);
    ?>
    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Add') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success float-right' : 'btn btn-primary float-right']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
