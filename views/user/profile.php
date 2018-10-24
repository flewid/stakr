<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\User */

$this->title = 'My profile';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-view">

    <p class="float-right pClassInView" >
        <?= Html::a(Yii::t('app', 'Edit my profile'), ['profile-edit'], ['class' => 'btn btn-primary']) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'name',
            'email:email',
            'address',
            'city',
            'state',
            'postalCode',
            [
                'attribute'=>'country_id',
                'value'=>$model->country->title,
            ],
            'phone',
            'offline_mode:boolean',
            [
                'attribute'=>'role',
                'value'=>$model->roleLabel,
                'visible'=>Yii::$app->user->can('adminPermission'),
            ],
            'enable:boolean',
        ],
    ]) ?>

</div>
