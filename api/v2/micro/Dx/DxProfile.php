<?php
namespace Micro\Dx;

use Micro\Helpers\Text,
    Micro\Helpers\UTF8;

class DxProfile {
    
    const ACTION_INSERT = 'insert';
    const ACTION_UPDATE = 'update';

    protected $_name;  
    protected $_provider;  
    protected $_profile;
    protected $_mapping;
    protected $_listeners;
    protected $_book;
    protected $_sheet;
    protected $_worksheet;
    protected $_content;
    protected $_columns;
    protected $_buffers;
    protected $_mindate = '1988-12-12';
    protected $_ignore_blank = FALSE;
    protected $_batch_limit = 200;
    protected $_progress  = FALSE;
    protected $_has_merge_cell = FALSE;

    public function __construct($name, $providers) {
        $this->_name = $name;
        $this->_providers = $providers;
        $this->_result = new DxResult();
        $this->_setup();
        $this->initialize();
    }

    protected function _setup() {
        // fetch profile
        $query = self::__run($this->_providers->profile, 'get');
        $query->where('profile_name = :name:', array('name' => $this->_name));

        $this->_profile = $query->execute()->getFirst();

        if ($this->_profile) {
            $query = self::__run($this->_providers->mapping, 'get');
            $query->where('map_profile_id = :profile:', array('profile' => $this->_profile->profile_id));
            $query->orderBy('map_grp_seq, map_tbl_col');

            $this->_mapping = $query->execute();
        }
    }

    public static function app() {
        return \Micro\App::getDefault();
    }

    public static function db() {
        return self::app()->db;
    }

    public function data() {
        return $this->_profile;
    }

    public function reset() {
        $this->_listeners = array();
        $this->_book = NULL;
        $this->_sheet = NULL;
        $this->_worksheet = 0;
        $this->_columns = array();
        $this->_buffers = array();
        $this->_result->reset();

        return $this;
    }

    public function initialize($options = array()) {
        $this->reset();

        foreach($options as $key => $val) {
            if ($key == 'listeners') {
                $this->on($val);
            } else {
                $this->{'_'.$key} = $val;
            }
        }

        if ($this->_progress) {
            @apache_setenv('no-gzip', 1);
            @ini_set('zlib.output_compression', 0);
            @ini_set('implicit_flush', 1); 
            
            for ($i = 0; $i < ob_get_level(); $i++) { 
                ob_end_flush(); 
            }

            ob_implicit_flush(1);
        }

        $this->fire('initialize');
        return $this;
    }

    public function on($type, $handler = NULL, $priority = 1500) {
        if (is_array($type)) {
            foreach($type as $k => $v) {
                if (is_array($v)) {
                    $this->on($k, $v['handler'], $v['priority']);
                } else {
                    $this->on($k, $v);    
                }
            }
            return $this;
        }

        if ( ! isset($this->_listeners[$type])) {
            $this->_listeners[$type] = array();
        }

        $this->_listeners[$type][] = array(
            'handler' => $handler,
            'priority' => $priority
        );

        return $this;
    }

    public function fire($type, Array $data = array()) {
        $listeners = isset($this->_listeners[$type]) ? $this->_listeners[$type] : array();

        usort($listeners, function($a, $b){
            $va = (int)$a['priority'];
            $vb = (int)$b['priority'];

            if ($va == $vb) return 0;
            return $va < $vb ? -1 : 1;
        });

        $event = new DxEvent($type, $data);

        foreach($listeners as $opts) {
            $handler = $opts['handler'];

            if ($handler instanceof \Closure) {
                $result = call_user_func_array($handler, array($event, $this));

                if (isset($result) && $result === FALSE) {
                    $event->preventDefault();
                }
            }
        }

        return $event;
    }

    public function upload($source = 'userfile') {
        $this->_result->start();

        $request = self::app()->request;
        $file = NULL;

        if ($request->hasFiles()) {
            foreach($request->getFiles() as $item) {
                if ($item->getKey() == $source) {
                    $file = $item;
                    break;
                }
            }
        }

        if (is_null($file)) {
            $this->_debug('Error: no file has been uploaded');
            return FALSE;
        }

        if ( ! $this->read($file)) {
            return FALSE;
        }

        $success = $this->save();
        $this->_result->stop();

        return $success;
    }

