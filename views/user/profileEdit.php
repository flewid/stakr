<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\User */

$this->title = 'Edit my profile';

$this->params['breadcrumbs'][] = ['label' => 'My profile', 'url' => ['profile']];
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="user-view">
    <p class="float-right pClassInView" >
        <?= Html::a(Yii::t('app', 'Change email'), ['change-email'], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('app', 'Change password'), ['change-password'], ['class' => 'btn btn-primary']) ?>
    </p>
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
