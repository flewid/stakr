<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\User */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="user-form">

    <?php $form = ActiveForm::begin(
        [
        ]
    ); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => 200]) ?>
    <?= $form->field($model, 'email')->textInput(['maxlength' => 200]) ?>
    <?php
    if($model->scenario=='signup')
    {
        ?>
        <?= $form->field($model, 'password')->passwordInput(['maxlength' => 200]) ?>
        <?= $form->field($model, 'password_repeat')->passwordInput(['maxlength' => 200]) ?>
        <?php
    }
    ?>
    <?= $form->field($model, 'address')->textInput(['maxlength' => 200]) ?>
    <?= $form->field($model, 'city')->textInput(['maxlength' => 200]) ?>
    <?= $form->field($model, 'state')->textInput(['maxlength' => 200]) ?>
    <?= $form->field($model, 'postalCode')->textInput(['maxlength' => 200]) ?>
    <?= $form->field($model, 'country_id')->dropDownList(\yii\helpers\ArrayHelper::map(\app\models\Country::find()->asArray()->all(), 'id', 'title'),  array('prompt'=>'Choose')) ?>
    <?= $form->field($model, 'phone')->textInput(['maxlength' => 200]) ?>
    <?php
    if(Yii::$app->user->can('adminPermission'))
    {
        ?>
        <?= $form->field($model, 'role')->dropDownList(\app\models\User::$roleValues,  array('prompt'=>'Choose')) ?>
        <?= $form->field($model, 'enable')->checkbox([], false) ?>
        <?php
    }
    ?>
    <div class="form-group">
        <div class="">
            <?php
            if($model->id)
                $text=Yii::t('app', 'Update');
            else
            {
                if(Yii::$app->user->identity->role==\app\models\User::ROLE_ADMIN)
                    $text=Yii::t('app', 'Add user');
                else
                    $text=Yii::t('app', 'Sign up');
            }
            echo Html::submitButton($text , ['class' => 'btn btn-primary', 'name' => 'login-button']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
