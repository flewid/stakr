<?php
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
$this->title = 'Settings';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-about">

    <ul class="nav flex-column">
        <?php
        $manageLinks=[
            ['icon'=>'fa fa-user',  'label' => 'Users', 'url' => ['user/index'], 'controllerName'=>'user',],
            ['icon'=>'fa fa-tags','label' => 'Types', 'url' => ['metal-type/index'], 'controllerName'=>'metal-type',],
            ['icon'=>'fa fa-eye','label' => 'Grades', 'url' => ['metal-grade/index'], 'controllerName'=>'metal-grade',],
            ['icon'=>'fa fa-road','label' => 'Vendors', 'url' => ['metal-vendors/index'], 'controllerName'=>'metal-vendors',],
            ['icon'=>'fa fa-stop','label' => 'Shapes', 'url' => ['metal-shape/index'], 'controllerName'=>'metal-shape',],
            ['icon'=>'fa fa-globe','label' => 'Mints', 'url' => ['metal-mint/index'], 'controllerName'=>'metal-mint',],
        ];
        if(Yii::$app->user->identity->role!=\app\models\User::ROLE_ADMIN)
            unset($manageLinks[0]);
        foreach ($manageLinks as $link)
        {
            ?>
            <li class="nav-item">
                <a class="nav-link <?=$controller==$link['controllerName']?'active':'';?>" href="<?=Url::to($link['url']);?>" class="pjaxLink">
                    <i class="<?=$link['icon'];?>" style="font-size: inherit; margin: -3px 3px 0 -3px" ></i>
                    <?=$link['label'];?></a>
            </li>
        <?php
        }
        ?>
    </ul>

</div>
