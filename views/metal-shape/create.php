<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\MetalShape */

$this->title = Yii::t('app', 'Add shape');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Shapes'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="metal-shape-create">



    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
