<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */

$this->title = $name;
?>
<div class="site-error">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?php  echo nl2br(Html::encode($message)) ?> <br/>
        <a href="javascript:window.history.back();">Go back</a>
    </p>


</div>
