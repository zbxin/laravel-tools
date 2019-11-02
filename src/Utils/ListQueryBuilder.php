<?php

namespace ZhiEq\Utils;

use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Psr\Log\InvalidArgumentException;

class ListQueryBuilder
{
  const ORDER_TYPE_ASC = 'asc';
  const ORDER_TYPE_DESC = 'desc';

  /**
   * @var Builder
   */

  private $baseQuery;

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
   * @var bool
   */

  private $isEmptySearch = false;

  /**
   * @var bool
   */

  private $isCountBaseQuery = false;

  /**
   * @var array
   */

  private $hidden = [];

  /**
   * @var array
   */

  private $append = [];

  /**
   * @var array
   */

  private $visible = [];

  /**
   * @var array
   */

  private $needField = [];

  /**
   * @var array
   */

  private $searchKeywords = [];


  /**
   * ListQueryBuilder constructor.
   * @param Builder $query
   * @param array $configs
   */


  public function __construct(Builder $query, $configs = [])
  {
    $this->setBaseQuery($query);
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
    $query = $this->query;
    if ($this->withPage === true) {
      $query->limit($this->perPage)->offset(($this->page - 1) * $this->perPage);
    }
    return $query;
  }

  /**
   * @return Builder
   */

  public function getQuery()
  {
    return $this->query;
  }

  /**
   * @param Builder $query
   * @return $this
   */

  public function setBaseQuery(Builder $query)
  {
    $this->baseQuery = $query;
    return $this;
  }

  /**
   * @return Builder
   */

  public function getBaseQuery()
  {
    return $this->baseQuery;
  }

  /**
   * @param array $searchKeywords
   * @return $this
   */

  public function setSearchKeywords(array $searchKeywords)
  {
    $this->searchKeywords = $searchKeywords;
    return $this;
  }

  /**
   * @return array
   */

  public function getSearchKeywords()
  {
    if (empty($this->searchKeywords)) {
      $this->searchKeywords = $this->getSearchKeywordsFromRequest();
    }
    return $this->searchKeywords;
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

  public function withSearch(array $searchRules, $allowEmpty = true, Closure $customQuery = null)
  {
    if ($this->checkSearchKeywordsIsAllEmpty() && $allowEmpty === false) {
      $this->isEmptySearch = true;
      return $this;
    }
    $this->query = SearchKeyword::query($this->getSearchKeywords(), $this->query, $searchRules, $customQuery);
    logs()->info('search sql:' . $this->query->toSql());
    return $this;
  }

  /**
   * @return array|mixed|string
   */

  protected function getSearchKeywordsFromRequest()
  {
    $searchKeywords = $this->request->header($this->searchKeywordKey, null);
    $searchKeywords = !empty($searchKeywords) ? json_decode(base64_decode($searchKeywords), true) : [];
    logs()->info('search keywords', $searchKeywords);
    return $searchKeywords;
  }

  /**
   * @return bool
   */

  protected function checkSearchKeywordsIsAllEmpty()
  {
    $searchKeywords = $this->getSearchKeywords();
    if (empty($searchKeywords)) {
      return true;
    }
    return !(collect($this->searchRules)->count() === collect($this->searchRules)->filter(function ($rule) use ($searchKeywords) {
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
      })->count());
  }

  /**
   * @param array $needField
   * @return $this
   */

  public function withFilterField(array $needField)
  {
    $this->needField = $needField;
    return $this;
  }

  /**
   * @param array $hidden
   * @return $this
   */

  public function withHidden(array $hidden)
  {
    $this->hidden = $hidden;
    return $this;
  }

  /**
   * @param array $append
   * @return $this
   */

  public function withAppends(array $append)
  {
    $this->append = $append;
    return $this;
  }

  /**
   * @param array $visible
   * @return ListQueryBuilder
   */

  public function withVisible(array $visible)
  {
    $this->visible = $visible;
    return $this;
  }

  /**
   * @return $this
   */

  public function withCountBaseQuery()
  {
    $this->isCountBaseQuery = true;
    return $this;
  }

  /**
   * @return array|Builder[]|Collection
   */

  public function get()
  {
    if ($this->isEmptySearch) {
      return [];
    }
    return $this->query()->get();
  }

  /**
   * @return array|LengthAwarePaginator
   */

  public function paginate()
  {
    if ($this->isEmptySearch) {
      return [];
    }
    return $this->query->paginate($this->perPage, ['*'], 'Page', $this->page);
  }

  /**
   * @param $list
   * @return array
   */

  protected function convertList($list)
  {
    return array_map(function (Model $item) {
      if (!empty($this->hidden)) {
        $item->makeHidden($this->hidden);
      }
      if (!empty($this->append)) {
        $item->setAppends($this->append);
      }
      if (!empty($this->visible)) {
        $item->makeVisible($this->visible);
      }
      if (empty($this->needField)) {
        return $item->toArray();
      }
      return array_combine(array_map(function ($field) {
        return is_array($field) ? $field['key'] : $field;
      }, $this->needField), array_map(function ($field) use ($item) {
        if (is_string($field)) {
          return $item[$field] instanceof Carbon ? $item[$field]->toDateString() : $item[$field];
        } elseif (is_array($field)) {
          return $item[$field['key']] instanceof Carbon ? $item[$field['key']]->format($field['format']) : $item[$field['key']];
        } else {
          return null;
        }
      }, $this->needField));
    }, collect($list)->all());
  }

  /**
   * @return int
   */

  public function countBaseQuery()
  {
    return $this->baseQuery->count();
  }

  /**
   * @return array
   */

  public function getList()
  {
    return $this->convertList($this->get());
  }

  /**
   * @return array
   */

  public function paginateList()
  {
    $pageList = $this->paginate();
    $returnList = [
      'data' => empty($pageList) ? [] : $this->convertList($pageList->items()),
      'currentPage' => empty($pageList) ? 1 : $pageList->currentPage(),
      'total' => empty($pageList) ? 0 : $pageList->total(),
      'perPage' => empty($pageList) ? $this->perPage : $pageList->perPage(),
      'lastPage' => empty($pageList) ? 1 : $pageList->lastPage()
    ];
    if ($this->isCountBaseQuery) {
      $returnList['baseTotal'] = $this->countBaseQuery();
    }
    return $pageList;
  }
}
