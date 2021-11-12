<?php

namespace Zbxin\Events;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Zbxin\Exceptions\CustomException;

trait EventQueryModelTrait
{
    /**
     * @var Model
     */

    public $currentModel;

    /**
     * @var string
     */

    public $code;

    /**
     * @param $code
     */

    public function query($code)
    {
        $this->code = $code;
        if (!$this->currentModel = $this->queryModel()->where($this->queryPrimary(), $code)->first()) {
            throw new CustomException($this->notFoundMessage());
        }
    }

    /**
     * @return Builder
     */

    abstract protected function queryModel();

    /**
     * @return string
     */

    protected function queryPrimary()
    {
        return 'code';
    }

    /**
     * @return string
     */

    public function notFoundMessage()
    {
        return '不存在的模型';
    }
}
