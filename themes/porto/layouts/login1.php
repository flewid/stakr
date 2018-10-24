<?php
$this->beginContent('@app/views/layouts/base.php');
?>
    <section class="body-sign">
        <div class="center-sign">
            <a href="<?=Yii::$app->homeUrl;?>" class="logo pull-left">
                <img src="<?=$this->theme->baseUrl;?>/assets/images/logo.png" height="54" alt="Porto Admin" />
            </a>

            <div class="panel panel-sign">
                <div class="panel-title-sign mt-xl text-right">
                    <h2 class="title text-uppercase text-bold m-none"><i class="fa fa-user mr-xs"></i> <?=$this->title;?></h2>
                </div>
                <div class="panel-body">
                    <?=$content;?>
                </div>
            </div>

            <p class="text-center text-muted mt-md mb-md">&copy; Copyright 2018. All Rights Reserved.</p>
        </div>
    </section>
<?php  $this->endContent(); ?>
