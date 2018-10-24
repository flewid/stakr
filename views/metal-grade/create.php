<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\MetalGrade */

$this->title = Yii::t('app', 'Add {modelClass}', [
    'modelClass' => 'Grade',
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Grades'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="metal-grade-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
