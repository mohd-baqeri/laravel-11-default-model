<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DefaultModel extends Model
{
    /**
     * addRow
     * updateRow
     * deleteRow
     * getRows
     * getRowsIn
     * getRowsInSearch
     * getRowsNotIn
     * getRowsNotInSearch
     * getRowsJoin
     * getRowsSearch
     * getRowSearch
     * getRowsSearchJoin
     * getDistinctRows
     * getDistinctRowsSearch
     * getRow
     * getRowIn
     * getRowNotIn
     * getNextRow
     * getNextRows
     * getPrevRow
     * getPrevRows
     * getFirstRow
     * getLastRow
     * getRowMath
     * getRowMathSearch
     * getRowJoin
     */

    //  addRow
    public function addRow($tbl, $data)
    {
        $id = DB::table($tbl)->insertGetId($data);
        return $id;
    }

    // updateRow
    public function updateRow($tbl, $data, $where = [])
    {
        $affected = DB::table($tbl);
        foreach ($where as $where_collumn => $where_value) {
            $where_collumns = explode(' ', $where_collumn);
            if (count($where_collumns) == 2)
                $affected->where($where_collumns[0], $where_collumns[1], $where_value);
            else
                $affected->where($where_collumn, $where_value);
        }
        $affected->update($data);
        return $affected;
    }

    // deleteRow
    public function deleteRow($tbl, $where = [], $status = 'delete')
    {
        // update the updated_at COLLUMN for specifying the 'delete time'
        if ($status == 'update') {
            $query = DB::table($tbl);

            foreach ($where as $where_collumn => $where_value) {
                $where_collumns = explode(' ', $where_collumn);
                if (count($where_collumns) == 2)
                    $query->where($where_collumns[0], $where_collumns[1], $where_value);
                else
                    $query->where($where_collumn, $where_value);
            }

            $query->update([
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            return 'updated';
        }

        // move the row to another table,
        if ($status == 'move') {
            $selectQuery = DB::table($tbl);

            foreach ($where as $where_collumn => $where_value) {
                $where_collumns = explode(' ', $where_collumn);
                if (count($where_collumns) == 2)
                    $selectQuery->where($where_collumns[0], $where_collumns[1], $where_value);
                else
                    $selectQuery->where($where_collumn, $where_value);
            }

            DB::insert("INSERT INTO " . $tbl . "_deleted " . $selectQuery->toRawSql());
            return 'inserted'; // get the last inserted id
        }

        // and then delete it
        if ($status == 'delete') {
            $query = DB::table($tbl);
            foreach ($where as $where_collumn => $where_value) {
                $where_collumns = explode(' ', $where_collumn);
                if (count($where_collumns) == 2)
                    $query->where($where_collumns[0], $where_collumns[1], $where_value);
                else
                    $query->where($where_collumn, $where_value);
            }
            $query->delete();
            return 'deleted';
        }
    }

    // getRows
    public function getRows($tbl, $where = [], $orderBy = 'id ASC', $limit = false, $offset = false)
    {
        $query = DB::table($tbl);

        foreach ($where as $where_collumn => $where_value) {
            $where_collumns = explode(' ', $where_collumn);
            if (count($where_collumns) == 2)
                $query->where($where_collumns[0], $where_collumns[1], $where_value);
            else
                $query->where($where_collumn, $where_value);
        }

        if (is_array($orderBy)) {
            foreach ($orderBy as $orderByItem) {
                $ordered = explode(' ', $orderByItem);
                count($ordered) > 1
                    ? $query->orderBy($ordered[0], $ordered[1])
                    : ($orderByItem
                        ? $query->orderBy($ordered[0])
                        : '');
            }
        } else {
            $ordered = explode(' ', $orderBy);
            count($ordered) > 1
                ? $query->orderBy($ordered[0], $ordered[1])
                : ($orderBy
                    ? $query->orderBy($ordered[0])
                    : '');
        }

        if ($limit && !$offset)
            $query->limit($limit);
        if ($limit && $offset) {
            $query->limit($limit);
            $query->offset($offset);
        }

        return $query->get();
    }

    // getRowsIn
    public function getRowsIn($tbl, $whereInCol, $whereInVal, $where = [], $orderBy = 'id ASC', $limit = false, $offset = false)
    {
        $query = DB::table($tbl);

        foreach ($where as $where_collumn => $where_value) {
            $where_collumns = explode(' ', $where_collumn);
            if (count($where_collumns) == 2)
                $query->where($where_collumns[0], $where_collumns[1], $where_value);
            else
                $query->where($where_collumn, $where_value);
        }

        is_array($whereInVal)
            ? $query->whereIn($whereInCol, $whereInVal)
            : $query->whereIn($whereInCol, [$whereInVal]);

        if (is_array($orderBy)) {
            foreach ($orderBy as $orderByItem) {
                $ordered = explode(' ', $orderByItem);
                count($ordered) > 1
                    ? $query->orderBy($ordered[0], $ordered[1])
                    : ($orderByItem
                        ? $query->orderBy($ordered[0])
                        : '');
            }
        } else {
            $ordered = explode(' ', $orderBy);
            count($ordered) > 1
                ? $query->orderBy($ordered[0], $ordered[1])
                : ($orderBy
                    ? $query->orderBy($ordered[0])
                    : '');
        }

        if ($limit && !$offset)
            $query->limit($limit);
        if ($limit && $offset) {
            $query->limit($limit);
            $query->offset($offset);
        }

        return $query->get();
    }

    // getRowsInSearch
    public function getRowsInSearch($tbl, $whereInCol, $whereInVal, $like = [], $where = [], $orderBy = 'id ASC', $limit = false, $offset = false)
    {
        $query = DB::table($tbl);

        foreach ($where as $where_collumn => $where_value) {
            $where_collumns = explode(' ', $where_collumn);
            if (count($where_collumns) == 2)
                $query->where($where_collumns[0], $where_collumns[1], $where_value);
            else
                $query->where($where_collumn, $where_value);
        }

        foreach ($like as $like_column => $like_value) {
            $query->whereLike($like_column, $like_value);
        }

        is_array($whereInVal)
            ? $query->whereIn($whereInCol, $whereInVal)
            : $query->whereIn($whereInCol, [$whereInVal]);

        if (is_array($orderBy)) {
            foreach ($orderBy as $orderByItem) {
                $ordered = explode(' ', $orderByItem);
                count($ordered) > 1
                    ? $query->orderBy($ordered[0], $ordered[1])
                    : ($orderByItem
                        ? $query->orderBy($ordered[0])
                        : '');
            }
        } else {
            $ordered = explode(' ', $orderBy);
            count($ordered) > 1
                ? $query->orderBy($ordered[0], $ordered[1])
                : ($orderBy
                    ? $query->orderBy($ordered[0])
                    : '');
        }

        if ($limit && !$offset)
            $query->limit($limit);
        if ($limit && $offset) {
            $query->limit($limit);
            $query->offset($offset);
        }

        return $query->get();
    }

    // getRowsNotIn
    public function getRowsNotIn($tbl, $whereNotInCol, $whereNotInVal, $where = [], $orderBy = 'id ASC', $limit = false, $offset = false)
    {
        $query = DB::table($tbl);

        foreach ($where as $where_collumn => $where_value) {
            $where_collumns = explode(' ', $where_collumn);
            if (count($where_collumns) == 2)
                $query->where($where_collumns[0], $where_collumns[1], $where_value);
            else
                $query->where($where_collumn, $where_value);
        }

        is_array($whereNotInVal)
            ? $query->whereNotIn($whereNotInCol, $whereNotInVal)
            : $query->whereNotIn($whereNotInCol, [$whereNotInVal]);

        if (is_array($orderBy)) {
            foreach ($orderBy as $orderByItem) {
                $ordered = explode(' ', $orderByItem);
                count($ordered) > 1
                    ? $query->orderBy($ordered[0], $ordered[1])
                    : ($orderByItem
                        ? $query->orderBy($ordered[0])
                        : '');
            }
        } else {
            $ordered = explode(' ', $orderBy);
            count($ordered) > 1
                ? $query->orderBy($ordered[0], $ordered[1])
                : ($orderBy
                    ? $query->orderBy($ordered[0])
                    : '');
        }

        if ($limit && !$offset)
            $query->limit($limit);
        if ($limit && $offset) {
            $query->limit($limit);
            $query->offset($offset);
        }

        return $query->get();
    }

    // getRowsNotInSearch
    public function getRowsNotInSearch($tbl, $whereNotInCol, $whereNotInVal, $like = [], $where = [], $orderBy = 'id ASC', $limit = false, $offset = false)
    {
        $query = DB::table($tbl);

        foreach ($where as $where_collumn => $where_value) {
            $where_collumns = explode(' ', $where_collumn);
            if (count($where_collumns) == 2)
                $query->where($where_collumns[0], $where_collumns[1], $where_value);
            else
                $query->where($where_collumn, $where_value);
        }

        foreach ($like as $like_column => $like_value) {
            $query->whereLike($like_column, $like_value);
        }

        is_array($whereNotInVal)
            ? $query->whereNotIn($whereNotInCol, $whereNotInVal)
            : $query->whereNotIn($whereNotInCol, [$whereNotInVal]);

        if (is_array($orderBy)) {
            foreach ($orderBy as $orderByItem) {
                $ordered = explode(' ', $orderByItem);
                count($ordered) > 1
                    ? $query->orderBy($ordered[0], $ordered[1])
                    : ($orderByItem
                        ? $query->orderBy($ordered[0])
                        : '');
            }
        } else {
            $ordered = explode(' ', $orderBy);
            count($ordered) > 1
                ? $query->orderBy($ordered[0], $ordered[1])
                : ($orderBy
                    ? $query->orderBy($ordered[0])
                    : '');
        }

        if ($limit && !$offset)
            $query->limit($limit);
        if ($limit && $offset) {
            $query->limit($limit);
            $query->offset($offset);
        }

        return $query->get();
    }

    // getRowsJoin
    public function getRowsJoin($tbl1, $tbl2, $onClause, $select = '*', $where = [], $orderBy = 'id ASC', $limit = false, $offset = false)
    {
        $query = DB::table($tbl1)->select($select);

        foreach ($where as $where_collumn => $where_value) {
            $where_collumns = explode(' ', $where_collumn);
            if (count($where_collumns) == 2)
                $query->where($where_collumns[0], $where_collumns[1], $where_value);
            else
                $query->where($where_collumn, $where_value);
        }

        $onClauses = explode('=', $onClause);

        $query->join($tbl2, $onClauses[0], '=', $onClauses[1]);

        if (is_array($orderBy)) {
            foreach ($orderBy as $orderByItem) {
                $ordered = explode(' ', $orderByItem);
                count($ordered) > 1
                    ? $query->orderBy($ordered[0], $ordered[1])
                    : ($orderByItem
                        ? $query->orderBy($ordered[0])
                        : '');
            }
        } else {
            $ordered = explode(' ', $orderBy);
            count($ordered) > 1
                ? $query->orderBy($ordered[0], $ordered[1])
                : ($orderBy
                    ? $query->orderBy($ordered[0])
                    : '');
        }

        if ($limit && !$offset)
            $query->limit($limit);
        if ($limit && $offset) {
            $query->limit($limit);
            $query->offset($offset);
        }

        return $query->get();
    }

    // getRowsSearch
    public function getRowsSearch($tbl, $like = [], $where = [], $orderBy = 'id ASC', $limit = false, $offset = false)
    {
        $query = DB::table($tbl);

        foreach ($where as $where_collumn => $where_value) {
            $where_collumns = explode(' ', $where_collumn);
            if (count($where_collumns) == 2)
                $query->where($where_collumns[0], $where_collumns[1], $where_value);
            else
                $query->where($where_collumn, $where_value);
        }

        foreach ($like as $like_column => $like_value) {
            $query->whereLike($like_column, $like_value);
        }

        if (is_array($orderBy)) {
            foreach ($orderBy as $orderByItem) {
                $ordered = explode(' ', $orderByItem);
                count($ordered) > 1
                    ? $query->orderBy($ordered[0], $ordered[1])
                    : ($orderByItem
                        ? $query->orderBy($ordered[0])
                        : '');
            }
        } else {
            $ordered = explode(' ', $orderBy);
            count($ordered) > 1
                ? $query->orderBy($ordered[0], $ordered[1])
                : ($orderBy
                    ? $query->orderBy($ordered[0])
                    : '');
        }

        if ($limit && !$offset)
            $query->limit($limit);
        if ($limit && $offset) {
            $query->limit($limit);
            $query->offset($offset);
        }

        return $query->get();
    }

    // getRowSearch
    public function getRowSearch($tbl, $like = [], $where = [], $orderBy = 'id ASC', $limit = false, $offset = false)
    {
        $query = DB::table($tbl);

        foreach ($where as $where_collumn => $where_value) {
            $where_collumns = explode(' ', $where_collumn);
            if (count($where_collumns) == 2)
                $query->where($where_collumns[0], $where_collumns[1], $where_value);
            else
                $query->where($where_collumn, $where_value);
        }

        foreach ($like as $like_column => $like_value) {
            $query->whereLike($like_column, $like_value);
        }

        if (is_array($orderBy)) {
            foreach ($orderBy as $orderByItem) {
                $ordered = explode(' ', $orderByItem);
                count($ordered) > 1
                    ? $query->orderBy($ordered[0], $ordered[1])
                    : ($orderByItem
                        ? $query->orderBy($ordered[0])
                        : '');
            }
        } else {
            $ordered = explode(' ', $orderBy);
            count($ordered) > 1
                ? $query->orderBy($ordered[0], $ordered[1])
                : ($orderBy
                    ? $query->orderBy($ordered[0])
                    : '');
        }

        if ($limit && !$offset)
            $query->limit($limit);
        if ($limit && $offset) {
            $query->limit($limit);
            $query->offset($offset);
        }

        return $query->first();
    }

    // getRowsSearchJoin
    public function getRowsSearchJoin($tbl1, $tbl2, $onClause, $select = '*', $like = [], $where = [], $orderBy = 'id ASC', $limit = false, $offset = false)
    {
        $query = DB::table($tbl1)->select($select);

        foreach ($where as $where_collumn => $where_value) {
            $where_collumns = explode(' ', $where_collumn);
            if (count($where_collumns) == 2)
                $query->where($where_collumns[0], $where_collumns[1], $where_value);
            else
                $query->where($where_collumn, $where_value);
        }

        foreach ($like as $like_column => $like_value) {
            $query->whereLike($like_column, $like_value);
        }

        $onClauses = explode('=', $onClause);

        $query->join($tbl2, $onClauses[0], '=', $onClauses[1]);

        if (is_array($orderBy)) {
            foreach ($orderBy as $orderByItem) {
                $ordered = explode(' ', $orderByItem);
                count($ordered) > 1
                    ? $query->orderBy($ordered[0], $ordered[1])
                    : ($orderByItem
                        ? $query->orderBy($ordered[0])
                        : '');
            }
        } else {
            $ordered = explode(' ', $orderBy);
            count($ordered) > 1
                ? $query->orderBy($ordered[0], $ordered[1])
                : ($orderBy
                    ? $query->orderBy($ordered[0])
                    : '');
        }

        if ($limit && !$offset)
            $query->limit($limit);
        if ($limit && $offset) {
            $query->limit($limit);
            $query->offset($offset);
        }

        return $query->get();
    }

    // getDistinctRows // search about distinct
    public function getDistinctRows($tbl, $distinct_col, $where = [], $orderBy = NULL, $limit = false, $offset = false)
    {
        $query = DB::table($tbl)->distinct($distinct_col);

        foreach ($where as $where_collumn => $where_value) {
            $where_collumns = explode(' ', $where_collumn);
            if (count($where_collumns) == 2)
                $query->where($where_collumns[0], $where_collumns[1], $where_value);
            else
                $query->where($where_collumn, $where_value);
        }

        if (is_array($orderBy)) {
            foreach ($orderBy as $orderByItem) {
                $ordered = explode(' ', $orderByItem);
                count($ordered) > 1
                    ? $query->orderBy($ordered[0], $ordered[1])
                    : ($orderByItem
                        ? $query->orderBy($ordered[0])
                        : '');
            }
        } else {
            $ordered = explode(' ', $orderBy);
            count($ordered) > 1
                ? $query->orderBy($ordered[0], $ordered[1])
                : ($orderBy
                    ? $query->orderBy($ordered[0])
                    : '');
        }

        if ($limit && !$offset)
            $query->limit($limit);
        if ($limit && $offset) {
            $query->limit($limit);
            $query->offset($offset);
        }

        return $query->get();
    }

    // getDistinctRowsSearch
    public function getDistinctRowsSearch($tbl, $distinct_col, $like = [], $where = [], $orderBy = NULL, $limit = false, $offset = false)
    {
        $query = DB::table($tbl)->distinct($distinct_col);

        foreach ($where as $where_collumn => $where_value) {
            $where_collumns = explode(' ', $where_collumn);
            if (count($where_collumns) == 2)
                $query->where($where_collumns[0], $where_collumns[1], $where_value);
            else
                $query->where($where_collumn, $where_value);
        }

        foreach ($like as $like_column => $like_value) {
            $query->whereLike($like_column, $like_value);
        }

        if (is_array($orderBy)) {
            foreach ($orderBy as $orderByItem) {
                $ordered = explode(' ', $orderByItem);
                count($ordered) > 1
                    ? $query->orderBy($ordered[0], $ordered[1])
                    : ($orderByItem
                        ? $query->orderBy($ordered[0])
                        : '');
            }
        } else {
            $ordered = explode(' ', $orderBy);
            count($ordered) > 1
                ? $query->orderBy($ordered[0], $ordered[1])
                : ($orderBy
                    ? $query->orderBy($ordered[0])
                    : '');
        }

        if ($limit && !$offset)
            $query->limit($limit);
        if ($limit && $offset) {
            $query->limit($limit);
            $query->offset($offset);
        }

        return $query->get();
    }

    // getRow
    public function getRow($tbl, $where = [], $orderBy = NULL)
    {
        $query = DB::table($tbl);

        foreach ($where as $where_collumn => $where_value) {
            $where_collumns = explode(' ', $where_collumn);
            if (count($where_collumns) == 2)
                $query->where($where_collumns[0], $where_collumns[1], $where_value);
            else
                $query->where($where_collumn, $where_value);
        }

        if (is_array($orderBy)) {
            foreach ($orderBy as $orderByItem) {
                $ordered = explode(' ', $orderByItem);
                count($ordered) > 1
                    ? $query->orderBy($ordered[0], $ordered[1])
                    : ($orderByItem
                        ? $query->orderBy($ordered[0])
                        : '');
            }
        } else {
            $ordered = explode(' ', $orderBy);
            count($ordered) > 1
                ? $query->orderBy($ordered[0], $ordered[1])
                : ($orderBy
                    ? $query->orderBy($ordered[0])
                    : '');
        }

        return $query->first();
    }

    // getRowIn
    public function getRowIn($tbl, $whereInCol, $whereInVal, $where = [])
    {
        $query = DB::table($tbl);

        foreach ($where as $where_collumn => $where_value) {
            $where_collumns = explode(' ', $where_collumn);
            if (count($where_collumns) == 2)
                $query->where($where_collumns[0], $where_collumns[1], $where_value);
            else
                $query->where($where_collumn, $where_value);
        }

        is_array($whereInVal)
            ? $query->whereIn($whereInCol, $whereInVal)
            : $query->whereIn($whereInCol, [$whereInVal]);

        return $query->first();
    }

    // getRowNotIn
    public function getRowNotIn($tbl, $whereNotInCol, $whereNotInVal, $where = [])
    {
        $query = DB::table($tbl);

        foreach ($where as $where_collumn => $where_value) {
            $where_collumns = explode(' ', $where_collumn);
            if (count($where_collumns) == 2)
                $query->where($where_collumns[0], $where_collumns[1], $where_value);
            else
                $query->where($where_collumn, $where_value);
        }

        is_array($whereNotInVal)
            ? $query->whereNotIn($whereNotInCol, $whereNotInVal)
            : $query->whereNotIn($whereNotInCol, [$whereNotInVal]);

        return $query->first();
    }

    // getNextRow
    public function getNextRow($tbl, $current_col_name, $current_col_val, $where = [])
    {
        $query = DB::table($tbl);

        foreach ($where as $where_collumn => $where_value) {
            $where_collumns = explode(' ', $where_collumn);
            if (count($where_collumns) == 2)
                $query->where($where_collumns[0], $where_collumns[1], $where_value);
            else
                $query->where($where_collumn, $where_value);
        }

        $query->where($current_col_name, '>', $current_col_val);
        $query->orderBy($current_col_name, 'ASC');

        return $query->first();
    }

    // getNextRows
    public function getNextRows($tbl, $current_col_name, $current_col_val, $where = [], $limit = false, $offset = false)
    {
        $query = DB::table($tbl);

        foreach ($where as $where_collumn => $where_value) {
            $where_collumns = explode(' ', $where_collumn);
            if (count($where_collumns) == 2)
                $query->where($where_collumns[0], $where_collumns[1], $where_value);
            else
                $query->where($where_collumn, $where_value);
        }

        if (strpos($current_col_name, ' ')) {
            $current_col_name_arr = explode(' ', $current_col_name);
            $query->where($current_col_name_arr[0], $current_col_name_arr[1], $current_col_val);
            $query->orderBy($current_col_name_arr[0], 'ASC');
        } else {
            $query->where($current_col_name, '>', $current_col_val)
                ->orderBy($current_col_name, 'ASC');
        }

        if ($limit && !$offset)
            $query->limit($limit);
        if ($limit && $offset) {
            $query->limit($limit);
            $query->offset($offset);
        }

        return $query->get();
    }

    // getPrevRow
    public function getPrevRow($tbl, $current_col_name, $current_col_val, $where = [])
    {
        $query = DB::table($tbl);

        foreach ($where as $where_collumn => $where_value) {
            $where_collumns = explode(' ', $where_collumn);
            if (count($where_collumns) == 2)
                $query->where($where_collumns[0], $where_collumns[1], $where_value);
            else
                $query->where($where_collumn, $where_value);
        }

        $query->where($current_col_name, '<', $current_col_val);
        $query->orderBy($current_col_name, 'DESC');

        return $query->first();
    }

    // getPrevRows
    public function getPrevRows($tbl, $current_col_name, $current_col_val, $where = [], $limit = false, $offset = false)
    {
        $query = DB::table($tbl);

        foreach ($where as $where_collumn => $where_value) {
            $where_collumns = explode(' ', $where_collumn);
            if (count($where_collumns) == 2)
                $query->where($where_collumns[0], $where_collumns[1], $where_value);
            else
                $query->where($where_collumn, $where_value);
        }

        if (strpos($current_col_name, ' ')) {
            $current_col_name_arr = explode(' ', $current_col_name);
            $query->where($current_col_name_arr[0], $current_col_name_arr[1], $current_col_val);
            $query->orderBy($current_col_name_arr[0], 'DESC');
        } else {
            $query->where($current_col_name, '<=', $current_col_val)
                ->orderBy($current_col_name, 'DESC');
        }

        if ($limit && !$offset)
            $query->limit($limit);
        if ($limit && $offset) {
            $query->limit($limit);
            $query->offset($offset);
        }

        return $query->get();
    }

    // getFirstRow
    public function getFirstRow($tbl, $where = [], $orderBy = 'id ASC')
    {
        $query = DB::table($tbl);

        foreach ($where as $where_collumn => $where_value) {
            $where_collumns = explode(' ', $where_collumn);
            if (count($where_collumns) == 2)
                $query->where($where_collumns[0], $where_collumns[1], $where_value);
            else
                $query->where($where_collumn, $where_value);
        }

        if (is_array($orderBy)) {
            foreach ($orderBy as $orderByItem) {
                $ordered = explode(' ', $orderByItem);
                count($ordered) > 1
                    ? $query->orderBy($ordered[0], $ordered[1])
                    : ($orderByItem
                        ? $query->orderBy($ordered[0])
                        : '');
            }
        } else {
            $ordered = explode(' ', $orderBy);
            count($ordered) > 1
                ? $query->orderBy($ordered[0], $ordered[1])
                : ($orderBy
                    ? $query->orderBy($ordered[0])
                    : '');
        }

        $query->limit(1);

        return $query->first();
    }

    // getLastRow
    public function getLastRow($tbl, $where = [], $orderBy = 'id DESC')
    {
        $query = DB::table($tbl);

        foreach ($where as $where_collumn => $where_value) {
            $where_collumns = explode(' ', $where_collumn);
            if (count($where_collumns) == 2)
                $query->where($where_collumns[0], $where_collumns[1], $where_value);
            else
                $query->where($where_collumn, $where_value);
        }

        if (is_array($orderBy)) {
            foreach ($orderBy as $orderByItem) {
                $ordered = explode(' ', $orderByItem);
                count($ordered) > 1
                    ? $query->orderBy($ordered[0], $ordered[1])
                    : ($orderByItem
                        ? $query->orderBy($ordered[0])
                        : '');
            }
        } else {
            $ordered = explode(' ', $orderBy);
            count($ordered) > 1
                ? $query->orderBy($ordered[0], $ordered[1])
                : ($orderBy
                    ? $query->orderBy($ordered[0])
                    : '');
        }

        $query->limit(1);

        return $query->first();
    }

    // getRowMath
    public function getRowMath($tbl, $math = 'SUM', $col = 'id', $where = [])
    {
        $query = DB::table($tbl);

        foreach ($where as $where_collumn => $where_value) {
            $where_collumns = explode(' ', $where_collumn);
            if (count($where_collumns) == 2)
                $query->where($where_collumns[0], $where_collumns[1], $where_value);
            else
                $query->where($where_collumn, $where_value);
        }

        if ($math == 'AVG')
            return $query->avg($col);
        if ($math == 'COUNT')
            return $query->count($col);
        if ($math == 'MAX')
            return $query->max($col);
        if ($math == 'MIN')
            return $query->min($col);
        if ($math == 'SUM')
            return $query->sum($col);
    }

    // getRowMathSearch
    public function getRowMathSearch($tbl, $math = 'SUM', $col = 'id', $like = [], $where = [])
    {
        $query = DB::table($tbl);

        foreach ($where as $where_collumn => $where_value) {
            $where_collumns = explode(' ', $where_collumn);
            if (count($where_collumns) == 2)
                $query->where($where_collumns[0], $where_collumns[1], $where_value);
            else
                $query->where($where_collumn, $where_value);
        }

        foreach ($like as $like_column => $like_value) {
            $query->whereLike($like_column, $like_value);
        }

        if ($math == 'AVG')
            return $query->average($col);
        if ($math == 'COUNT')
            return $query->count($col);
        if ($math == 'MAX')
            return $query->max($col);
        if ($math == 'MIN')
            return $query->min($col);
        if ($math == 'SUM')
            return $query->sum($col);
    }

    // getRowJoin
    public function getRowJoin($tbl1, $tbl2, $onClause, $select = '*', $where = [], $orderBy = '')
    {
        $query = DB::table($tbl1)->select($select);

        foreach ($where as $where_collumn => $where_value) {
            $where_collumns = explode(' ', $where_collumn);
            if (count($where_collumns) == 2)
                $query->where($where_collumns[0], $where_collumns[1], $where_value);
            else
                $query->where($where_collumn, $where_value);
        }

        $onClauses = explode('=', $onClause);

        $query->join($tbl2, $onClauses[0], '=', $onClauses[1]);

        if (is_array($orderBy)) {
            foreach ($orderBy as $orderByItem) {
                $ordered = explode(' ', $orderByItem);
                count($ordered) > 1
                    ? $query->orderBy($ordered[0], $ordered[1])
                    : ($orderByItem
                        ? $query->orderBy($ordered[0])
                        : '');
            }
        } else {
            $ordered = explode(' ', $orderBy);
            count($ordered) > 1
                ? $query->orderBy($ordered[0], $ordered[1])
                : ($orderBy
                    ? $query->orderBy($ordered[0])
                    : '');
        }

        return $query->first();
    }
}
