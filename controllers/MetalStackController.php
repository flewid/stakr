<?php

namespace app\controllers;

use app\models\Form;
use app\models\History;
use app\models\Log;
use app\models\MetalGrade;
use app\models\MetalMint;
use app\models\MetalShape;
use app\models\MetalType;
use app\models\MetalVendors;
use app\models\SoldForm;
use app\models\User;
use Yii;
use app\models\MetalStack;
use app\models\search\MetalStackSearch;
use app\components\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\ForbiddenHttpException;
use yii\helpers\Html;
use app\models\Country;
/**
 * MetalStackController implements the CRUD actions for MetalStack model.
 */
class MetalStackController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['create', 'view','index', 'lost-index','sold-index','trade-index','update-mode', 'download', 'truncate', 'readfile', 'upload', 'list'],
                        'allow' => true,
                        'roles' => [\app\models\User::ROLE_USER],
                    ],
                    [
                        'actions' => ['update', 'delete', 'down',],
                        'allow' => true,
                        'roles' => [\app\models\User::ROLE_USER],
                        'matchCallback' => function($rule, $action)
                            {
                                return \Yii::$app->user->can('updateOwnModel', ['model' => $this->findModel($_GET['id'])]);
                            }
                    ],
                    [
                        'actions' => ['lost', 'sold', 'restore'],
                        'allow' => true,
                        'roles' => [\app\models\User::ROLE_USER],
                        'matchCallback' => function($rule, $action)
                            {
                                return \Yii::$app->user->can('updateOwnModel', ['model' => $this->findModel($_GET['id'])]);
                            }
                    ],
                    [
                        'actions' => ['trade'],
                        'allow' => true,
                        'roles' => [\app\models\User::ROLE_USER],
                        'matchCallback' => function($rule, $action)
                            {
                                if(isset($_POST['Form'], $_POST['Form']['stack_id']))
                                    return \Yii::$app->user->can('updateOwnModel', ['model' => $this->findModel($_POST['Form']['stack_id'])]);
                                return false;
                            }
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Lists all MetalStack models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new MetalStackSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->mine()->notInRoll()->open();
        //$dataProvider->query->createCommand()->params;
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionList()
    {
        $searchModel = new MetalStackSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->mine()->notInRoll()->open()->andWhere(['!=', 'id', $_POST['trade_id']]);
        $dataProvider->pagination=false;
        //$options='<option value=""></option>';
        $options='';
        foreach ($dataProvider->models as $model)
            $options.=Html::tag('option', $model->title, ['value'=>$model->ID,]);
        if(!$options)
            $options='<option value=""></option>';
        return $options;
    }
    public function custom($type, $title)
    {
        $searchModel = new MetalStackSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->query->mine()->notInRoll();
        if($type=='sold')
            $dataProvider->query->sold();
        if($type=='trade'){
            $dataProvider->query->trade();
            $dataProvider->query->with('trade');//можно
        }
        if($type=='lost')
            $dataProvider->query->lost();
        return $this->render('index_custom', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'title' => $title,
        ]);
    }

    public function actionLostIndex()
    {
        return $this->custom('lost','Lost items');
    }
    public function actionSoldIndex()
    {
        return $this->custom('sold', 'Sold items');
    }
    public function actionTradeIndex()
    {
        return $this->custom('trade', 'Traded items');
    }

    public function actionLost($id)
    {
        $model=$this->findModel($id);
        $model->status = MetalStack::STATUS_LOST;
        $model->save();
        Log::create(['metal_id'=>$id]);
        return $this->redirect(['index']);
    }
    public function actionTrade()
    {
        $form = new Form();
        $this->performAjaxValidation($form);
        if($form->load($_POST) && $form->validate())
        {
            $model=$this->findModel($form->stack_id);
            $model->status = MetalStack::STATUS_TRADE;
            if($form->trade_radio=='stack'){
                $model->trade_id = $form->trade_id;
                $model->trade_text = '';
            }
            if($form->trade_radio=='other'){
                $model->trade_id = '';
                $model->trade_text = $form->trade_text;
            }
            $model->save();
            $tradeObject = $model->trade_id ? MetalStack::findOne($model->trade_id)->link: $model->trade_text;
            Log::create(['metal_id'=>$model->ID, 'description'=>Yii::$app->user->identity->link." traded the item {$model->link} to $tradeObject",]);
        }
        return $this->redirect(['index']);
    }
    public function actionSold($id)
    {
        $form = new SoldForm();
        $this->performAjaxValidation($form);
        if($form->load($_POST) && $form->validate())
        {
            $model=$this->findModel($form->stack_id);
            $model->status = MetalStack::STATUS_SOLD;
            $model->sold_price=$form->sold_price;
            $model->save();
            Log::create(['metal_id'=>$model->ID]);
        }
        return $this->redirect(['index']);
    }
    public function actionRestore($id)
    {
        $model=$this->findModel($id);
        $model->status = '';
        $model->trade_id = '';
        $model->trade_text = '';
        $oldStatus=$model->oldAttributes['status'];
        $model->save();
        Log::create(['metal_id'=>$model->ID]);
        return $this->redirect([strtolower($model->statusValues[$oldStatus]).'-index']);
    }

    /**
     * Displays a single MetalStack model.
     * @param integer $id
     * @return mixed
     */
    public function actionDown($id)
    {
        $model=$this->findModel($id);
        $return='';
        foreach ($model->kids as $kid)
        {
            $return.=$this->renderPartial('tr', ['model'=>$kid, 'parent_id'=>$model->ID,]);
        }
        return $return;
    }
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new MetalStack model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $this->initHistory();
        $model = new MetalStack(['afterConstruct'=>true,]);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Log::create(['metal_id'=>$model->ID]);
            return $this->redirect(['index']);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing MetalStack model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $this->initHistory();
        $model = $this->findModel($id);
        if(Yii::$app->user->identity->role!=\app\models\User::ROLE_ADMIN && Yii::$app->user->identity->id!=$model->author_id)
            throw new ForbiddenHttpException('You are not allowed to perform this action. ');

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Log::create(['metal_id'=>$model->ID]);
            return $this->redirect(['index']);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing MetalStack model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionTruncate()
    {
         $mineQuery=MetalStack::find()->mine(true);
         $conditionSql = Yii::$app->db->queryBuilder->buildCondition($mineQuery->where, $mineQuery->params);
         $conditionSql=Yii::$app->db->createCommand($conditionSql, $mineQuery->params)->rawSql;
        $count=MetalStack::find()->where($conditionSql)->count();
        MetalStack::deleteAll($conditionSql);
        Log::create(['description'=>Yii::$app->user->identity->link." truncated ".$count." items"]);
    }
    public function actionReadfile()
    {
        header("Content-Type: application/force-download");
        header('Content-Disposition: attachment; filename="staks.xml"');
        readfile('upload/user/'.Yii::$app->user->id.'/staks.xml');
        die();
    }
    public function actionDownload($download=true)
    {
        /* create a dom document with encoding utf8 */
        $domtree = new \DOMDocument('1.0', 'UTF-8');

        /* create the root element of the xml tree */
        $xmlRoot = $domtree->createElement("stacks");
        /* append it to the document created */
        $xmlRoot = $domtree->appendChild($xmlRoot);

        /* you should enclose the following two lines in a cicle */
        $models=MetalStack::find()->mine()->all();
        foreach ($models as $model) {
            $currentStack = $domtree->createElement("stack");
            $currentStack = $xmlRoot->appendChild($currentStack);

            foreach ($model->getAttributes(null, ['author_id']) as $key=>$value) {
                if($key=='status')
                    $value = $model->statusText;
                if($key=='metalSymbol')
                    $value = $model->type->metalDescription;
                if($key=='metalVendor')
                    $value = $model->vendor->vendorName;
                if($key=='metalShape')
                    $value = $model->shape->shape;
                if($key=='metalGrade')
                    $value = $model->grade->metalGrade;
                if($key=='metalOriginMint')
                    $value = $model->mint->title;
                $currentStack->appendChild($domtree->createElement($key,$value));
            }
        }
        /* get the xml printed */
        $domtree->preserveWhiteSpace = false;
        $domtree->formatOutput = true;
        //header('Content-type: text/xml');
        //header('Content-Disposition: attachment; filename="staks.xml"');
        //echo $domtree->saveXML();
        //die();

        if(!is_dir('upload/user/'.Yii::$app->user->id))
            mkdir('upload/user/'.Yii::$app->user->id);
        if($models)
            $domtree->save('upload/user/'.Yii::$app->user->id.'/staks.xml');
        Log::create(['description'=>Yii::$app->user->identity->link." downloaded ".count($models)." items"]);
        if($download)
            return $this->redirect('readfile');
    }
    public function actionUpdateMode($checked)
    {
        $model = Yii::$app->user->identity;
        $model->offline_mode=$checked;
        $model->save();
    }


    public function actionUpload()
    {
        /*
Yii::app()->db->createCommand("
TRUNCATE TABLE `Client`;
")->execute();
*/
        $countImported=0;
        if(isset($_FILES["file"]["tmp_name"]) && $file=$_FILES["file"]["tmp_name"])
        {
            if(!($xml = @simplexml_load_file($file)))
                Yii::$app->session->setFlash('error', "Wrong xml format.");
            else
            {
                foreach ($xml->stack as $stack) {
                    $model = MetalStack::findOne($stack->ID);
                    if(!$model)
                        $model = new MetalStack(['afterConstruct'=>true,]);
                    if(!$model->isNewRecord && !$_POST['overwrite'])
                        continue;

                    foreach ($stack as $key=>$value) {

                        $value = (string) $value;
                        if($key=='status')
                            $value = $value ? array_flip($model->statusValues)[(string) $value] : 0;
                        if($key=='metalOriginCountry')
                            $value = Country::find()->where(['title'=>$value,])->one()->id;
                        if($key=='metalSymbol')
                            $value = MetalType::find()->where(['metalDescription'=>$value,])->one()->ID;
                        if($key=='metalVendor')
                            $value = MetalVendors::find()->where(['vendorName'=>$value,])->one()->ID;
                        if($key=='metalShape')
                            $value = MetalShape::find()->where(['shape'=>$value,])->one()->ID;
                        if($key=='metalGrade')
                            $value = MetalGrade::find()->where(['metalGrade'=>$value,])->one()->ID;
                        if($key=='metalOriginMint')
                            $value = MetalMint::find()->where(['title'=>$value,])->one()->id;

                        $model->$key=(string) $value;
                    }
                    if(!$model->save())
                    {
                        $errorIDs[]=$model->title;
                        $errorTexts[Html::errorSummary($model, ['header'=>false,])]=Html::errorSummary($model, ['header'=>false,]);
                    }
                    else
                        $countImported++;
                }
                if(isset($errorIDs))
                    Yii::$app->session->setFlash('warning', "The following items(".implode(', ', $errorIDs).") failed to upload. <br>".implode(',', $errorTexts));
                Yii::$app->session->setFlash('success', "Successfully imported: $countImported rows.");
                Log::create(['description'=>Yii::$app->user->identity->link." uploaded ".$countImported." items"]);
            }
        }
        return $this->render('upload',array());
    }

    public function actionDelete($id)
    {
        $model=$this->findModel($id);
        $model->delete();
        Log::create(['description'=>Yii::$app->user->identity->link." deleted the item ".$model->title]);
        return $this->redirect(['index']);
    }

    /**
     * Finds the MetalStack model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return MetalStack the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = MetalStack::findOne($id)) !== null) {
            if($model->metalSymbol)
                $model->currentPrice = (float) History::find()->today()->metal($model->metalSymbol)->one()->metalValue;
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
