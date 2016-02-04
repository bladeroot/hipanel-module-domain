<?php

/*
 * Domain plugin for HiPanel
 *
 * @link      https://github.com/hiqdev/hipanel-module-domain
 * @package   hipanel-module-domain
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2015-2016, HiQDev (http://hiqdev.com/)
 */

namespace hipanel\modules\domain\widgets;

use hipanel\modules\domain\models\Domain;

class Expires extends \hipanel\widgets\Label
{
    /**
     * @var Domain
     */
    public $model;

    public function init()
    {
        $expires = $this->model->expires;
        if (strtotime('+30 days', time()) < strtotime($expires)) {
            $class = 'none';
        } elseif (strtotime('+0 days', time()) < strtotime($expires)) {
            $class = 'warning';
        } else {
            $class = 'danger';
        }

        $this->color = $class;
        $this->label = \Yii::$app->formatter->asDate($expires);
        parent::init();
    }
}
