<tr data-key="<?=$model->ID;?>" style="background: #f9f9f9; display: none;" class="kidsFrom-<?=$parent_id;?>" >
    <td style="padding-left: 50px;"><?=$model->title;?></td>
    <td><?=$model->ID;?></td>
    <td><?=Yii::$app->formatter->asBoolean($model->roll);?></td>
    <td><?=Yii::$app->formatter->asNtext(null);?></td>
    <td><?=$model->metalDescription;?></td>
    <td><?=$model->type->metalDescription;?></td>
    <td><?=$model->vendor->vendorName;?></td>
    <td><?=$model->shape->shape;?></td>
    <td><?=$model->grade->metalGrade;?></td>
    <td><?=$model->author->name;?></td>
    <td>
        <a data-pjax="0" title="View" href="<?=\yii\helpers\Url::to(['metal-stack/view', 'id'=>$model->ID,]);?>">
            <i class="far fa-eye"></i></a>
        <?php
        if(Yii::$app->user->can('updateOwnModel', ['model' => $model]))
        {
            ?>
            <a data-pjax="0" title="Update" href="<?=\yii\helpers\Url::to(['metal-stack/update', 'id'=>$model->ID,]);?>">
                <i class="fas fa-pencil-alt"></i></a>
            <?php
        }
        if(Yii::$app->user->can('updateOwnModel', ['model' => $model]))
        {
            ?>
            <a data-pjax="0" data-method="post"
               data-confirm="Are you sure you want to delete this item?" title="Delete"
               href="<?=\yii\helpers\Url::to(['metal-stack/delete', 'id'=>$model->ID,]);?>">
                <i class="far fa-trash-alt"></i></a>
            <?php
        }
        ?>
    </td>
</tr>