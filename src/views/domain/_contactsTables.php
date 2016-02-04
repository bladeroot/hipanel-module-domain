<?php

use hipanel\modules\domain\models\Domain;
use yii\helpers\Html;
use yii\widgets\DetailView;

/*
 * @var $domainContactInfo array
 * @var $domainId integer
 * @var $domainName string
 */
?>

<?php foreach (Domain::$contactOptions as $contactType) : ?>
    <?php $contact = $domainContactInfo[$contactType] ?>
    <div class="col-md-6">
        <div class="row">
            <div class="col-md-6"><?= Html::tag('h4', Yii::t('app', ucfirst($contactType) . ' contact')) ?></div>
            <div class="col-md-6">
                <div class="pull-right btn-group" style="padding-top:10px">
                    <?= Html::a(Yii::t('app', 'Details'), ['@contact/view', 'id' => $contact['id']], ['class' => 'btn btn-default btn-xs']) ?>
                    <?= Html::a(Yii::t('app', 'Change'), [
                        '@contact/change-contact',
                        'contactId' => $contact['id'],
                        'contactType' => $contactType,
                        'domainId' => $domainId,
                        'domainName' => $domainName,
                    ], ['class' => 'btn btn-default btn-xs']) ?>
                </div>
            </div>
        </div>
        <?= DetailView::widget([
            'model' => $contact,
            'attributes' => [
                'name:raw:' . Yii::t('app', 'Name'),
                'email:email:' . Yii::t('app', 'Email'),
                'organization:raw:' . Yii::t('app', 'Organization'),
                'voice_phone:raw:' . Yii::t('app', 'Phone'),
                'fax_phone:raw:' . Yii::t('app', 'Fax'),
            ],
        ]) ?>
    </div>
<?php endforeach; ?>
