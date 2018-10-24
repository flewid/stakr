<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\MetalType */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="metal-type-form">

    <?php $form = ActiveForm::begin(); ?>
    <?=$form->errorSummary($model); ?>
    <?= $form->field($model, 'metalSymbol')->textInput(['maxlength' => 3]) ?>

    <?= $form->field($model, 'metalDescription')->textInput(['maxlength' => 255]) ?>
    <?php
    if(Yii::$app->user->identity->role==\app\models\User::ROLE_ADMIN && ($model->isNewRecord || $model->author_id==Yii::$app->user->id))
        echo $form->field($model, 'default')->dropDownList(['0'=>'No', '1'=>'Yes',]);
    ?>
    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Add') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success float-right' : 'btn btn-primary float-right']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
