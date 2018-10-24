<?php
$this->beginContent('@app/views/layouts/base.php');
?>
    <section class="body-sign">
        <div class="center-sign">
            <a href="<?=Yii::$app->homeUrl;?>" class="logo pull-left">
                <img src="<?=$this->theme->baseUrl;?>/assets/images/logo.png" height="54" alt="Porto Admin" />
            </a>

            <div class="panel card-sign">
                <div class="card-title-sign mt-3 text-right">
                    <h2 class="title text-uppercase font-weight-bold m-0"><i class="fas fa-user mr-1"></i> <?=$this->title;?></h2>
                </div>
                <div class="card-body">
                    <?=$content;?>
                </div>
            </div>

            <p class="text-center text-muted mt-3 mb-3">&copy; Copyright 2018. All Rights Reserved.</p>
        </div>
    </section>
<?php  $this->endContent(); ?>
