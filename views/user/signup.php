<?php

use yii\helpers\Html;
use yii\bootstrap\Alert;


/* @var $this yii\web\View */
/* @var $model app\models\User */

$this->title = Yii::t('app', 'Sign up');

$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-create">

    <?php
    if(Yii::$app->session->hasFlash('success'))
        echo Alert::widget([
            'options' => [
                'class' => 'alert-success',
            ],
            'body' => Yii::$app->session->getFlash('success'),
        ]);
    else
    {
        ?>
        <?= $this->render('_form_signup', [
            'model' => $model,
            'create' => true,
        ]) ?>
        <?php
    }
    ?>

</div>
