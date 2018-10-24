<?php
$this->title='Personal Precious Metal Manager';
$this->beginContent('@app/views/layouts/base.php');
?>
    <section class="body-sign" style="position: relative" >
        <div class="center-sign" style="
        text-align: center;
        position: absolute;
        top: 15%;
        ">
                <a href="<?=Yii::$app->homeUrl;?>" class="logo" >
                    <img src="<?=Yii::$app->request->baseUrl;?>/static/images/large-logo.png" style="width: 100%"  />
                </a>
                <div style="margin-top: 5%;">
                    <?=$content;?>
                </div>
        </div>
    </section>
<?php  $this->endContent(); ?>
<a href="http://www.beyondsecurity.com/vulnerability-scanner-verification/stakr.io"><img src="https://seal.beyondsecurity.com/verification-images/stakr.io/vulnerability-scanner-2.gif" alt="Website Security Test" border="0" /></a>
