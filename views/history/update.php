<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\History */

$this->title = 'Update metal value history: ' . ' ' . $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Metal value histories', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->ID]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="history-update">



    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
