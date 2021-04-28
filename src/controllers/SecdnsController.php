<?php
/**
 * Domain plugin for HiPanel
 *
 * @link      https://github.com/hiqdev/hipanel-module-domain
 * @package   hipanel-module-domain
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2015-2019, HiQDev (http://hiqdev.com/)
 */

namespace hipanel\modules\domain\controllers;

use hipanel\actions\Action;
use hipanel\actions\IndexAction;
use hipanel\actions\PrepareBulkAction;
use hipanel\actions\ProxyAction;
use hipanel\actions\RedirectAction;
use hipanel\actions\RenderAction;
use hipanel\actions\RenderJsonAction;
use hipanel\actions\SmartDeleteAction;
use hipanel\actions\SmartPerformAction;
use hipanel\actions\ValidateFormAction;
use hipanel\filters\EasyAccessControl;
use hipanel\helpers\ArrayHelper;
use hipanel\modules\domain\models\Domain;
use hiqdev\hiart\Collection;
use Yii;
use yii\base\DynamicModel;
use yii\base\Event;
use yii\db\Exception;
use yii\helpers\Json;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\filters\VerbFilter;

class SecdnsController extends \hipanel\base\CrudController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            [
                'class' => EasyAccessControl::class,
                'actions' => [
                    '*' => 'domain.read',
                ],
            ],
        ]);
    }

    public function actions()
    {
        return array_merge(parent::actions(), [
            'search' => [
                'class' => ComboSearchAction::class,
            ],
            'index' => [
                'class' => IndexAction::class,
                'filterStorageMap' => [
                    'domain_like' => 'domain.domain.domain_like',
                    'client_id' => 'client.client.id',
                    'seller_id' => 'client.client.seller_id',
                ],
            ],
            'create' => [
                'class' => SmartPerformAction::class,
                'success' => Yii::t('hipanel:domain', 'SecDNS record was created'),
                'error' => Yii::t('hipanel:domain', 'Error during creating'),
            ],
            'create-modal' => [
            ],
            'delete' => [
                'class' => SmartDeleteAction::class,
                'success' => Yii::t('hipanel:domain', 'SecDNS record was deleted'),
            ],
            'validate-form' => [
                'class' => ValidateFormAction::class,
            ],
        ]);
    }

    public function actionView($id)
    {
        if (($models = $this->newModel()->find()->where(['domain_id' => $id])->all()) === null) {
            throw new NotFoundHttpException('SecDNS does not exist');
        }

        $recordsDataProvider = new ArrayDataProvider([
            'allModels' => $models,
            'pagination' => false,
            'modelClass' => Record::class,
        ]);

        return $this->render('view', [
            'model' => $model,
            'recordsDataProvider' => $recordsDataProvider,
        ]);
    }
}