    public function read($file, $sheet = NULL) {
        static $process;

        $temp = NULL;
        $name = NULL;

        if (is_null($sheet)) {
            $sheet = $this->_worksheet;
        }

        if ($file instanceof \Phalcon\Http\Request\File) {
            $temp = $file->getTempName();
            $name = $file->getName();
        } else if (is_array($file) && isset($file['tmp_name'])) {
            $temp = $file['tmp_name'];
            $name = $file['name'];
        } else {
            $name = $file;
        }

        $current = md5($name.$sheet);

        if (is_null($process) || ( ! is_null($process) && $process != $current)) {
            $this->_book = new \ExcelBook('Said M Fahmi', 'linux-edd01c7891abac1006082d3240p0d7u5', TRUE);

            

            if ( ! $this->_book->loadFile($temp)) {
                $this->_debug(sprintf('Error: unable to read file %s', $name));
                return FALSE;
            } else {

                
                

                $this->_sheet = $this->_book->getSheet($sheet);

                

                if ( ! $this->_sheet) {
                    $this->_debug(sprintf('Error: unable to get worksheet %d', $sheet));
                    return FALSE;
                }
            }

            $columns = $this->_sheet->readRow(
                $this->_profile->header_row_idx - 1,
                0,
                $this->_sheet->lastCol() - 1
            );

            $maxcol = count($columns);
            $header = 'A';
            $number = NULL;

            $frow = $this->_profile->row_offset - 1;
            $lrow = $this->_sheet->lastRow() - 1;

            $this->_columns = array();
            $this->_buffers = array();

            

            if ($frow <= $lrow) {
                for ($i = 0; $i < $maxcol; $i++) {
                    if ($header == $this->_profile->col_offset) {
                        $number = $i;
                    }

                    if ( ! is_null($number)) {

                        if ((int)$this->_profile->map_header == 1) {
                            $column = strval(strtoupper(trim($columns[$i])));
                            $column = empty($column) ? 'COLUMN_'.$header : $column;
                        } else {
                            $column = $header;
                        }

                        if (isset($this->_columns[$column])) {
                            $column = $column.'_'.$header;
                        }

                        $this->_columns[$column] = array(
                            'offset' => $i,
                            'header' => $header
                        );

                        if ((int) $this->_profile->has_merge_cell == 1) {
                            for($n = $frow; $n <= $lrow; $n++) {
                                $merge = $this->_sheet->getMerge($n, $i);
                                if ($merge) {
                                    $value = $this->_sheet->read($merge['row_first'], $merge['col_first']);
                                } else {
                                    $value = $this->_sheet->read($n, $i);
                                }

                                if ($value === FALSE) {
                                    $value = '';
                                    $this->_debug(sprintf('Warning: unable to read data at %s%d (set to empty)', $column, ($n + 1)));
                                }

                                $this->_buffers[$column][] = $value;
                            }
                        } else {
                            $this->_buffers[$column] = $this->_sheet->readCol($i, $frow, $lrow);
                        }

                        if ($this->_buffers[$column] === FALSE) {
                            for ($n = $frow; $n <= $lrow; $n++) {
                                $value = $this->_sheet->read($n, $i);
                                if ($value === FALSE) {
                                    $value = '';
                                    $this->_debug(sprintf('Warning: unable to read data at %s%d (set to empty)', $column, ($n + 1)));
                                }
                                $this->_buffers[$column][] = $value;
                            }
                        }
                    }

                    $header++;
                }
            }
            $process = $current;

            
        }

        return TRUE;
    }

