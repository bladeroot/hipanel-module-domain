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

use hipanel\modules\domain\models\Domain;
use hipanel\modules\domain\models\Zone;
use hipanel\modules\finance\cart\AbstractCartPosition;
use hipanel\validators\DomainValidator;
use hiqdev\yii2\cart\DontIncrementQuantityWhenAlreadyInCart;
use Yii;

abstract class AbstractDomainProduct extends AbstractCartPosition implements DontIncrementQuantityWhenAlreadyInCart
{
    /**
     * @var Domain
     */
    protected $_model;

    /**
     * @var string the operation name
     */
    protected $_operation;

    /** {@inheritdoc} */
    protected $_calculationModel = Calculation::class;

    /**
     * @var integer[] The limit of quantity (years of purchase/renew) for each domain zone in years
     */
    protected $quantityLimits;

    /** {@inheritdoc} */
    public function getIcon()
    {
        return '<i class="fa fa-globe"></i>';
    }

    public function rules()
    {
        return [
            [['name'], 'required'],
        ];
    }

    public function getName(): string
    {
        return DomainValidator::convertAsciiToIdn($this->name);
    }

    /**
     * Extracts domain zone from.
     * @return string
     */
    public function getZone()
    {
        list(, $zone) = explode('.', $this->name, 2);

        return $zone;
    }

    /** {@inheritdoc} */
    public function getQuantityOptions()
    {
        return [];
    }

    /** {@inheritdoc} */
    public function getCalculationModel($options = [])
    {
        return parent::getCalculationModel(array_merge([
            'type' => $this->_operation,
            'domain' => $this->name,
            'zone' => $this->getZone(),
        ], $options));
    }

    protected function serializationMap()
    {
        $parent = parent::serializationMap();
        $parent['_operation'] = $this->_operation;
        $parent['_model'] = $this->_model;

        return $parent;
    }

    protected function getQuantityLimits() : array
    {
        return Yii::$app->cache->getOrSet(['get-zones-quantity-limits', $user->id], function() {
            $zones = $this->getZoneData();
            $data = ['*' => 10];
            foreach ($zones as $name => $zone) {
                if ($zone->max_delegation) {
                    $data[substr($name, 1)] = (int) $zone->max_delegation;
                }
            }

            return $data;
        });
    }

    protected function getZoneData()
    {
        return Yii::$app->cache->getOrSet(['get-zones-data-with-values', $user->id], function() {
            $data = Zone::find()->where(['state' => Zone::STATE_OK])->all();
            foreach ($data as $zone) {
                $zones[$zone->zone] = $zone;
                $zones[$zone->name] = $zone;
            }

            return $zones;
        });
    }

    protected function setQuantityList($limit)
    {
    }
}
