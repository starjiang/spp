<?php
namespace spp\model;
interface CMapper
{
    public function findByPk($id, $columns);

    public function where($column, $op, $value, $bool);

    public function limit($count, $offset);

    public function orderBy($column, $order);

    public function find($columns);

    public function findOne($columns);

    public function count();

    public function max($column);

    public function min($column);

    public function sum($column);

    public function distinct($column);

    public function whereIn($column, $values, $bool);

    public function whereNotIn($column, $values, $bool);

    public function insert($obj);

    public function update($obj);

    public function save($obj);

    public function updateByPk($obj);

    public function saveByPk($obj);

    public function deleteByPk($id);

    public function delete();

}

