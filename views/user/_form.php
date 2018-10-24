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
            'options' => ['class' => 'form-horizontal'],
            'fieldConfig' => [
                'template' => "{label}\n<div class=\"col-md-6\">{input}</div>\n<div class=\"col-md-3\">{error}</div>",
                'labelOptions' => ['class' => 'col-md-3 control-label'],
            ],
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
    <?= $form->field($model, 'offline_mode', ['template' => "{label}\n
                                                                <div class=\"col-md-6 checkbox-custom checkbox-primary\" style='margin: 5px 0 -5px 15px;'>
                                                                    {input}<label></label>
                                                                </div>\n
                                                                <div class=\"col-md-3\">{error}</div>",
    ])->checkbox([], false) ?>

    <?php
    if(Yii::$app->user->can('adminPermission'))
    {
        ?>
        <?= $form->field($model, 'role')->dropDownList(\app\models\User::$roleValues,  array('prompt'=>'Choose')) ?>
        <?= $form->field($model, 'enable', ['template' => "{label}\n
                                                                <div class=\"col-md-6 checkbox-custom checkbox-primary\" style='margin: 5px 0 -5px 15px;'>
                                                                    {input}<label></label>
                                                                </div>\n
                                                                <div class=\"col-md-3\">{error}</div>",
        ])->checkbox([], false) ?>
        <?php
    }
    ?>
    <div class="form-group">
        <div class="col-md-offset-3 col-md-6">
            <?php
            if($model->id)
                $text=Yii::t('app', 'Update');
            else
            {
                if(Yii::$app->user->can('adminPermission'))
                    $text=Yii::t('app', 'Add user');
                else
                    $text=Yii::t('app', 'Sign up');
            }
            echo Html::submitButton($text , ['class' => 'btn btn-primary', 'name' => 'login-button']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