    public function save() {
        static $orphans = array();

        if ( ! $this->_profile) {
            $this->_debug(sprintf('Error: unable to load profile %s', $this->_name));
            return FALSE;
        }

        if (empty($this->_buffers)) {
            $this->_debug('Error: empty worksheet');
            return FALSE;
        }

        $setup = array(
            'valid_xls_cols'    => array(),
            'valid_map_cols'    => array(),
            'valid_map_type'    => array(),

            'map_tables'        => array(),
            'map_ids'           => array(),
            'map_profile_ids'   => array(),

            'pk_xls_cols'       => array(),
            'pk_map_cols'       => array(),

            'sk_xls_cols'       => array(),
            'sk_map_cols'       => array()
        );

        foreach($this->_mapping as $m) {
            if ( ! $m->map_active) continue;

            $table = $m->map_table;
            $xlscol = strtoupper($m->map_xls_col);
            
            if ( ! self::__resolveTable($table)) {
                $this->_debug(sprintf('Table `%s` does not exist!', $table));
                continue;
            }

            if (isset($this->_columns[$xlscol])) {
                $setup['valid_xls_cols'][$table][] = $xlscol;
                $setup['valid_map_cols'][$table][] = $m->map_tbl_col;
                $setup['valid_map_type'][$table][] = strtolower($m->map_dtype);

                $setup['map_tables'][] = $table;
                $setup['map_ids'][$table][] = $m->map_id;
                $setup['map_profile_ids'][$table][] = $m->map_profile_id;

                if ((int)$m->map_pk == 1) {
                    $setup['pk_map_cols'][$table][] = $m->map_tbl_col;
                    $setup['pk_xls_cols'][$table][] = $xlscol;
                }
                
                if ((int)$m->map_sk == 1) {
                    $setup['sk_map_cols'][$table][] = $m->map_tbl_col;
                    $setup['sk_xls_cols'][$table][] = $xlscol;
                }
            } else {
                $this->_result->sum($table, 'missing_columns', $xlscol, TRUE);
            }
        }

        if (count($setup['valid_map_cols']) == 0) {
            $this->_debug('Error: no valid columns mapping');
            return FALSE;
        }

        $tables = array_unique($setup['map_tables']);
        $stacks = array_filter($this->_buffers);
        $header = array_keys($this->_buffers);

        foreach($tables as $tab) {
            unset($pk_map_cols, $pk_xls_cols, $sk_map_cols, $sk_xls_cols);

            $sel_keys = array();

            $map_keys = array();
            $xls_keys = array();

            if (isset($setup['pk_map_cols'][$tab]) && count($setup['pk_map_cols'][$tab]) > 0) {
                $pk_map_cols = $setup['pk_map_cols'][$tab];
                $pk_xls_cols = $setup['pk_xls_cols'][$tab];

                $map_keys = array_merge($map_keys, $pk_map_cols);
                $xls_keys = array_merge($xls_keys, $pk_xls_cols);

                $sel_keys = array_merge($sel_keys, $pk_map_cols);

            } else {
                $this->_debug(sprintf('Warning: missing primary key definitions for table `%s`', $tab));
                $this->_result->sum($tab, 'missing_primary_keys', TRUE);
            }

            if (isset($setup['sk_map_cols'][$tab]) && count($setup['sk_map_cols'][$tab]) > 0) {
                $sk_map_cols = $setup['sk_map_cols'][$tab];
                $sk_xls_cols = $setup['sk_xls_cols'][$tab];

                $map_keys = array_merge($map_keys, $sk_map_cols);
                $xls_keys = array_merge($xls_keys, $sk_xls_cols);

                $sel_keys = array_merge($sel_keys, $sk_map_cols);
            } else {
                $this->_debug(sprintf('Warning: missing secondary key definitions for table `%s`', $tab));
                $this->_result->sum($tab, 'missing_secondary_keys', TRUE);
            }

            $hashes = array();

            if ( ! empty($sel_keys)) {

                $m_nums = count($map_keys);
                $x_nums = count($xls_keys);

                $query = self::__query('SELECT '.implode(',', $sel_keys).' FROM '.$tab);
                
                if ($query->numRows() > 0) {
                    foreach($query->fetchAll() as $idx => $row) {
                        $hash = array_reduce($map_keys, function($c, $k) use ($row){ return $c .= strval($row[$k]); }, '');
                        $hash = strtoupper($hash);
                        $hashes[$hash] = TRUE;
                    }
                }
            }   

            if (count($hashes) > 0) {
                // sampling
                $sample = count($this->_buffers[$header[0]]);

                $insert = array();
                $update = array();

                for ($i = 0; $i < $sample; $i++) {
                    $hash = array_reduce($xls_keys, function($c, $k) use($stacks, $i){ return $c .= $stacks[$k][$i]; }, '');
                    $hash = strtoupper($hash);

                    if ( ! empty($hash)) {
                        if (isset($hashes[$hash])) {
                            $update[] = $i;
                        } else {
                            $insert[] = $i;
                        }    
                    } else {
                        if ( ! isset($orphans[$i])) {
                            $orphans[$i] = TRUE;
                            $this->_debug('Warning: missing key values for row '.$i.' (transaction ignored)');
                            $this->_result->sum($tab, 'ignored', 1, TRUE);
                            $this->_result->log('ignored');
                        }
                    }
                }

                if (count($insert) > 0) {
                    $this->__store(self::ACTION_INSERT, $insert, $tab, $setup);
                }

                if (count($update) > 0) {
                    $this->__store(self::ACTION_UPDATE, $update, $tab, $setup, $map_keys);
                }

            } else {
                if (count($map_keys) > 0) {
                    $sample = count($this->_buffers[$header[0]]);
                    $insert = array();

                    for ($i = 0; $i < $sample; $i++) {
                        $hash = array_reduce($xls_keys, function($c, $k) use($stacks, $i){ return $c .= $stacks[$k][$i]; }, '');
                        $hash = strtoupper($hash);

                        if ( ! empty($hash)) {
                            $insert[] = $i;
                        } else {
                            if ( ! isset($orphans[$i])) {
                                $orphans[$i] = TRUE;
                                $this->_debug('Warning: missing key values for row '.$i);    
                            }
                        }
                    }

                    if (count($insert) > 0) {
                        $this->__store(self::ACTION_INSERT, $insert, $tab, $setup);
                    }
                } else {
                    $this->_debug('Warning: no data imported');
                }
            }
        }

        $this->_result->success = TRUE;
        return TRUE;
    }

