<?php
use yii\Aida;
use yii\bootstrap\Alert;



/* @var $this yii\web\View */
$this->title = Yii::$app->name;
?>
<div class="site-index">

    <div class="body-content">

        <div class="row">
            <div class="col-lg-12">
                <p>
                    <?php
                    if(Yii::$app->session->hasFlash('success'))
                        echo Alert::widget([
                            'options' => [
                                'class' => 'alert-success',
                            ],
                            'body' => Yii::$app->session->getFlash('success'),
                        ]);
                    ?>
                </p>

            </div>

        </div>

    </div>
</div>
