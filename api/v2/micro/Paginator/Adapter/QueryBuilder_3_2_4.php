<?php
namespace Micro\Paginator\Adapter;

class QueryBuilder extends \Phalcon\Paginator\Adapter\QueryBuilder {

    public function getConnectionType() {
        return $this->getConnection()->getType();
    }

    public function getConnection() {
        static $conn;

        if (is_null($conn)) {
            $builder = $this->_builder;
            $modelClass = $builder->getFrom();

            if (is_array($modelClass)) {
                $modelClass = $modelClass[0];
            }
            
            $model = new $modelClass();
            $connService = $model->getReadConnectionService();
            $conn = $builder->getDI()->get($connService);
        }

        return $conn;
    }

    public function getPaginate() {

        $originalBuilder = $this->_builder;
        $columns = $this->_columns;

        $builder = clone $originalBuilder;
        $totalBuilder = clone $builder;

        $limit = $this->_limitRows;
        $numberPage = (int) $this->_page;

        if ( ! $numberPage) {
            $numberPage = 1;
        }

        $number = $limit * ($numberPage - 1);

        if ($number < $limit) {
            $builder->limit($limit);
        } else {
            $builder->limit($limit, $number);
        }

        $query = $builder->getQuery();

        if ($numberPage == 1) {
            $before = 1;
        } else {
            $before = $numberPage - 1;
        }

        $items = $query->execute();

        $hasHaving = ( ! empty($totalBuilder->getHaving()));

        $groups = $totalBuilder->getGroupBy();

        $hasGroup = ( ! empty($groups));

        if ($hasHaving && ! $hasGroup) {
            if (empty($columns)) {
                throw new \Exception("When having is set there should be columns option provided for which calculate row count");
            }
            $totalBuilder->columns($columns);
        } else {
            $totalBuilder->columns("COUNT(*) [rowcount]");
        }

        if ($hasGroup) {
            if (is_array($groups)) {
                $groupColumn = implode(", ", $groups);
            } else {
                $groupColumn = $groups;
            }

            if ( ! $hasHaving) {
                $totalBuilder->groupBy(null)->columns(["COUNT(DISTINCT ".$groupColumn.") AS [rowcount]"]);
            } else {
                $totalBuilder->columns(["DISTINCT ".$groupColumn]);
            }
        }

        $totalBuilder->orderBy(null);

        $totalQuery = $totalBuilder->getQuery();

        if ($hasHaving) {
            $sql = $totalQuery->getSql();
            $con = $this->getConnection();
            $row = $con->fetchOne("SELECT COUNT(*) as \"rowcount\" FROM (" .  $sql["sql"] . ") as T1", \Phalcon\Db::FETCH_ASSOC, $sql["bind"]);
            $rowcount = $row ? intval($row["rowcount"]) : 0;
            $totalPages = intval(ceil($rowcount / $limit));
        } else {
            if ($this->getConnectionType() == 'pgsql') {
                $con = $this->getConnection();
                $sql = $totalQuery->getSql();
                $fix = preg_replace('#DISTINCT\s([^)]+)#', 'DISTINCT ($1)', $sql["sql"]);
                $row = $con->fetchOne($fix, \Phalcon\Db::FETCH_ASSOC, $sql["bind"], $sql["bindTypes"]);
                $rowcount = $row ? intval($row["rowcount"]) : 0;
                $totalPages = intval(ceil($rowcount / $limit));
            } else {
                $result = $totalQuery->execute();
                $row = $result->getFirst();
                $rowcount = $row ? intval($row->rowcount) : 0;
                $totalPages = intval(ceil($rowcount / $limit));  
            }
        }

        if ($numberPage < $totalPages) {
            $next = $numberPage + 1;
        } else {
            $next = $totalPages;
        }

        $page = new \stdClass();
        $page->items = $items;
        $page->first = 1;
        $page->before = $before;
        $page->current = $numberPage;
        $page->last = $totalPages;
        $page->next = $next;
        $page->total_pages = $totalPages;
        $page->total_items = $rowcount;
        $page->limit = $this->_limitRows;

        return $page;
    }


}