    public function result() {
        return $this->_result;
    }

    public function _debug($message) {
        $this->_result->data['messages'][] = $message;
    }

    public function getMessages() {
        return $this->_result->messages;
    }

    public function download() {

    }

    private function __store($action, $lines, $table, $setup, $keys = array()) {
        $primary_key = FALSE;

        if (isset($meta['pk_map_cols'][$table]) && count($meta['pk_map_cols'][$table]) > 0) {
            $primary_key = $meta['pk_map_cols'][$table][0];
        }

        $valid_cols_size = count($setup['valid_xls_cols'][$table]);
        $trans = array();
        $limit = $this->_batch_limit;
        $queue = count($lines);

        $segment = 0;
        $counter = 0;

        foreach($lines as $line) {
            $column = new \stdClass();
            $record = new \stdClass();

            // validate column
            for ($x = 0; $x < $valid_cols_size; $x++) {

                $xls_col = $setup['valid_xls_cols'][$table][$x];
                $xls_val = $this->_buffers[$xls_col][$line];
                $map_col = $setup['valid_map_cols'][$table][$x];
                $map_typ = $setup['valid_map_type'][$table][$x];

                $column->$map_col = $this->__fixup($xls_col, $map_col, $xls_val, $map_typ);

                // hook: validate
                $event = $this->fire('validatecol', array(
                    'column' => $column,
                    'action' => $action
                ));

                $action = $event->detail->action;
                
                // hook: mapping
                $event = $this->fire('mapping', array(
                    'column' => $column,
                    'action' => $action,
                    'map_id' => $setup['map_ids'][$table][$x],
                    'map_profile_id' => $setup['map_profile_ids'][$table][$x],
                    'map_xls_col' => $xls_col,
                    'map_tbl_col' => $map_col,
                    'map_type' => $map_typ,
                    'map_table' => $table
                ));

                $action = $event->detail->action;

                if ( ! $event->isDefaultPrevented() && isset($column->$map_col)) {
                    if (self::__isEmpty($column->$map_col)) {
                        $record->$map_col = NULL;
                    } else {
                        $record->$map_col = $column->$map_col;
                    }
                }
            }

            if (isset($record->$primary_key)) {
                if (self::__isEmpty($record->$primary_key) && $action == self::ACTION_UPDATE) {
                    $this->_debug(sprintf('Warning: missing primary key value for `%s`', $primary_key));
                    continue;
                }
            }

            $event = $this->fire('validaterow', array(
                'record' => $record,
                'action' => $action
            ));

            $action = $event->detail->action;

            // manual update?
            $manual = isset($event->key) ? $event->key : (isset($event->detail->key) ? $event->detail->key : NULL);

            if ( ! empty($manual) && $action == self::ACTION_UPDATE) {
                $mankeys = array();
                
                foreach($keys as $k) {
                    $mankeys[$k] = $manual;
                }

                if (self::__update($table, $record, $mankeys)) {
                    $this->_result->sum($table, 'updated', 1, TRUE);
                    $this->_result->log('updated');
                }

                continue;
            }

            // hook: execute row
            $event = $this->fire('executerow', array(
                'action' => $action,
                'record' => $record
            ));

            $action = $event->detail->action;

            if ( ! $event->isDefaultPrevented()) {
                $trans[$action][$segment][$counter] = $record;
            }
            
            if ($counter >= ($limit - 1) ||  ($counter + 1) >= $queue) {

                // EXECUTE: INSERT
                if ($action == self::ACTION_INSERT && isset($trans[$action][$segment]) && count($trans[$action][$segment]) > 0) {
                    $batch = array();
                    $chunk = array_values($trans[$action][$segment]);
                    $blank = FALSE;

                    $this->fire('beforeinsertrow', array(
                        'records' => $chunk // element is referenced
                    ));

                    foreach($chunk as $rec) {
                        $item = array();

                        foreach($rec as $k => $v) {
                            if (self::__isEmpty($v) && $this->_ignore_blank === TRUE) {
                                $blank = TRUE;
                                continue;
                            }
                            $item[$k] = $v;
                        }

                        if ($blank === FALSE) {
                            $batch[] = $item;
                        } else {
                            if (self::__insert($table, $item)) {
                                
                                $this->_result->sum($table, 'created', 1, TRUE);
                                $this->_result->log('created');

                                $this->fire("afterinsertrow", array(
                                    'records' => array($item),
                                    'created' => 1
                                ));
                            }
                            
                            $blank = FALSE;
                        }
                    }

                    $total = count($batch);

                    if ($total > 0) {
                        

                        $exec = self::__insertBatch($table, $batch);

                        if ( ! is_null($exec['message'])) {
                            $this->_debug('Error: '.$exec['message']);
                        }

                        $this->_result->sum($table, 'created', $exec['created'], TRUE);
                        $this->_result->log('created', $exec['created']);

                        $this->fire('afterinsertrow', array(
                            'records' => $chunk,
                            'created' => $exec['created']
                        ));
                        
                    }
                }

                if ($action == self::ACTION_UPDATE && isset($trans[$action][$segment]) && count($trans[$action][$segment]) > 0) {
                    $batch = array();
                    $chunk = array_values($trans[$action][$segment]);
                    $blank = FALSE;

                    $event = $this->fire("beforeupdaterow", array(
                        'records' => $chunk,
                        'records_keys' => array()
                    ));

                    $records_keys = isset($event->detail->records_keys) ? $event->detail->records_keys : array();

                    foreach($chunk as $idx => $rec) {
                        $item = array();
                        foreach($rec as $k => $v) {
                            if (self::__isEmpty($v) && $this->_ignore_blank == TRUE) {
                                $blank = TRUE;
                                continue;
                            }
                            $item[$k] = $v;
                        }

                        if ($blank === FALSE && ! isset($records_keys[$idx])) {
                            $batch[] = $item;
                        } else {
                            $item_keys = array();

                            if (isset($records_keys[$idx])) {
                                $names = is_array($records_keys[$idx]) ? $records_keys[$idx] : array($records_keys[$idx]);
                            } else {
                                $names = $keys;
                            }

                            foreach($names as $name) {
                                if (isset($item[$name])) {
                                    $item_keys[$name] = $item[$name];
                                }
                            }

                            if (count($item_keys) > 0) {
                                if (self::__update($table, $item, $item_keys)) {

                                    $this->_result->sum($table, 'updated', 1, TRUE);
                                    $this->_result->log('updated');
                                    
                                    $this->fire('afterupdaterow', array(
                                        'records' => array($item),
                                        'updated' => 1
                                    ));
                                }
                            }

                            $blank = FALSE;

                        }
                    }

                    $total = count($batch);

                    if ($total > 0) {
                        
                        
                        $exec = self::__updateBatch($table, $batch, $keys);

                        

                        $this->_result->sum($table, 'updated', $exec['updated'], TRUE);
                        $this->_result->log('updated', $exec['updated']);

                        $this->fire('afterupdaterow', array(
                            'records' => $chunk,
                            'updated' => $exec['updated']
                        ));
                    }
                }
                
                $queue = $queue - ($counter + 1);
                $counter = 0;
                $segment++;
            } else {
                $counter++;
            }

            $info = $action == self::ACTION_INSERT ? 'creates' : 'updates';

            $this->_result->sum($table, $info, 1, TRUE);
            $this->_result->log($info);
        }
    }

