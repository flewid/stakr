<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\MetalVendors */

$this->title = Yii::t('app', 'Add vendor');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Vendors'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="metal-vendors-create">



    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
