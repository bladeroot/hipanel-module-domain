<?php
/**
 * @link    http://hiqdev.com/hipanel-module-domain
 * @license http://hiqdev.com/hipanel-module-domain/license
 * @copyright Copyright (c) 2015 HiQDev
 */

namespace hipanel\modules\domain\models;

use hipanel\helpers\StringHelper;
use hipanel\validators\DomainValidator;
use hipanel\validators\IpValidator;
use Yii;

class Host extends \hipanel\base\Model
{
    use \hipanel\base\ModelTrait;

    /** @inheritdoc */
    public function rules () {
        return [
            [['host'],                                  'safe'],
            [['id'],                                    'safe'],
            [['id'],                                    'integer', 'on' => 'delete'],
            [['seller_id','client_id','domain_id'],     'safe'],
            [['seller','client'],                       'safe'],
            [['domain','host'],                         'safe'],
            [['ip'],                                    'safe'],
            [['ips'],                                   'safe'],
            [['host', 'ips'], 'required', 'on' => 'create'],
            [['host'], DomainValidator::className()],

            [['ips'], 'filter', 'filter' => function($value) {
                if (!is_array($value)) {
                    return (mb_strlen($value) > 0 ) ? StringHelper::mexplode($value) : true;
                } else {
                    return $value;
                }
            }, 'on' => ['create', 'update']],

            [['ips'], 'each', 'rule' => [IpValidator::className()], 'on' => ['create', 'update']],
        ];
    }

    /** @inheritdoc */
    public function attributeLabels () {
        return $this->mergeAttributeLabels([
            'remoteid'              => Yii::t('app', 'Remote ID'),
            'seller'                => Yii::t('app', 'Reseller'),
            'host'                  => Yii::t('app', 'Name Server'),
            'ip'                    => Yii::t('app', 'IP'),
            'ips'                   => Yii::t('app', 'IPs'),
            'created_date'          => Yii::t('app', 'Create Time'),
            'updated_date'          => Yii::t('app', 'Update Time'),
        ]);
    }
}
