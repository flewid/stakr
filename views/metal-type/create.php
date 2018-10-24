<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\MetalType */

$this->title = Yii::t('app', 'Add {modelClass}', [
    'modelClass' => 'Type',
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Metal Types'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="metal-type-create">



    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
