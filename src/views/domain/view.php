<?php

use hipanel\modules\dns\widgets\DnsZoneEditWidget;
use hipanel\modules\domain\grid\DomainGridView;
use hipanel\modules\domain\widgets\AuthCode;
use hipanel\widgets\Box;
use hipanel\widgets\Pjax;
use hipanel\widgets\ClientSellerLink;
use hiqdev\bootstrap_switch\BootstrapSwitchColumn;
use hiqdev\xeditable\widgets\XEditable;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\Modal;
use yii\helpers\Html;
use yii\helpers\Url;

$model->nameservers = str_replace(',', ', ', $model->nameservers);

$this->title = Html::encode($model->domain);
$this->subtitle = Yii::t('app', 'Domain detailed information') . ' #' . $model->id;
$this->breadcrumbs->setItems([
    ['label' => Yii::t('app', 'Domains'), 'url' => ['index']],
    $this->title,
]);
$this->registerCss(<<<CSS
.tab-pane {
    min-height: 300px;
}
CSS
);
?>

<?php Pjax::begin(Yii::$app->params['pjax']); ?>
<div class="row" xmlns="http://www.w3.org/1999/html">

    <div class="col-md-3">

        <?= Html::a('Domain renew', ['add-to-cart-renewal', 'model_id' => $model->id], ['class' => 'btn btn-block margin-bottom btn-warning', 'data-pjax' => 0]); ?>

        <?php Box::begin([
            'options' => [
                'class' => 'box-solid',
            ],
            'bodyOptions' => [
                'class' => 'no-padding',
            ],
        ]); ?>
        <div class="profile-user-img text-center">
            <img class="img-thumbnail" src="//mini.s-shot.ru/1024x768/PNG/200/Z100/?<?= $model->domain ?>"/>
        </div>
        <p class="text-center">
            <span class="profile-user-role"><?= $this->title ?></span>
            <br>
            <span class="profile-user-name"><?= ClientSellerLink::widget(compact('model')) ?></span>
        </p>

        <div class="profile-usermenu">
            <ul class="nav">
                <li>
                    <?php $url = 'http://' . $model->domain . '/' ?>
                    <?= Html::a('<i class="fa fa-globe"></i>' . Yii::t('app', 'Go to site ') . $url, $url, ['target' => '_blank']); ?>
                </li>
                <li>
                    <?php Modal::begin([
                        'header' => '<h4 class="modal-title">' . Yii::t('app', 'Push ' . Html::tag('b', $this->title)) . '</h4>',
                        'footer' => Html::submitButton(Yii::t('app', 'Push'), ['class' => 'btn btn-default push']),
                        'toggleButton' => ['label' => '<i class="ion-ios-paperplane-outline"></i>' . Yii::t('app', 'Push domain'), 'tag' => 'a', 'class' => 'clickable'],
                    ]); ?>


                    <?php
                    $form = ActiveForm::begin([
                        'id' => 'push-domain-form',
                    ]) ?>

                    <?= $form->field($model, 'client_id')->widget(\hipanel\modules\client\widgets\combo\ClientCombo::className())->hint(Yii::t('app', 'Client, you push your domain to')) ?>

                    <?php $form->end() ?>

                    <?php Modal::end() ?>
                </li>
                <?php if (Yii::$app->user->can('support') && Yii::$app->user->not($model->client_id)) : ?>
                    <li><?= $this->render('_sync_button', compact('model')) ?></li>
                <?php endif ?>
            </ul>
        </div>
        <?php Box::end() ?>
    </div>

    <div class="col-md-9">
        <div class="nav-tabs-custom">
            <!-- Tabs within a box -->
            <ul class="nav nav-tabs">
                <li class="active"><a href="#domain-details"
                                      data-toggle="tab"><?= Yii::t('app', 'Domain details') ?></a></li>
                <li><a href="#ns-records" data-toggle="tab"><?= Yii::t('app', 'NS records') ?></a></li>
                <!--                    <li><a href="#authorization-code" data-toggle="tab">-->
                <?php//= Yii::t('app', 'Authorization code') ?><!--</a></li>-->
                <li><a href="#dns-records" data-toggle="tab"><?= Yii::t('app', 'DNS records') ?></a></li>
                <!--                    <li><a href="#url-forwarding" data-toggle="tab">-->
                <?php//= Yii::t('app', 'URL forwarding') ?><!--</a></li>-->
                <!--                    <li><a href="#email-forwarding" data-toggle="tab">-->
                <?php//= Yii::t('app', 'Email forwarding') ?><!--</a></li>-->
                <!--                    <li><a href="#parking" data-toggle="tab">-->
                <?php//= Yii::t('app', 'Parking') ?><!--</a></li>-->
                <li><a href="#contacts" data-toggle="tab"><?= Yii::t('app', 'Contacts') ?></a></li>
            </ul>
            <div class="tab-content">

                <!-- Morris t - Sales -->
                <div class="tab-pane active" id="domain-details">
                    <?= DomainGridView::detailView([
                        'boxed' => false,
                        'model' => $model,
                        'columns' => [
                            'seller_id', 'client_id',
                            [
                                'attribute' => 'domain',
                                'headerOptions' => ['class' => 'text-nowrap'],
                            ],
                            'note',
                            'state',
                            'whois_protected', 'is_secured',
                            'created_date', 'expires',
                            'autorenewal',
                            [
                                'attribute' => 'authCode',
                                'label' => Yii::t('app', 'Auth code'),
                                'value' => function ($model) {
                                    return AuthCode::widget(['domainId' => $model->id]);
                                },
                                'format' => 'raw',
                            ],
                        ],
                    ]) ?>

                </div>

                <!-- NS records -->
                <div class=" tab-pane" id="ns-records">
                    <?= Html::tag('b', $model->getAttributeLabel('nameservers') . ': '); ?>
                    <?= XEditable::widget([
                        'model' => $model,
                        'attribute' => 'nameservers',
                        'pluginOptions' => [
                            'placement' => 'bottom',
                            'type' => 'textarea',
                            'emptytext' => Yii::t('app', 'There are no NS. Domain may not work properly'),
                            'url' => Url::to('set-nss'),
                        ],
                    ]); ?>
                </div>

                <!-- Authorization code -->
                <!--                    <div class=" tab-pane" id="authorization-code"></div>-->

                <!-- DNS records -->
                <div class="tab-pane" id="dns-records">
                    <?= DomainGridView::detailView([
                        'model' => $model,
                        'boxed' => false,
                        'columns' => [
                            'is_premium' => [
                                'label' => Yii::t('app', 'Premium package'),
                                'value' => function ($model) {
                                    $enablePremiumLink = Html::a(Yii::t('app', 'Enable premium'), Url::toRoute(''), ['class' => 'btn btn-success btn-xs pull-right']);

                                    return $model->is_premium == 't' ? Yii::t('app', 'Activated to ') . Yii::$app->formatter->asDatetime($model->prem_expires) : sprintf('%s %s', Yii::t('app', 'Not enabled'), $enablePremiumLink);
                                },
                                'format' => 'raw',
                            ],
                            'premium_autorenewal' => [
                                'class' => BootstrapSwitchColumn::className(),
                                'attribute' => 'premium_autorenewal',
                                'label' => Yii::t('app', 'Premium package autorenewal'),
                                'filter' => false,
                                'url' => Url::toRoute(['@hdomain/set-paid-feature-autorenewal']),
                                'popover' => 'The domain will be autorenewed for one year in a week before it expires if you have enough credit on your account',
                                'visible' => $model->is_premium == 't' ? true : false,
                                'pluginOptions' => [
                                    'onColor' => 'info',
                                ],
                            ],
//                                [
//                                    'label' => Yii::t('app', 'Premium package autorenewal'),
//                                    'value' => BootstrapSwitch::widget([
//                                        'model' => $model,
//                                        'attribute' => 'premium_autorenewal',
//                                        'options' => [
//                                            'label' => false
//                                        ],
//                                        'pluginOptions' => [
//                                            'inlineLabel' => false,
//                                            'labelText' => false
//                                        ]
//                                    ]),
//                                    'format' => 'raw',
//                                ]
                        ],
                    ]); ?>
                    <?php if (Yii::$app->hasModule('dns')) {
                        echo DnsZoneEditWidget::widget([
                            'domainId' => $model->id,
                            'clientScriptWrap' => function ($js) {
                                return new \yii\web\JsExpression("
                                    $('a[data-toggle=tab]').filter(function () {
                                        return $(this).attr('href') == '#dns-records';
                                    }).on('shown.bs.tab', function (e) {
                                        $js
                                    });
                                ");
                            }
                        ]);
                    } ?>
                </div>

                <!-- URL forwarding -->
                <div class=" tab-pane" id="url-forwarding"></div>

                <!-- E-mail forwarding -->
                <div class=" tab-pane" id="email-forwarding"></div>

                <!-- Parking -->
                <div class=" tab-pane" id="parking"></div>

                <!--  -->
                <div class=" tab-pane" id="contacts">
                    <div class="row">
                        <div class="col-md-12">
                            <?= $this->render('_modalContacts', ['model' => $model]) ?>
                        </div>
                        <div id="contacts-tables">
                            <?= $this->render('_contactsTables', ['domainContactInfo' => $domainContactInfo]) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php Pjax::end(); ?>
