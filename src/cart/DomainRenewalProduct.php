<?php
/**
 * Domain plugin for HiPanel
 *
 * @link      https://github.com/hiqdev/hipanel-module-domain
 * @package   hipanel-module-domain
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2015-2019, HiQDev (http://hiqdev.com/)
 */

namespace hipanel\modules\domain\cart;

use DateTime;
use hipanel\modules\domain\models\Domain;
use hipanel\modules\finance\cart\BatchPurchasablePositionInterface;
use hipanel\modules\finance\cart\BatchPurchaseStrategy;
use Yii;

class DomainRenewalProduct extends AbstractDomainProduct implements BatchPurchasablePositionInterface
{
    /** {@inheritdoc} */
    protected $_purchaseModel = 'hipanel\modules\domain\cart\DomainRenewalPurchase';

    /** {@inheritdoc} */
    protected $_operation = 'renewal';

    /** {@inheritdoc} */
    public static function primaryKey()
    {
        return ['model_id'];
    }

    /**
     * @var integer[] the limit of days before expiration date for each domain zone, when domain can be renewed
     */
    protected $daysBeforeExpire;

    /** {@inheritdoc} */
    public function load($data, $formName = null)
    {
        if ($result = parent::load($data, '')) {
            $this->ensureRelatedData();
        }

        return $result;
    }

    /** {@inheritdoc} */
    private function ensureRelatedData()
    {
        $this->_model = Domain::findOne(['id' => $this->model_id]);
        $this->name = $this->_model->domain;
        $this->description = Yii::t('hipanel:domain', 'Renewal');
    }

    /** {@inheritdoc} */
    public function getId()
    {
        return hash('crc32b', implode('_', ['domain', 'renewal', $this->_model->id]));
    }

    /** {@inheritdoc} */
    public function getCalculationModel($options = [])
    {
        return parent::getCalculationModel(array_merge([
            'id' => $this->model_id,
            'client' => $this->getModel()->client,
            'seller' => $this->getModel()->seller,
        ], $options));
    }

    /** {@inheritdoc} */
    public function getPurchaseModel($options = [])
    {
        $this->ensureRelatedData(); // To get fresh domain expiration date
        return parent::getPurchaseModel(array_merge(['expires' => $this->_model->expires], $options));
    }

    /** {@inheritdoc} */
    public function getQuantityOptions()
    {
        $result = [];
        if (empty($this->_model)) {
            return $result;
        }

        $limit = $this->_model->max_delegation;
        $interval = (new \DateTime())->diff(new \DateTime($this->_model->expires));

        if ($interval->y >= 0 && !$interval->invert) {
            $limit -= $interval->y;
            if ($interval->m > 0 || $interval->d > 0) {
                --$limit;
            }
        }

        $limit = $limit < 1 ? 1 : $limit;
        for ($n = 1; $n <= $limit; ++$n) {
            $result[$n] = Yii::t('hipanel:domain', '{0, plural, one{# year} other{# years}}', $n);
        }

        return $result;
    }

    /**
     * Checks whether domain reached the limit of days before expiration date and can be renewed.
     *
     * @param $attribute
     * @return bool
     */
    public function daysBeforeExpireValidator($attribute)
    {
        $this->daysBeforeExpire = $this->daysBeforeExpire ?? $this->getDaysBeforeExpire();
        if (!$this->_model->days_before_expire) {
            return true;
        }

        $minDays = $this->_model->days_before_expire;
        $interval = (new DateTime())->diff(new DateTime($this->_model->expires));
        $diff = $interval->format('%a') - $minDays;
        if ($diff <= 0) {
            return true;
        }

        $date = Yii::$app->formatter->asDate((new DateTime())->add(new \DateInterval("P{$diff}D")));
        $this->addError('id', Yii::t('hipanel:domain', 'Domains in zone {zone} could be renewed only in last {min, plural, one{# day} other{# days}} before the expiration date. You are able to renew domain {domain} only after {date} (in {days, plural, one{# day} other{# days}})', [
            'zone' => (string) $this->getZone(),
            'min' => (int) $minDays,
            'date' => (string) $date,
            'days' => (int) $diff,
            'domain' => (string) $this->name
        ]));
        
        return false;
    }

    /**
     * Checks domains status.
     *
     * @param $attribute
     * @return bool
     */
    public function statusValidator($attribute)
    {
        if (!$this->_model->isRenewable()) {
            $this->addError('id', Yii::t('hipanel:domain', 'Domain status prohibits this operation'));
        }
    }

    /** {@inheritdoc} */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['model_id'], 'integer'],
            [['id'], 'daysBeforeExpireValidator'],
            [['id'], 'statusValidator'],
        ]);
    }

    public function getBatchPurchaseStrategyClass()
    {
        return BatchPurchaseStrategy::class;
    }
}
