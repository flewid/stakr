<?php
use yii\helpers\Html;
$this->title = Yii::t('app', 'Upload stacks');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Metal Stacks'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="product-form">
    <h1><?= Html::encode($this->title) ?></h1>


    <?php
    if(Yii::$app->session->hasFlash('error'))
        echo \yii\bootstrap\Alert::widget([
            'options' => [
                'class' => 'alert-danger',
            ],
            'body' => Yii::$app->session->getFlash('error'),
        ]);
    if(Yii::$app->session->hasFlash('warning'))
        echo \yii\bootstrap\Alert::widget([
            'options' => [
                'class' => 'alert-warning',
            ],
            'body' => Yii::$app->session->getFlash('warning'),
        ]);

    if(Yii::$app->session->hasFlash('success'))
        echo \yii\bootstrap\Alert::widget([
            'options' => [
                'class' => 'alert-success',
            ],
            'body' => Yii::$app->session->getFlash('success'),
        ]);
    else
    {
        ?>
        <form action="" method="post" enctype="multipart/form-data">
            <input type="hidden" name="_csrf" value="<?=Yii::$app->request->getCsrfToken()?>" />
            <div class="form-group field-product-name required">
                <label for="product-name" class="control-label">File</label>
                <input type="file" name="file"   >
                <div class="help-block"></div>
            </div>
            <div class="form-group field-product-name">
                <div class="checkbox-custom checkbox-primary" >
                    <input id="overwrite" type="checkbox" name="overwrite"   >
                    <label for="overwrite">Overwrite exist items</label>
                </div>
                <div class="help-block"></div>
            </div>
            <input type="submit" class="btn btn-success" value="Upload">
        </form>
    <?php
    }
    ?>

</div>