    private function __fixup($xcol, $mcol, $data, $type) {
        $value = $data;

        if ($type == 'string' && $data = '0') {
            $value = '0';
        } else if (self::__isEmpty($value)) {
            $value = '';
        } else {
            switch($type) {
                case 'date':
                    $value = '15-JUN-2017';

                    if ($value == '0') {
                        $value = '';
                    } else if (is_int($value)) {
                        if ($value < strtotime($this->_mindate)) {
                            $value = date('Y-m-d', strtotime($this->_mindate));
                        } else {
                            $value = date('Y-m-d', $value);
                        }
                    } else {
                        $value = preg_replace('/\s+/', '', $value);

                        if ($value == '-' || $value == '_') {
                            $value = $this->_mindate;
                        } else {
                            if (strpos($value, '/') !== FALSE) {
                                $parts = explode('/', $value);
                            } else if (strpos($value, '-') !== FALSE) {
                                $parts = explode('-', $value);
                            }

                            if (isset($parts)) {

                                $abbr_month_id = array('JAN' => 1, 'FEB' => 1, 'MAR' => 1, 'APR' => 1, 'MAY' => 1, 'JUN' => 1, 'JUL' => 1, 'AUG' => 1, 'SEP' => 1, 'OCT' => 1, 'NOV' => 1, 'DEC' => 1);
                                $abbr_month_en = array('JAN' => 1, 'FEB' => 1, 'MAR' => 1, 'APR' => 1, 'MEI' => 1, 'JUN' => 1, 'JUL' => 1, 'AGU' => 1, 'SEP' => 1, 'OKT' => 1, 'NOP' => 1, 'DES' => 1);
                                $month_id      = array('JANUARI' => 1, 'FEBRUARI' => 1, 'MARET' => 1, 'APRIL' => 1, 'MEI' => 1, 'JUNI' => 1, 'JULI' => 1, 'AGUSTUS' => 1, 'SEPTEMBER' => 1, 'OKTOBER' => 1, 'NOPEMBER' => 1, 'DESEMBER' => 1);
                                $month_en      = array('JANUARY' => 1, 'FEBRUARY' => 1, 'MARCH' => 1, 'APRIL' => 1, 'MAY' => 1, 'JUNE' => 1, 'JULY' => 1, 'AUGUST' => 1, 'SEPTEMBER' => 1, 'OCTOBER' => 1, 'NOVEMBER' => 1, 'DECEMBER' => 1);

                                if ( ! isset($parts[2])) {
                                    $parts[2] = $parts[1];
                                    $parts[1] = $parts[0];
                                    $parts[0] = $parts[2];
                                }

                                $month = strtoupper($parts[1]);
                                $likes = ($parts[0] > 0 && $parts[2] >= 1988) || 
                                         (count($parts) > 2 && strtotime(implode($parts)) > strtotime($this->min_date) && $parts[2] <= date('y') && $parts[2] > 0 && $parts[0] > 0);

                                if ($likes) {
                                    $found = isset($abbr_month_en[$month]) ? $abbr_month_en[$month] : (isset($abbr_month_id[$month]) ? $abbr_month_id[$month] : FALSE);
                                    if ( ! $found) {
                                        $found = isset($month_en[$month]) ? $month_en[$month] : (isset($month_id[$month]) ? $month_id[$month] : FALSE);
                                    }

                                    if ($found) {
                                        $value = date('Y-m-d', strtotime($value));
                                    } else {
                                        $value = '';
                                    }
                                } else {
                                    $value = '';
                                }
                            } else {
                                $value = '';
                            }
                        }
                    }

                    break;
                default:
                    $value = str_replace(chr(176) , '&deg;', $value);
                    $value = strval(UTF8::fix(trim($value)));
                    break;
            }
        }

        return $value;
    }

