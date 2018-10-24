<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\User */
/* @var $form yii\widgets\ActiveForm */

$this->title='Change password';

$this->params['breadcrumbs'][] = ['label' => 'My profile', 'url' => ['profile']];
$this->params['breadcrumbs'][] = ['label' => 'Edit my profile', 'url' => ['profile-edit']];
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="user-form">

    <?php $form = ActiveForm::begin(
        [
            'enableAjaxValidation'=>true,
            'options' => ['class' => 'form-horizontal'],
            'fieldConfig' => [
                'template' => "{label}\n<div class=\"col-md-6\">{input}</div>\n<div class=\"col-md-3\">{error}</div>",
                'labelOptions' => ['class' => 'col-md-3 control-label'],
            ],
        ]
    ); ?>



    <?= $form->field($model, 'password_old')->passwordInput(['maxlength' => 200]) ?>
    <?= $form->field($model, 'password_new')->passwordInput(['maxlength' => 200]) ?>
    <?= $form->field($model, 'password_new_repeat')->passwordInput(['maxlength' => 200]) ?>


    <div class="form-group">
        <div class="col-lg-offset-3 col-lg-5">
            <?= Html::submitButton('Change password', ['class' => 'btn btn-primary', 'name' => 'login-button']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
