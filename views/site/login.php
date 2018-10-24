<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */

$this->title = 'Login';
$this->params['breadcrumbs'][] = $this->title;
?>


    <?php $form = ActiveForm::begin([
        'id' => 'login-form',
        'fieldConfig' => ['options' => ['class' => 'form-group mb-3']]
    ]); ?>

    <?= $form->field($model, 'username', ['template'=>'
    {label}
    <div class="input-group">
									{input}
									<span class="input-group-append">
										<span class="input-group-text">
											<i class="fas fa-user"></i>
										</span>
									</span>
								</div>
								{error}
    '])->textInput(['class'=>'form-control form-control-lg']) ?>

    <?= $form->field($model, 'password', ['template'=>'
                                <div class="clearfix">
									{label}
									<a class="float-right" href="javascript:void(0);" id="resetButton" >Lost Password?</a>
								</div>
								<div class="input-group">
									{input}
									<span class="input-group-append">
										<span class="input-group-text">
											<i class="fas fa-lock"></i>
										</span>
									</span>
								</div>
								{error}
								',])->passwordInput(['class'=>'input-lg form-control',]) ?>
	<div class="row">
		<div class="col-sm-8">
		    <?= $form->field($model, 'rememberMe', ['template' => "     <div class=\"checkbox-custom checkbox-default\" >
		                                                                    {input}{label}
		                                                                </div>\n
		                                                                {error}",
		    ])->checkbox(['class'=>'input-sm',], false) ?>
		</div>	
	    <div class="col-sm-4 text-right">
	            <?= Html::submitButton('Login', ['class' => 'btn btn-primary', 'name' => 'login-button']) ?>
	            <?php //echo Html::button('Sign Up', ['class' => 'btn btn-success', 'id' => 'signButton']) ?>
	    </div>
	</div>

    <?php ActiveForm::end(); ?>

