<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\MetalMint */

$this->title = Yii::t('app', 'Add {modelClass}', [
    'modelClass' => 'Mint',
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Metal Mints'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="metal-mint-create">



    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
