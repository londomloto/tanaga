<?php
namespace Micro\Dx;

class DxResult {

    public $success = FALSE;
    public $data;

    private $__start = 0;
    private $__stop  = 0;

    public function __construct() {
        $this->reset();
    }

    public function reset() {
        $this->data = array(
            'messages' => array(),
            'summaries' => array(),
            'transaction' => array(
                'creates' => 0,
                'created' => 0,
                'updates' => 0,
                'updated' => 0,
                'ignored' => 0
            ),
            "benchmarks" => array(
                "begin" => NULL,
                "end" => NULL,
                "time" => NULL
            )
        );
    }

    public function start() {
        $this->__start = microtime(TRUE);
        $this->data['benchmarks']['begin'] = date('Y-m-d H:i:s');
    }

    public function stop() {
        $this->__stop = microtime(TRUE);
        $this->data['benchmarks']['end'] = date('Y-m-d H:i:s');
        $this->data['benchmarks']['time'] = ($this->__stop - $this->__start);
    }

    public function init($table) {
        if ( ! isset($this->data['summaries'][$table])) {
            $this->data['summaries'][$table] = array(
                'missing_primary_keys' => FALSE,
                'missing_secondary_keys' => FALSE,
                'missing_columns' => array(),
                'creates' => 0,
                'created' => 0,
                'updates' => 0,
                'updated' => 0,
                'ignored' => 0
            );
        }
    }

    public function sum($table, $info, $value, $summary = FALSE) {
        $this->init($table);
        $context =& $this->data['summaries'][$table];

        if ($summary) {
            if (is_numeric($context[$info])) {
                $context[$info] += $value;    
            } else {
                $context[$info][] = $value;
            }
        } else {
            $context[$info] = $value;    
        }
    }

    public function log($info, $value = 1) {
        $this->data['transaction'][$info] += $value;
    }
}