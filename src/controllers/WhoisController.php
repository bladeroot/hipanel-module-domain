<?php

namespace hipanel\modules\domainchecker\controllers;

use hipanel\helpers\ArrayHelper;
use hipanel\modules\domain\repositories\DomainTariffRepository;
use hipanel\modules\domainchecker\models\Whois;
use yii\web\UnprocessableEntityHttpException;
use Yii;

class WhoisController extends \hipanel\base\CrudController
{
    private function getWhoisModel($domain)
    {
        $whois = Yii::$app->hiart->createCommand()->perform('domainGetWhois', ['domain' => $domain]);

        $model = reset(Whois::find()->populate([$whois]));

        return $model;
    }

    public function actionIndex($domain = null)
    {
        $model = new Whois;
        $model->load(Yii::$app->request->get(), '');
        if (!$model->validate()) {
            throw new UnprocessableEntityHttpException();
        }
        /** @var DomainTariffRepository $repository */
        $repository = Yii::createObject(DomainTariffRepository::class);
        $availableZones = ArrayHelper::getColumn($repository->getAvailableZones(), 'zone', false);

        return $this->render('index', [
            'model' => $model,
            'availableZones' => $availableZones,
        ]);
    }

    public function actionLookup()
    {
        $request = Yii::$app->request;
        $model = $this->getWhoisModel($request->post('domain'));
        if ($request->isAjax) {
            // $sShotSrc =

            return $this->renderPartial('_view', [
                'model' => $model,
              //  'sShotSrc' => $sShotSrc,
            ]);
        }

        Yii::$app->end();
    }
}
