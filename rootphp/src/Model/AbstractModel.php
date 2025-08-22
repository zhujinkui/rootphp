<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Zhujinkui\Rootphp\Model;

use Hyperf\Database\Model\Builder;
use Hyperf\DbConnection\Model\Model;
use Hyperf\Utils\Str;
use JetBrains\PhpStorm\ArrayShape;

class AbstractModel extends Model
{
    /**
     * 添加单条
     *
     * @param array $data 添加的数据
     *
     * @return int 执行结果
     */
    public function createOne(array $data): int
    {
        $new_data = $this->columnsFormat($data, true, true);
        return self::query()->insertGetId($new_data);
    }

    /**
     * 添加多条
     *
     * @param array $data 添加的数据
     *
     * @return bool 执行结果
     */
    public function createAll(array $data): bool
    {
        $new_data = array_map(function ($item) {
            return $this->columnsFormat($item, true, true);
        }, $data);
        return self::query()->insert($new_data);
    }

    /**
     * 删除 - 单条
     *
     * @param int $id 删除ID
     *
     * @return int 删除条数
     */
    public function deleteOne(int $id): int
    {
        return self::destroy($id);
    }

    /**
     * 删除 - 多条
     *
     * @param array $ids 删除ID
     *
     * @return int 删除条数
     */
    public function deleteAll(array $ids): int
    {
        return self::destroy($ids);
    }

    /**
     * 删除多条
     *
     * @param array $where
     *
     * @return int
     */
    public function deleteAllByField(array $where): int
    {
        return self::where($where)->delete();
    }

    /**
     * 修改单条 - 根据ID.
     *
     * @param int   $id   id
     * @param array $data 修改数据
     *
     * @return int 修改条数
     */
    public function updateOneById(int $id, array $data): int
    {
        $new_data = $this->columnsFormat($data, true, true);

        return self::query()->where('id', $id)->update($new_data);
    }

    /**
     * 修改单条 - 根据字段
     *
     * @param string $field
     * @param array  $data
     *
     * @return int
     */
    public function updateOneByField(string $field, array $data): int
    {
        $new_data = $this->columnsFormat($data, true, true);

        return self::query()->where($field, $data[$field])->update($new_data);
    }

    /**
     * 修改多条 - 根据字段
     *
     * @param array $where
     * @param array $data
     *
     * @return int
     */
    public function updateByGroupField(array $where, array $data): int
    {
        $new_data = $this->columnsFormat($data, true, true);
        return self::query()->where($where)->update($new_data);
    }

    /**
     * 查询单条 - 根据ID.
     *
     * @param int            $id      ID
     * @param array|string[] $columns 查询字段
     *
     * @return array 数组
     */
    public function getOneById(int $id, array $columns = ['*']): array
    {
        $data = self::query()->find($id, $columns);
        $data || $data = collect([]);
        return $data->toArray();
    }

    /**
     * 查询单条 - 根据字段
     *
     * @param array $where
     * @param array $columns
     *
     * @return array
     */
    public function getOneByField(array $where, array $columns = ['*']): array
    {
        $model = $this->optionWhere($where);
        return $model->first($columns)->toArray();
    }

    /**
     * 查询多条 - 根据ID.
     *
     * @param array          $ids     ID
     * @param array|string[] $columns 查询字段
     *
     * @return array 数组
     */
    public function getAllById(array $ids, array $columns = ['*']): array
    {
        $data = self::query()->find($ids, $columns);
        $data || $data = collect([]);
        return $data->toArray();
    }

    /**
     * 查询所有
     *
     * @param array $where
     * @param array $columns
     *
     * @return array
     */
    public function getAll(array $where = [], array $columns = ['*']): array
    {
        $model = $this->optionWhere($where);
        return $model->select($columns)->get()->toArray();
    }

    /**
     * 格式化表字段.
     *
     * @param array $value          ...
     * @param bool  $isTransSnake   是否转snake
     * @param bool  $isColumnFilter 是否过滤表不存在的字段
     *
     * @return array ...
     */
    public function columnsFormat(array $value, bool $isTransSnake = false, bool $isColumnFilter = false): array
    {
        $formatValue = [];
        $isColumnFilter && $tableColumns = array_flip(\Hyperf\Database\Schema\Schema::getColumnListing($this->getTable()));
        foreach ($value as $field => $fieldValue) {
            $isTransSnake && $field = \Hyperf\Stringable\Str::snake($field);
            if ($isColumnFilter && !isset($tableColumns[$field])) {
                continue;
            }
            $formatValue[$field] = $fieldValue;
        }
        return $formatValue;
    }

    /**
     *
     * @param array $where
     * @param array $columns
     * @param array $options
     *
     * @return array
     */
    public function getPageList(array $options = [], array $where = [], array $columns = ['*']): array
    {
        $model = $this->optionWhere($where, $options);

        ## 分页参数
        $page_num  = isset($options['page_num']) ? (int)$options['page_num'] : 10;
        $page_name = $options['page_name'] ?? 'page';
        $page      = isset($options['page']) ? (int)$options['page'] : null;

        ## 分页
        $result = $model->paginate($page_num, $columns, $page_name, $page);
        //$result || $result = collect([]);

        return [
            'current_page' => $result->currentPage(),
            //'page_num'     => $result->count(),
            //'total'        => $result->total(),
            'list'         => $result->items()
        ];
    }

    /**
     * @param array    $where   查询条件
     * @param string[] $options 可选项 ['orderByRaw'=> 'id asc', 'skip' => 15, 'take' => 5]
     *
     * @return \Hyperf\Database\Model\Builder|\Hyperf\Database\Query\Builder
     */
    public function optionWhere(array $where = [], array $options = [])
    {
        $model = new static();

        if (!empty($where) && is_array($where)) {
            foreach ($where as $k => $v) {
                ## 一维数组
                if (!is_array($v)) {
                    $model = $model->where($k, $v);
                    continue;
                }

                ## 二维索引数组
                if (is_numeric($k)) {
                    $v[1]    = mb_strtoupper($v[1]);
                    $boolean = isset($v[3]) ? $v[3] : 'and';
                    if (in_array($v[1], ['=', '!=', '<', '<=', '>', '>=', 'LIKE', 'NOT LIKE'])) {
                        $model = $model->where($v[0], $v[1], $v[2], $boolean);
                    } elseif ($v[1] == 'IN') {
                        $model = $model->whereIn($v[0], $v[2], $boolean);
                    } elseif ($v[1] == 'NOT IN') {
                        $model = $model->whereNotIn($v[0], $v[2], $boolean);
                    } elseif ($v[1] == 'RAW') {
                        $model = $model->whereRaw($v[0], $v[2], $boolean);
                    }
                } else {
                    ## 二维关联数组
                    $model = $model->whereIn($k, $v);
                }
            }
        }

        ## 排序
        isset($options['order']) && $model = $model->orderByRaw($options['order']);

        ## 限制集合
        isset($options['skip']) && $model = $model->skip($options['skip']);
        isset($options['take']) && $model = $model->take($options['take']);

        return $model;
    }
}