<?php

use hipanel\modules\domain\assets\NSyncPluginAsset;
use hipanel\widgets\Box;
use hipanel\widgets\Pjax;
use hiqdev\assets\icheck\iCheckAsset;
use wbraganca\dynamicform\DynamicFormWidget;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;
use yii\helpers\Url;

Yii::$app->assetManager->forceCopy = true;

NSyncPluginAsset::register($this);

?>

<?php Pjax::begin(['id' => 'nss-form-pjax', 'enablePushState' => false, 'enableReplaceState' => false]) ?>
<?php $this->registerJs("$('#nss-form-pjax').NSync();"); ?>
<?php $form = ActiveForm::begin([
    'id' => 'dynamic-form',
    'action' => 'set-nss',
    'validationUrl' => Url::toRoute(['validate-nss', 'scenario' => 'default']),
    'options' => [
        'data-pjax' => '#nss-form-pjax',
    ],
]); ?>
<?= Html::activeHiddenInput($model, "id") ?>
<?= Html::activeHiddenInput($model, "domain") ?>

    <?php Box::begin(); ?>
    <div class="row" style="margin-top: 15pt;">
        <div class="col-md-10 inline-form-selector">
            <?= Html::activeTextInput($model, 'nameservers', ['class' => 'form-control']) ?>
        </div>
        <div class="col-md-2 text-right">
            <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-default']) ?>
        </div>
    </div>
    <?php Box::end(); ?>

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-body">
                    <?php DynamicFormWidget::begin([
                        'widgetContainer' => 'dynamicform_wrapper', // required: only alphanumeric characters plus "_" [A-Za-z0-9_]
                        'widgetBody' => '.container-items', // required: css class selector
                        'widgetItem' => '.item', // required: css class
                        'limit' => 13, // the maximum times, an element can be cloned (default 999)
                        'min' => 1, // 0 or 1 (default 1)
                        'insertButton' => '.add-item', // css class
                        'deleteButton' => '.remove-item', // css class
                        'model' => reset($nsModels),
                        'formId' => 'dynamic-form',
                        'formFields' => [
                            'name',
                            'ip',
                        ],
                    ]) ?>
                    <div class="container-items">
                        <?php foreach ($nsModels as $i => $nsModel): ?>
                            <div class="item">
                                <div class="row" style="margin-bottom: 5pt">
                                    <div class="col-md-5">
                                        <?= Html::activeTextInput($nsModel, "[$i]name", ['placeholder' => $nsModel->getAttributeLabel('name'), 'class' => 'form-control']) ?>
                                    </div>
                                    <div class="col-md-5">
                                        <?= Html::activeTextInput($nsModel, "[$i]ip", [
                                            'placeholder' => $nsModel->getAttributeLabel('ip'),
                                            'class' => 'form-control',
                                        ]) ?>
                                    </div>
                                    <div class="col-md-2 text-right">
                                        <div class="btn-group" role="group">
                                            <button type="button" class="add-item btn btn-default"><i
                                                    class="glyphicon glyphicon-plus"></i></button>
                                            <button type="button" class="remove-item btn btn-default"><i
                                                    class="glyphicon glyphicon-minus"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php DynamicFormWidget::end(); ?>
                </div>
            </div>
        </div>
    </div>

<?php ActiveForm::end(); ?>
<?php Pjax::end(); ?>