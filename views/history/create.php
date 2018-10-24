<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\History */

$this->title = 'Create metal value history';
$this->params['breadcrumbs'][] = ['label' => 'Metal value histories', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="history-create">



    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