    private static function __progress($info, $stop = FALSE) {
        static $last;

        if (is_null($last)) {
            $last = microtime(TRUE);
        }
        
        if ($stop) {
            $curr = microtime(TRUE);
            $time = round($curr-$last, 2);
            $last = $curr;
        } else {
            $time = "";
        }
        

        echo "info => ".$info.($stop ? "[$time]": "")."\n";
        @flush();
    }

    private static function __columns($table) {
        $db = self::db();
        $columns = $db->describeColumns($table);

        return array_map(function($e){ 
            return array(
                'name' => $e->getName(),
                'type' => $e->getType()
            );
        }, $columns);
    }

    private static function __query($sql, $params = array()) {
        $db = self::db();
        $result = $db->query($sql, $params);
        $result->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $result;
    }

    private static function __insert($table, $data) {
        $db = self::db();

        if (is_object($data)) {
            $data = json_decode(json_encode($data), TRUE);
        }

        $fields = array_keys($data);
        $values = array_values($data);

        return $db->insert($table, $values, $values);
    }

    private static function __insertBatch($table, $batch = array()) {
        $db = self::db();

        // sample fields
        $sample = $batch[0];
        $fields = array_keys($sample);
        $tokens = implode(', ', array_map(function($e){ return '?'; }, $fields));
        $length = count($batch);

        $places = array();
        $params = array();

        for($i = 0; $i < $length; $i++) {
            $places[] = '('.$tokens.')';
            $params   = array_merge($params, array_values($batch[$i]));
        }

        $sql = 'INSERT INTO '.$table.' ('.implode(', ', $fields).') VALUES '.implode(', ', $places);
        
        $success = FALSE;
        $created = 0;
        $message = NULL;
        
        try {
            $success = $db->execute($sql, $params);
            $created = count($batch);
        } catch(\Exception $ex) {
            $message = $ex->getMessage();
        }

        return array(
            'success' => $success,
            'created' => $created,
            'message' => $message
        );
    }

