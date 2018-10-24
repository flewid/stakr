<?php
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;
use app\assets\BootboxAsset;
use yii\web\View;
use yii\web\JqueryAsset;
use yii\bootstrap\BootstrapAsset;

/* @var $this \yii\web\View */
/* @var $content string */

?>
<?php $this->beginPage() ?>
<!doctype html>
<html class="fixed">
<head>
    <?php
    $this->registerJs("
                         var baseUrl = '".Yii::$app->request->BaseUrl."';
                         var regUrl = '".\yii\helpers\Url::to(['user/signup'])."';
                         var resetUrl = '".\yii\helpers\Url::to(['user/reset'])."';
                      ", View::POS_HEAD);
    $this->registerJsFile(Yii::$app->request->baseUrl."/static/js/scripts.js", ['depends' => [JqueryAsset::className()]]);
    $this->registerJsFile(Yii::$app->request->baseUrl."/static/plugins/notify.js", ['depends' => [JqueryAsset::className()]]);
    //$this->registerJsFile("@web/assetsUi/vendor/jquery-browser-mobile/jquery.browser.mobile.js", ['depends' => [JqueryAsset::className()]]);
    $this->registerJsFile($this->theme->baseUrl."/assets/vendor/jquery-browser-mobile/jquery.browser.mobile.js", ['depends' => [JqueryAsset::className()]]);
    $this->registerJsFile($this->theme->baseUrl."/assets/vendor/bootstrap/js/bootstrap.js", ['depends' => [JqueryAsset::className()]]);
    $this->registerJsFile($this->theme->baseUrl."/assets/vendor/nanoscroller/nanoscroller.js", ['depends' => [JqueryAsset::className()]]);
    $this->registerJsFile($this->theme->baseUrl."/assets/vendor/bootstrap-datepicker/js/bootstrap-datepicker.js", ['depends' => [JqueryAsset::className()]]);
    $this->registerJsFile($this->theme->baseUrl."/assets/vendor/magnific-popup/magnific-popup.js", ['depends' => [JqueryAsset::className()]]);
    $this->registerJsFile($this->theme->baseUrl."/assets/vendor/jquery-placeholder/jquery.placeholder.js", ['depends' => [JqueryAsset::className()]]);

    $this->registerJsFile($this->theme->baseUrl."/assets/vendor/jquery-autosize/jquery.autosize.js", ['depends' => [JqueryAsset::className()]]);
    $this->registerJsFile($this->theme->baseUrl."/assets/vendor/bootstrap-fileupload/bootstrap-fileupload.min.js", ['depends' => [JqueryAsset::className()]]);

    $this->registerJsFile($this->theme->baseUrl."/assets/javascripts/theme.js", ['depends' => [JqueryAsset::className()]]);
    $this->registerJsFile($this->theme->baseUrl."/assets/javascripts/theme.custom.js", ['depends' => [JqueryAsset::className()]]);
    $this->registerJsFile($this->theme->baseUrl."/assets/javascripts/theme.init.js", ['depends' => [JqueryAsset::className()]]);

    $this->registerCssFile("http://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800|Shadows+Into+Light");
    //$this->registerCssFile($this->theme->baseUrl."/assets/vendor/bootstrap/css/bootstrap.css");
    $this->registerCssFile($this->theme->baseUrl."/assets/vendor/font-awesome/css/font-awesome.css", ['depends' => [BootstrapAsset::className()]]);
    $this->registerCssFile($this->theme->baseUrl."/assets/vendor/magnific-popup/magnific-popup.css",['depends' => [BootstrapAsset::className()]]);
    $this->registerCssFile($this->theme->baseUrl."/assets/vendor/bootstrap-datepicker/css/datepicker3.css",['depends' => [BootstrapAsset::className()]]);
    $this->registerCssFile($this->theme->baseUrl."/assets/vendor/bootstrap-fileupload/bootstrap-fileupload.min.css",['depends' => [BootstrapAsset::className()]]);
    $this->registerCssFile($this->theme->baseUrl."/assets/stylesheets/theme.css",['depends' => [BootstrapAsset::className()]]);
    $this->registerCssFile($this->theme->baseUrl."/assets/stylesheets/skins/default.css",['depends' => [BootstrapAsset::className()]]);
    $this->registerCssFile($this->theme->baseUrl."/assets/stylesheets/theme-custom.css",['depends' => [BootstrapAsset::className()]]);
    $this->registerJsFile($this->theme->baseUrl."/assets/vendor/modernizr/modernizr.js",['depends' => [BootstrapAsset::className()], 'position'=>View::POS_HEAD,] );
    AppAsset::register($this);
    BootboxAsset::register($this);
    BootboxAsset::overrideSystemConfirm();
    ?>
    <!-- Web Fonts  -->
    <meta charset="UTF-8">

    <link rel="apple-touch-icon" sizes="57x57" href="<?=Yii::$app->request->baseUrl;?>/static/images/favicon/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="<?=Yii::$app->request->baseUrl;?>/static/images/favicon/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="<?=Yii::$app->request->baseUrl;?>/static/images/favicon/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="<?=Yii::$app->request->baseUrl;?>/static/images/favicon/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="<?=Yii::$app->request->baseUrl;?>/static/images/favicon/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="<?=Yii::$app->request->baseUrl;?>/static/images/favicon/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="<?=Yii::$app->request->baseUrl;?>/static/images/favicon/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="<?=Yii::$app->request->baseUrl;?>/static/images/favicon/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="<?=Yii::$app->request->baseUrl;?>/static/images/favicon/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192"  href="<?=Yii::$app->request->baseUrl;?>/static/images/favicon/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?=Yii::$app->request->baseUrl;?>/static/images/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="<?=Yii::$app->request->baseUrl;?>/static/images/favicon/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?=Yii::$app->request->baseUrl;?>/static/images/favicon/favicon-16x16.png">
    <link rel="manifest" href="<?=Yii::$app->request->baseUrl;?>/static/images/favicon/manifest.json">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="<?=Yii::$app->request->baseUrl;?>/static/images/favicon/ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff">



    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>

    <meta name="keywords" content="HTML5 Admin Template" />
    <meta name="description" content="Porto Admin - Responsive HTML5 Template">
    <meta name="author" content="okler.net">
    <!-- Mobile Metas -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
</head>
<body>
<?php $this->beginBody() ?>
    <?=$content;?>
    <script>
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
        (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
        m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
    ga('create', 'UA-3657386-41', 'auto');
    ga('require', 'displayfeatures');
    ga('require', 'linkid', 'linkid.js');
    ga('send', 'pageview');
</script>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>