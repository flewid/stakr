<?php
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;
use app\assets\BootboxAsset;


/* @var $this \yii\web\View */
/* @var $content string */

AppAsset::register($this);
BootboxAsset::register($this);
BootboxAsset::overrideSystemConfirm();
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="<?=Yii::$app->request->baseUrl;?>/static/images/favicon.ico" />
    <?php
    use yii\web\View;
    use yii\web\JqueryAsset;
    $this->registerJs("
                         var baseUrl = '".Yii::$app->request->baseUrl."';
                         var regUrl = '".\yii\helpers\Url::to(['user/signup'])."';
                         var resetUrl = '".\yii\helpers\Url::to(['user/reset'])."';
                      ", View::POS_HEAD);
    $this->registerJsFile(Yii::$app->request->baseUrl."/static/js/scripts.js", ['depends' => [JqueryAsset::className()]]);
    ?>
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>

<?php $this->beginBody() ?>
    <div class="wrap">
        <?php
            NavBar::begin([
                'brandLabel' => Yii::$app->name,
                'brandUrl' => Yii::$app->homeUrl,
                'options' => [
                    'class' => 'navbar-inverse navbar-fixed-top',
                ],
            ]);

            $items=[
                ['label' => 'Home', 'url' => ['/site/index']],
            ];
            $items[]=['label' => 'Contact', 'url' => ['/site/contact']];
            $items[]=['label' => 'Metal Stacks', 'url' => ['metal-stack/index']];
            if(Yii::$app->user->identity && Yii::$app->user->identity->role == app\models\User::ROLE_ADMIN)
            {
                $items[]=['label' => 'Manage1', 'url' => '',
                    'items'=>[
                        ['label' => 'Users', 'url' => ['user/index']],
                        ['label' => 'Metal Types', 'url' => ['metal-type/index']],
                        ['label' => 'Grades', 'url' => ['metal-grade/index']],
                        ['label' => 'Vendors', 'url' => ['metal-vendors/index']],
                        ['label' => 'Shapes', 'url' => ['metal-shape/index']],
                        ['label' => 'Metal Mints', 'url' => ['metal-mint/index']],
                    ]];
            }
            if(Yii::$app->user->identity && Yii::$app->user->identity->id)
            {
                $items[]=['label' => Yii::$app->user->identity->name, 'url' => '',
                    'items'=>[
                        ['label' => 'My Profile', 'url' => ['user/profile']],
                        ['label' => 'Change email', 'url' => ['user/change-email']],
                        ['label' => 'Change password', 'url' => ['user/change-password']],
                        ['label' => 'Logout (' . Yii::$app->user->identity->name . ')',
                            'url' => ['/site/logout'],
                            'linkOptions' => ['data-method' => 'post']],
                    ]];
            }
            else
                $items[]=['label' => 'Login', 'url' => ['/site/login']];
        //Yii::$app->user->isGuest

            echo Nav::widget([
                'options' => ['class' => 'navbar-nav navbar-right'],
                'items' => $items,
            ]);
            NavBar::end();
        ?>

        <div class="container">
            <?= Breadcrumbs::widget([
                'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
            ]) ?>
            <?php
            /*
            $user = \app\models\User::find()
                ->where(['id' => 1])
                ->one();
            */
            use \app\models\User;
            $user=User::find()->one();
            //echo \app\models\User::find()
            //echo \yii\Aida::$qwe;
            ?>
            <?= $content ?>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <p class="float-left">&copy; My Company <?= date('Y') ?></p>
            <p class="float-right"><?= Yii::powered() ?></p>
        </div>
    </footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
