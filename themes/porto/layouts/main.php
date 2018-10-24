<?php
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;
use yii\web\View;
use yii\web\JqueryAsset;
use yii\bootstrap\BootstrapAsset;
use yii\helpers\Url;

$this->beginContent('@app/views/layouts/base.php');
?>
<section class="body">
    <!-- start: header -->
    <header class="header">
        <div class="logo-container">
            <a href="<?=Yii::$app->homeUrl;?>" class="logo">
                <img src="<?=$this->theme->baseUrl;?>/assets/images/logo.png" height="35" alt="Porto Admin" />
            </a>
            <div class="d-block d-sm-none toggle-sidebar-left" data-toggle-class="sidebar-left-opened" data-target="html" data-fire-event="sidebar-left-opened">
                <i class="fa fa-bars" aria-label="Toggle sidebar"></i>
            </div>
        </div>
        <!-- start: search & user box -->
        <div class="header-right">
            <span class="separator"></span>
            <!--
            <form action="pages-search-results.html" class="search nav-form">
                <div class="input-group input-search">
                    <input type="text" class="form-control" name="q" id="q" placeholder="Search...">
                        <span class="input-group-btn">
                            <button class="btn btn-default" type="submit"><i class="fa fa-search"></i></button>
                        </span>
                </div>
            </form>
            <!--
            <span class="separator"></span>
            <ul class="notifications">
                <li>
                    <a href="#" class="dropdown-toggle notification-icon" data-toggle="dropdown">
                        <i class="fa fa-bell"></i>
                        <span class="badge">3</span>
                    </a>

                    <div class="dropdown-menu notification-menu">
                        <div class="notification-title">
                            <span class="pull-right label label-default">3</span>
                            Alerts
                        </div>

                        <div class="content">
                            <ul>
                                <li>
                                    <a href="#" class="clearfix">
                                        <div class="image">
                                            <i class="fa fa-thumbs-down bg-danger"></i>
                                        </div>
                                        <span class="title">Server is Down!</span>
                                        <span class="message">Just now</span>
                                    </a>
                                </li>

                            </ul>

                            <hr />

                            <div class="text-right">
                                <a href="#" class="view-more">View All</a>
                            </div>
                        </div>
                    </div>
                </li>
            </ul>
            <span class="separator"></span>-->
            <?php
            if(Yii::$app->user->id)
            {
                ?>
                <div id="userbox" class="userbox">

                    <a href="#" data-toggle="dropdown">
                        <div class="profile-info" data-lock-name="<?=Yii::$app->user->identity->name;?>" data-lock-email="<?=Yii::$app->user->identity->email;?>">
                            <span class="name" ><?=Yii::$app->user->identity->name;?></span>
                            <?php
                            if(Yii::$app->user->identity->role==\app\models\User::ROLE_ADMIN)
                            {
                                ?>
                                <span class="role">Administrator</span>
                                <?php
                            }
                            ?>
                        </div>
                        <i class="fa custom-caret"></i>
                    </a>
                    <div class="dropdown-menu">
                        <ul class="list-unstyled">
                            <li class="divider"></li>
                            <?php
                            $profileLinks=[
                                ['label' => 'My profile', 'url' => ['user/profile']],
                                ['label' => 'Logout (' . Yii::$app->user->identity->name . ')',
                                    'url' => ['/site/logout'],
                                    'linkOptions' => ['data-method' => 'post']],
                            ];
                            foreach ($profileLinks as $link) {
                                ?>
                                <li>
                                    <a role="menuitem" tabindex="-1" href="<?=Url::to($link['url']);?>"><?=$link['label'];?></a>
                                </li>
                            <?php
                            }
                            ?>
                        </ul>
                    </div>
                    <div style="clear: both"></div>
                </div>
                <?php
            }
            else
                echo Html::a('Login', ['site/login']);
            ?>

        </div>
        <!-- end: search & user box -->
    </header>
    <!-- end: header -->
    <div class="inner-wrapper">
        <!-- start: sidebar -->
        <aside id="sidebar-left" class="sidebar-left">
            <div class="sidebar-header">
                <div class="sidebar-title">
                    Personal Metal Manager
                </div>
                <div class="sidebar-toggle hidden-xs" data-toggle-class="sidebar-left-collapsed" data-target="html" data-fire-event="sidebar-left-toggle">
                    <i class="fa fa-bars" aria-label="Toggle sidebar"></i>
                </div>
            </div>
            <?php
            $controller=Yii::$app->controller->id;
            $action=Yii::$app->controller->action->id;
            ?>
            <div class="nano">
                <div class="nano-content">
                    <nav id="menu" class="nav-main" role="navigation">
                        <ul class="nav nav-main">
                            <?php
                            if(Yii::$app->user->identity && Yii::$app->user->identity->role==\app\models\User::ROLE_USER)
                            {
                                ?>
                                <li class="<?=in_array($controller, ['site']) ? 'nav-active':'';?>">
                                    <a href="<?=Yii::$app->homeUrl;?>" class="pjaxLink">
                                        <i class="fa fa-camera" aria-hidden="true"></i>
                                        <span>Stack Overview</span>
                                    </a>
                                </li>
                                <?php
                            }
                            ?>


                            <li class="nav-parent <?=in_array($controller, ['metal-stack'])?'nav-active nav-expanded':'';  ?>
                            ">
                                <a>
                                    <i class="fas fa-dollar-sign" aria-hidden="true" ></i>
                                    <span><?=Yii::$app->user->can('adminPermission') ? 'All stacks':'My Stack';?></span>
                                </a>
                                <ul class="nav nav-children">
                                    <li class="<?=$controller=='my-stack'&&$action=='index' ?'nav-active':'';?>">
                                        <?=Html::a('<i class="fas fa-dollar-sign" aria-hidden="true" style="font-size: inherit; margin: -3px 3px 0 -3px" ></i><span>Overall Stack</span>', ['metal-stack/index'], ['data-pjax' => 'true', 'class'=>'pjaxLink',]);?>
                                    </li>
                                    <li class="<?=$controller=='my-stack'&&$action=='sold-index' ?'nav-active':'';?>">
                                        <?=Html::a('<i class="fas fa-dollar-sign" aria-hidden="true" style="font-size: inherit; margin: -3px 3px 0 -3px" ></i><span>Sold Items</span>',
                                            ['metal-stack/sold-index'], ['data-pjax' => 'true', 'class'=>'pjaxLink',]);?>
                                    </li>
                                    <li class="<?=$controller=='my-stack'&&$action=='trade-index' ?'nav-active':'';?>">
                                        <?=Html::a('<i class="fas fa-dollar-sign" aria-hidden="true" style="font-size: inherit; margin: -3px 3px 0 -3px" ></i><span>Traded Items</span>',
                                            ['metal-stack/trade-index'], ['data-pjax' => 'true', 'class'=>'pjaxLink',]);?>
                                    </li>
                                    <li class="<?=$controller=='my-stack'&&$action=='lost-index' ?'nav-active':'';?>">
                                        <?=Html::a('<i class="fas fa-dollar-sign" aria-hidden="true" style="font-size: inherit; margin: -3px 3px 0 -3px" ></i><span>Lost Items</span>',
                                            ['metal-stack/lost-index'], ['data-pjax' => 'true', 'class'=>'pjaxLink',]);?>
                                    </li>
                                </ul>
                            </li>
                            <li class="nav-parent <?=in_array($controller, ['user', 'metal-type', 'metal-grade', 'metal-vendors',
                                'metal-shape', 'metal-mint'])?'nav-active nav-expanded':'';  ?>
                            ">
                                <a class="nav-link">
                                    <i class="fas fa-cog" aria-hidden="true" ></i>
                                    <span>Settings</span>
                                </a>
                                <ul class="nav nav-children">
                                    <?php
                                    $manageLinks=[
                                        ['icon'=>'fas fa-user',  'label' => 'Users', 'url' => ['user/index'], 'controllerName'=>'user',],
                                        ['icon'=>'fas fa-tag','label' => 'Types', 'url' => ['metal-type/index'], 'controllerName'=>'metal-type',],
                                        ['icon'=>'fas fa-eye','label' => 'Grades', 'url' => ['metal-grade/index'], 'controllerName'=>'metal-grade',],
                                        ['icon'=>'fas fa-road','label' => 'Vendors', 'url' => ['metal-vendors/index'], 'controllerName'=>'metal-vendors',],
                                        ['icon'=>'fas fa-stop','label' => 'Shapes', 'url' => ['metal-shape/index'], 'controllerName'=>'metal-shape',],
                                        ['icon'=>'fas fa-globe','label' => 'Mints', 'url' => ['metal-mint/index'], 'controllerName'=>'metal-mint',],
                                    ];
                                    if(Yii::$app->user->identity->role!=\app\models\User::ROLE_ADMIN)
                                        unset($manageLinks[0]);
                                    foreach ($manageLinks as $link)
                                    {
                                        ?>
                                        <li class="<?=$controller==$link['controllerName']?'nav-active':'';?>">
                                            <a href="<?=Url::to($link['url']);?>" class="pjaxLink">
                                                <i class="<?=$link['icon'];?>" style="font-size: inherit; margin: -3px 3px 0 -3px" ></i>
                                                <?=$link['label'];?></a>
                                        </li>
                                        <?php
                                    }
                                    ?>
                                </ul>
                            </li>
                            <li class="<?=in_array($controller, ['history']) ? 'nav-active':'';?>">
                                <a href="<?=Url::to(['history/index']);?>" class="pjaxLink">
                                    <i class="fas fa-history" aria-hidden="true"></i>
                                    <span>Spot Price History</span>
                                </a>
                            </li>
                            <li class="<?=in_array($controller, ['log']) ? 'nav-active':'';?>">
                                <a href="<?=Url::to(['log/index']);?>" class="pjaxLink">
                                    <i class="fas fa-outdent" aria-hidden="true"></i>
                                    <span>Logs</span>
                                </a>
                            </li>
                        </ul>
                    </nav>
                    <hr class="separator" />
                </div>
            </div>
            <div>
               
                <div id="sslsSiteSeal">
                    seal by <a href="https://ssls.com/">SSLs.com</a>
                </div>
            </div>
        </aside>
        <!-- end: sidebar -->
        <section role="main" class="content-body">
            <header class="page-header">
                <h2><?=$this->title;?></h2>
                <div class="right-wrapper pull-right">
                    <?= Breadcrumbs::widget([
                        'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
                        'options'=>['class'=>'breadcrumbs',],
                        'tag'=>'ol',
                        'encodeLabels'=>false,
                        'homeLink'=>[
                            'label'=>'<i class="fa fa-home"></i>',
                            'url'=>Yii::$app->homeUrl,
                        ],
                        'activeItemTemplate'=>"<li class=\"active\"><span>{link}</span></li>\n",
                    ]) ?>
                    &nbsp;
                    &nbsp;
                    &nbsp;
                </div>
            </header>
            <!--central content-->
            
                <?php
                Yii::$app->getRequest()->getHeaders()->set('X-Pjax', false);
                \yii\widgets\Pjax::begin([
                    'clientOptions'=>['fragment'=>'#pjaxContent',],
                    'timeout'=>5000,
                    'id'=>'pjaxContent',
                    'enablePushState'=>true,
                    'linkSelector'=>'a.pjaxLink',
                ]);
                ?>
                <div class="row">
                <div class="col-lg-12">
                    <section class="card">
                        <header class="card-header">
                            <div class="panel-actions">
                                <!--<a href="#" class="fa fa-caret-down"></a>
                                <a href="#" class="fa fa-times"></a>-->
                            </div>
                            <h2 class="card-title"><?=$this->title;?></h2>
                        </header>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <?php
                                    echo $content;
                                    ?>
                                    <script>
                                        <?php
                                        if(isset($_GET['_pjax']) && Yii::$app->request->isAjax)
                                            if($this->js)
                                               foreach ($this->js as $jsArray)
                                                   foreach ($jsArray as $key=>$js)
                                                       echo $js."\n";
                                        ?>
                                    </script>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
                </div>
                <?php
                \yii\widgets\Pjax::end();
                ?>
            
            <!-- end: page -->
        </section>
    </div>
</section>
<?php  $this->endContent(); ?>
<?php
/*$this->beginBlock('modalContainer', true);
$this->endBlock();*/
?>