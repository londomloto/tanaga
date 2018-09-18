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
                $modelClass = array_values($modelClass);
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

        // print_r($query->getSql());
        // exit();

        $items = $query->execute();

        $totalBuilder->columns("COUNT(*) [rowcount]");

        $groups = $totalBuilder->getGroupBy();
        
        if ( ! empty($groups)) {
            if (is_array($groups)) {
                $groupColumn = implode(", ", $groups);
            } else {
                $groupColumn = $groups;
            }
            $totalBuilder->groupBy(null)->columns(["COUNT(DISTINCT ".$groupColumn.") AS rowcount"]);
        }

        $totalBuilder->orderBy(null);

        if ($this->getConnectionType() == 'pgsql') {
            $sql = $totalBuilder->getQuery()->getSql();
            $fix = preg_replace('#DISTINCT\s([^)]+)#', 'DISTINCT ($1)', $sql['sql']);
            $con = $this->getConnection();
            $row = $con->fetchOne($fix, \Phalcon\Db::FETCH_ASSOC, $sql['bind'], $sql['bindTypes']);
            
            $rowcount = $row ? intval($row['rowcount']) : 0;
            $totalPages = intval(ceil($rowcount / $limit));
        } else {
            $totalQuery = $totalBuilder->getQuery();
            $result = $totalQuery->execute();
            $row = $result->getFirst();
            $rowcount = $row ? intval($row->rowcount) : 0;
            $totalPages = intval(ceil($rowcount / $limit));
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