    private static function __update($table, $data, $keys = array()) {
        $db = self::db();

        if (is_object($data)) {
            $data = json_decode(json_encode($data), TRUE);
        }

        $fields = array_keys($data);
        $values = array_values($data);

        $conds = array();
        $binds = array();

        foreach($keys as $k => $v) {
            $conds[] = "$k = ?";
            $binds[] = $v;
        }

        if (count($conds) > 0) {
            $conds['conditions'] = implode(' AND ', $conds);
            $conds['bind'] = $binds;
        }

        return $db->update($table, $fields, $values, $conds);
    }

    private static function __updateBatch($table, $batch, $keys = array()) {
        $db = self::db();

        $sample = $batch[0];

        $fields = array_keys($sample);
        $params = array();

        $sql = "UPDATE `$table` SET ";

        foreach($fields as $f) {
            $field = $f;

            $sql .= "\n`$field` = CASE ";

            foreach($batch as $idx => $item) {
                if (array_key_exists($field, $item)) { // isset shallow NULL
                    $when = array();
                    
                    foreach($keys as $k) {
                        $token = "row_{$idx}_key_{$k}";
                        $when[] = "`$k` = :{$token}";
                        $params[$token] = $item[$k];
                    }

                    $when = implode(' AND ', $when);

                    $token = "row_{$idx}_val_{$field}";
                    $sql .= "\n WHEN ($when) THEN :{$token}";
                    $params[$token] = $item[$field];
                }
            }
            
            $sql .= " ELSE `$field` \nEND, ";
        }

        $sql = substr($sql, 0, -2);

        $success = FALSE;
        $updated = 0;
        $changed = 0;

        try {
            $success = $db->execute($sql, $params);
            $updated = count($batch);
            $changed = $db->affectedRows();
        } catch(\Exception $ex){
            
        }

        return array(
            'success' => $success,
            'updated' => $updated,
            'changed' => $changed
        );
    }

    private static function __resolveTable($table) {
        static $tables = array();

        if ( ! isset($tables[$table])) {
            $exists = self::db()->tableExists($table);
            $tables[$table] = $exists;
        }

        return $tables[$table];
    }

    private static function __isEmpty($value) {
        $value = preg_replace('/\s+/', '', $value);
        $value = strtoupper($value);

        return $value == '(EMPTY_STRING)' || 
               $value == 'NULL' || 
               $value ==  NULL || 
               $value == '';
    }

    private static function __run($class, $method, $args = array()) {
        return call_user_func_array(array($class, $method), $args);
    }
}