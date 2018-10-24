<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\MetalStack */

$this->title = Yii::t('app', 'Add To Stack');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Metal Stacks'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="metal-stack-create">



    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
