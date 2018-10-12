<?php

namespace ZhiEq\Utils;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Psr\Log\InvalidArgumentException;

class ListQueryBuilder
{
    const ORDER_TYPE_ASC = 'asc';
    const ORDER_TYPE_DESC = 'desc';

    /**
     * @var Builder
     */

    private $query;

    /**
     * @var Request
     */

    private $request;

    /**
     * @var string
     */

    private $pageKey = 'X-Page';

    /**
     * @var string
     */

    private $perPageKey = 'X-Per-Page';

    /**
     * @var string
     */

    private $orderFieldKey = 'X-Order-Field';

    /**
     * @var string
     */

    private $orderTypeKey = 'X-Order-Type';

    /**
     * @var string
     */

    private $searchKeywordKey = 'X-Search-Keywords';

    /**
     * @var integer
     */

    private $perPage;

    /**
     * @var integer
     */

    private $page;

    /**
     * @var string
     */

    private $orderField;

    /**
     * @var string
     */

    private $orderType;

    /**
     * @var bool
     */

    private $withPage = false;

    /**
     * @var array
     */

    private $searchRules = [];


    /**
     * ListQueryBuilder constructor.
     * @param Builder $query
     * @param array $configs
     */


    public function __construct(Builder $query, $configs = [])
    {
        $this->setQuery($query);
        $this->setRequest(\Illuminate\Support\Facades\Request::instance());
        $this->resolveConfigs($configs);
    }

    /**
     * @param Builder $query
     * @param array $configs
     * @return ListQueryBuilder
     */

    public static function create(Builder $query, $configs = [])
    {
        return new self($query, $configs);
    }

    /**
     * @param $configs
     */

    protected function resolveConfigs(array $configs)
    {
        collect([
            'pageKey', 'perPageKey', 'orderFieldKey', 'orderTypeKey', 'searchKeywordKey'
        ])->each(function ($key) use ($configs) {
            $this->$key = isset($configs[$key]) ? $configs[$key] : $this->$key;
        });
    }

    /**
     * @param Request $request
     * @return $this
     */

    public function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @return Request
     */

    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param Builder $query
     * @return $this
     */

    public function setQuery(Builder $query)
    {
        $this->query = $query;
        return $this;
    }

    /**
     * @return Builder
     */

    public function query()
    {
        return $this->query;
    }

    /**
     * @param int $defaultPerPage
     * @param int $defaultPage
     * @return $this
     */

    public function withPage($defaultPerPage = 10, $defaultPage = 1)
    {
        $this->perPage = $this->request->header($this->perPageKey, $defaultPerPage);
        $this->page = $this->request->header($this->pageKey, $defaultPage);
        $this->withPage = true;
        return $this;
    }

    /**
     * @param string $defaultOrderField
     * @param string $defaultOrderType
     * @param array $allowOrderFiled
     * @return $this
     */

    public function withOrder($defaultOrderField, $defaultOrderType = self::ORDER_TYPE_DESC, array $allowOrderFiled = [])
    {
        if (!in_array($defaultOrderType, [self::ORDER_TYPE_ASC, self::ORDER_TYPE_DESC])) {
            throw new InvalidArgumentException('default order type is valid.');
        }
        $orderField = $this->request->header($this->orderFieldKey, $defaultOrderField);
        $this->orderField = !empty($allowOrderFiled) ? $this->checkOrderFieldIsAllow($allowOrderFiled, $orderField) : $orderField;
        $this->orderType = $this->request->header($this->orderTypeKey, $defaultOrderType);
        $this->query->orderBy($this->orderField, $this->orderType);
        return $this;
    }

    /**
     * @param $allowOrderFiled
     * @param $orderField
     * @return null
     */

    protected function checkOrderFieldIsAllow($allowOrderFiled, $orderField)
    {
        foreach ($allowOrderFiled as $key => $value) {
            $diffFiled = is_numeric($key) ? $value : $key;
            if ($orderField === $diffFiled) {
                return $value;
            }
        }
        return null;
    }

    /**
     * @param array $searchRules
     * @param bool $allowEmpty
     * @param Closure|null $customQuery
     * @return $this
     */

    public function withSearch(array $searchRules, $allowEmpty = false, Closure $customQuery = null)
    {
        if ($this->checkSearchKeywordsIsAllEmpty() && $allowEmpty === false) {
            return $this;
        }
        $this->query = SearchKeyword::query($this->getSearchKeywordsFromRequest(), $this->query, $searchRules, $customQuery);
        return $this;
    }

    /**
     * @return array|mixed|string
     */

    protected function getSearchKeywordsFromRequest()
    {
        $searchKeywords = $this->request->header($this->searchKeywordKey, null);
        $searchKeywords = !empty($searchKeywords) ? json_decode(base64_decode($searchKeywords), true) : [];
        return $searchKeywords;
    }

    /**
     * @return bool
     */

    protected function checkSearchKeywordsIsAllEmpty()
    {
        $searchKeywords = $this->getSearchKeywordsFromRequest();
        if (empty($searchKeywords)) {
            return true;
        }
        return collect($this->searchRules)->count() === collect($this->searchRules)->filter(function ($rule) use ($searchKeywords) {
                if (!is_array($rule['key']) && !isset($rule['value'])) {
                    return SearchKeyword::checkValueIssetAndEmpty($searchKeywords, $rule['key']);
                }
                if (!is_array($rule['key']) && isset($rule['value'])) {
                    $value = $rule['value'] instanceof Closure ? $rule['value']($searchKeywords) : $rule['value'];
                    return $value !== null;
                }
                return collect($rule['key'])->count() === collect($rule['key'])->filter(function ($value, $key) use ($searchKeywords) {
                        $readKey = is_numeric($key) ? $value : $key;
                        return SearchKeyword::checkValueIssetAndEmpty($searchKeywords, $readKey);
                    });
            })->count();
    }

    public function get()
    {
        return $this->query()->get();
    }

    public function paginate()
    {
        return $this->query->paginate();
    }
}
