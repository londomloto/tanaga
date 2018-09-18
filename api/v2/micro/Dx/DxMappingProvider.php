<?php
namespace Micro\Dx;

class DxMappingProvider extends \Micro\Component {

	private $_app;
	private $_profile = 'project';
	private $_source = 'userfile';
	private $_dbtype = 'mysql';
	private $_worksheet = 0;
	private $_xls;
	private $_sheet;
	private $_profileData;
	private $_profileMap;
	private $_profileAuth;
	private $_xlsItems;
	private $_xlsHeader;
	private $_listeners = array();
	private $_errors = array();
	private $_infos;
	private $_terminated = FALSE;
	private $_minDate = '1988-12-12';
	private $_isAuth = FALSE;
	private $_ugName = FALSE;
	private $_usgName = FALSE;
	private $_udName = FALSE;
	private $_ignoreBlank = FALSE;
	private $_updateMapId = array();
	private $_insertMapId = array();

	public $xsourceData;

	protected static $_win1252ToUtf8 = array(
		128 => "\xe2\x82\xac",
		130 => "\xe2\x80\x9a",
		131 => "\xc6\x92",
		132 => "\xe2\x80\x9e",
		133 => "\xe2\x80\xa6",
		134 => "\xe2\x80\xa0",
		135 => "\xe2\x80\xa1",
		136 => "\xcb\x86",
		137 => "\xe2\x80\xb0",
		138 => "\xc5\xa0",
		139 => "\xe2\x80\xb9",
		140 => "\xc5\x92",
		142 => "\xc5\xbd",
		145 => "\xe2\x80\x98",
		146 => "\xe2\x80\x99",
		147 => "\xe2\x80\x9c",
		148 => "\xe2\x80\x9d",
		149 => "\xe2\x80\xa2",
		150 => "\xe2\x80\x93",
		151 => "\xe2\x80\x94",
		152 => "\xcb\x9c",
		153 => "\xe2\x84\xa2",
		154 => "\xc5\xa1",
		155 => "\xe2\x80\xba",
		156 => "\xc5\x93",
		158 => "\xc5\xbe",
		159 => "\xc5\xb8"
	);
	protected static $_utf8ToWin1252 = array(
		"\xe2\x82\xac" => "\x80",
		"\xe2\x80\x9a" => "\x82",
		"\xc6\x92" => "\x83",
		"\xe2\x80\x9e" => "\x84",
		"\xe2\x80\xa6" => "\x85",
		"\xe2\x80\xa0" => "\x86",
		"\xe2\x80\xa1" => "\x87",
		"\xcb\x86" => "\x88",
		"\xe2\x80\xb0" => "\x89",
		"\xc5\xa0" => "\x8a",
		"\xe2\x80\xb9" => "\x8b",
		"\xc5\x92" => "\x8c",
		"\xc5\xbd" => "\x8e",
		"\xe2\x80\x98" => "\x91",
		"\xe2\x80\x99" => "\x92",
		"\xe2\x80\x9c" => "\x93",
		"\xe2\x80\x9d" => "\x94",
		"\xe2\x80\xa2" => "\x95",
		"\xe2\x80\x93" => "\x96",
		"\xe2\x80\x94" => "\x97",
		"\xcb\x9c" => "\x98",
		"\xe2\x84\xa2" => "\x99",
		"\xc5\xa1" => "\x9a",
		"\xe2\x80\xba" => "\x9b",
		"\xc5\x93" => "\x9c",
		"\xc5\xbe" => "\x9e",
		"\xc5\xb8" => "\x9f"
	);

	public function __construct($params = array()) {

		// get instance phalcon

		$this->_app = $this->getApp();
		$this->_db = $this->_app->db;
		$this->_providers = $this->_app->config->dx->providers;

		// default result info

		$this->_infos = new \stdClass();
		$this->_infos->updated = 0;
		$this->_infos->inserted = 0;
		$this->_infos->update = 0;
		$this->_infos->insert = 0;
		$this->_infos->missed_columns = array();
		$this->_infos->missed_primary_key = FALSE;
		$this->_infos->missed_secondary_key = FALSE;

		$this->setup($params);

		if (count($this->_profileData) > 0 && count($this->_profileMap) > 0 && $this->_terminated === FALSE) {
			$this->_configureXlsItems();		
		}

		$this->xsourceData = $this->_xlsItems;
	}

	private function _run($class, $method, $args = array()) {
		return call_user_func_array(array($class, $method), $args);
	}

	public function setup($params = array()) {
		foreach ($params as $k => $v) {
			$objName = '_'.$k;
			$this->$objName = $v;
		}

		$this->_initExcelbook();
		$this->_setupProfileMapping();

		/*$profile = $this->_run($this->_providers->profile, 'findFirst', array(
			array(
				'conditions' => 'profile_name = :pn:',
				'bind' => array('pn' => 'Fiberstar')
			)
		));

		print_r($profile->toArray());
		print_r($profile->mapping->toArray());*/
	}

	public function debug() {

	}

	public function save() {

		if ($this->_terminated === TRUE) {
			$this->error();
			
			return FALSE;
		}

		$config = new \stdClass();
		$config->validXlsCols = array();
		$config->validMapCols = array();
		$config->validMapDtype = array();
		$config->mapId = array();
		$config->mapProfileId = array();
		$config->mapTable = array();
		$config->pkMapCol = array();
		$config->pkXlsCol = array();
		$config->skMapCol = array();
		$config->skXlsCol = array();

		foreach ($this->_profileMap as $k) {

			if ($k['map_active'] != 1) continue;
			
			if (! $this->_checkTableExist($k['map_table'])) {
				$this->_errors[] = '[LIB] Can\'t find table [' . $k['map_table'] . ']';
				$this->_terminated = TRUE;
				
				return FALSE;
			}
			
			// if (count((array)$config) > 0) {
			if (in_array(strtoupper(strval($k['map_xls_col'])) , $this->_xlsHeader, TRUE)) {
				$config->validXlsCols[$k['map_table']][] = strtoupper($k['map_xls_col']);
				$config->validMapCols[$k['map_table']][] = $k['map_tbl_col'];
				$config->validMapDtype[$k['map_table']][] = $k['map_dtype'];
				$config->mapId[$k['map_table']][] = $k['map_id'];
				$config->mapProfileId[$k['map_table']][] = $k['map_profile_id'];
				$config->mapTable[] = $k['map_table'];

				if ($k['map_pk'] == 1 || $k['map_pk'] == '1') {
					$config->pkMapCol[$k['map_table']][] = $k['map_tbl_col'];
					$config->pkXlsCol[$k['map_table']][] = strtoupper($k['map_xls_col']);
				}
				
				if ($k['map_sk'] == 1) {
					$config->skMapCol[$k['map_table']][] = $k['map_tbl_col'];
					$config->skXlsCol[$k['map_table']][] = strtoupper($k['map_xls_col']);
				}
			} 

			else $this->_infos->missed_columns[] = $k['map_xls_col'];
		}

		if(count($config->validMapCols) === 0) $this->_terminated = TRUE;
		if ($this->_terminated === TRUE) return FALSE;
		
		$tbl = array_unique($config->mapTable);

		foreach ($tbl as $kTbl) {
			unset($pkMapCol, $pkXlsCol, $skMapCol, $skXlsCol);
			
			$mapKey = array();
			$xlsKey = array();
			$cMapKey = 0;
			$cXlsKey = 0;

			$sqlSelect = '';
			$sqlFrom = '';
			
			if (isset($config->pkMapCol[$kTbl]) && count($config->pkMapCol[$kTbl]) > 0) {
				$pkMapCol = $config->pkMapCol[$kTbl];
				$pkXlsCol = $config->pkXlsCol[$kTbl];
				$mapKey = array_merge($mapKey, $pkMapCol);
				$xlsKey = array_merge($xlsKey, $pkXlsCol);

				if($pkMapCol) {
					$sqlSelect .= implode(',', $pkMapCol);
				}
			} 
			else $this->_infos->missed_primary_key = TRUE;
			
			if (isset($config->skMapCol[$kTbl]) && count($config->skMapCol[$kTbl]) > 0) {
				$skMapCol = $config->skMapCol[$kTbl];
				$skXlsCol = $config->skXlsCol[$kTbl];
				$mapKey = array_merge($mapKey, $skMapCol);
				$xlsKey = array_merge($xlsKey, $skXlsCol);

				if($pkMapCol) {
					$sqlSelect .= implode(',', $pkMapCol);
				}
			} 
			else $this->_infos->missed_secondary_key = TRUE;

			if($kTbl) {
				$sqlFrom .= $kTbl;
			}

			$sqlQuery = "SELECT ".$sqlSelect." FROM ".$sqlFrom;
			$qry = $this->_db->fetchAll($sqlQuery);
			
			$cMapKey = count($mapKey);
			$cXlsKey = count($xlsKey);
			$dataMapKey = array();

			if($qry) {

				for($i = 0, $j = count($qry); $i < $j; $i++) {

					for($x = 0; $x < $cMapKey; $x++) {
						$tmp = $mapKey[$x];
						$dataMapKey[$tmp][$i] = $qry[$i][$tmp];
					}

				}
			}
			
			/*if ($qry->num_rows() > 0) {
				foreach ($qry->result_array() as $kResultArray => $vResultArray) {
					for ($x = 0; $x < $cMapKey; $x++) {
						$tmp = $mapKey[$x];
						$dataMapKey[$tmp][$kResultArray] = $vResultArray[$tmp];
					}
				}
			}*/
			
			$pkData = array();
			$skData = array();
			$arrayFilter = array_filter($this->_xlsItems);
			$result = array();
			
			if (count($dataMapKey) > 0) {
				for ($x = 0; $x < $cXlsKey; $x++) {
					$tmpXls = $xlsKey[$x];
					$tmpMap = $mapKey[$x];
					$result[] = array_diff($arrayFilter[$tmpXls], $dataMapKey[$tmpMap]);
				}

				$arKeys = array();
				for ($x = 0; $x < count($result); $x++) {
					$tmpKey = array_keys($result[$x]);
					for ($y = 0; $y < count($tmpKey); $y++) {
						$arKeys[] = $tmpKey[$y];
					}
				}
				
				$tmpXls = $xlsKey[0];
				$tmpMap = $mapKey[0];
				$newArrayFilter = array();
				$arUnique = array_unique($arKeys);

				for ($x = 0; $x < count($arUnique); $x++) {
					$tmpArUnique = $arUnique[$x];
					$newArrayFilter[$tmpXls][$tmpArUnique] = $arrayFilter[$tmpXls][$tmpArUnique];
					unset($arrayFilter[$tmpXls][$tmpArUnique]);
				}
				
				if (isset($newArrayFilter[$tmpXls]) && count($newArrayFilter[$tmpXls]) > 0) {
					$this->_storeData('insert', $newArrayFilter[$tmpXls], $config, $kTbl, $tmpMap);
				}

				$this->_storeData('update', $arrayFilter[$tmpXls], $config, $kTbl, $mapKey);
			} 
			else {
				if(isset($xlsKey[0]) && $mapKey[0]){
					$tmpXls = $xlsKey[0];
					$tmpMap = $mapKey[0];
					$this->_storeData('insert', $arrayFilter[$tmpXls], $config, $kTbl, $tmpMap);					
				}else $this->_errors[] = '[LIB] Primary Key Column Not Found';
			}
		}
	}

	public function info() {
		return 'inserted: ' . $this->_infos->inserted . '<br />updated: ' . $this->_infos->updated . '<br />missed xls columns: [' . implode(', ', $this->_infos->missed_columns) . ']' . '<br />missed primary key: ' . $this->_infos->missed_primary_key . '<br />missed secondary key: ' . $this->_infos->missed_secondary_key;
	}
	
	public function error() {
		if (count($this->_errors) > 0) return implode('<br />', $this->_errors);
		else return FALSE;
	}
	
	public function success() {
		if ($this->_terminated === FALSE) return TRUE;
		else return FALSE;
	}

	private function _initExcelbook() {

		if (isset($_FILES[$this->_source])) $this->_source = $_FILES[$this->_source];
		else {
			$this->_errors[] = '[LIB] Invalid File [' . $this->_source . ']';
			$this->_terminated = TRUE;
			
			return FALSE;
		}

		if($this->getApp()->config->libxl) {

			$libxlConfig = $this->getApp()->config->libxl;

			$isXlsx = pathinfo($this->_source['name'],PATHINFO_EXTENSION) == 'xlsx' ? TRUE: FALSE;
			// $isXlsx = FALSE;
		
			$this->_xls = new \ExcelBook($libxlConfig->user, $libxlConfig->key, $isXlsx);
			
			if (! $this->_xls->loadFile($this->_source['tmp_name'])) {

				$this->_errors[] = '[LIB] Can\'t load file [' . $this->_source['name'] . ']';
				$this->_terminated = TRUE;
				
				return FALSE;
			}
			
			$this->_sheet = $this->_xls->getSheet($this->_worksheet);
			
			if (! $this->_sheet) {
				$this->_errors[] = '[LIB] Can\'t load the worksheet [' . $this->_worksheet . ']';
				$this->_terminated = TRUE;
			}

		}
		else {

			$this->_errors[] = '[LIB] Config file is undefined';
			$this->_terminated = TRUE;
			
			return FALSE;

		}

		// if ($this->source['type'] == 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') $is_xlsx = TRUE;
		// else $is_xlsx = FALSE;
		
	}

	private function _setupProfileMapping() {

		// if ($this->_terminated === TRUE) return FALSE;

		$profile = $this->_run($this->_providers->profile, 'findFirst', array(
			array(
				'conditions' => 'upper(profile_name) = :pn:',
				'bind' => array('pn' => strtoupper($this->_profile))
			)
		));

		if($profile) {
			$this->_profileData = $profile->toArray();

			$mapping = $profile->mapping->toArray();

			if($mapping) {
				$this->_profileMap = $mapping;
			}
			else {
				$this->_errors[] = '[LIB] Can\'t find dx mapping [' . $this->_profile . ']';
				$this->_terminated = TRUE;
				
				return FALSE;
			}

		}
		else {

			$this->_errors[] = '[LIB] Can\'t find dx profile [' . $this->_profile . ']';
			$this->_terminated = TRUE;
			
			return FALSE;

		}
	}

	private function _init_dx_map() {
		// if ($this->_terminated === TRUE) return FALSE;

		$queryMap = 
		"
			SELECT
				*
			FROM
				dx_mapping
			WHERE
				map_profile_id = ".$this->_profileData['profile_id']."
			ORDER BY
				map_table,
				map_grp_seq,
				map_tbl_col
		";

		$resMap = $this->_db->fetchAll($queryMap);

		if($resMap) {

			$this->_profileMap = $resMap;
			
		}
		else {
			$this->_errors[] = '[LIB] Can\'t find dx mapping [' . $this->_profile . ']';
			$this->_terminated = TRUE;
			
			return FALSE;
		}
	}

	private function _configureXlsItems() {

		$xlsHeader = $this->_sheet->readRow(($this->_profileData['header_row_idx'] - 1) , 0, ($this->_sheet->lastCol() - 1));
		$colLetter = 'A';
		$colNumber = FALSE;
		$this->_xlsItems = array();
		$this->_xlsHeader = array();
		$rowFirst = $this->_profileData['row_offset'] - 1;
		$rowLast = $this->_sheet->lastRow() - 1;
		/* Added by asep@kct.co.id :: Handle error when read empty data in worksheet */
		if($rowFirst <= $rowLast){
			for ($x = 0; $x < count($xlsHeader); $x++) {
				if ($colLetter == $this->_profileData['col_offset']) $colNumber = $x;
				if( $colNumber !== FALSE) {
					// if ($colNumber === FALSE) continue;
					
					if($this->_profileData['map_header'] == 1){
						$tmp = strtoupper($xlsHeader[$x]);
						$tmp = strval($tmp);
					}else{
						$tmp = $colLetter;				
					}
					
					$this->_xlsHeader[] = $tmp;
					if(isset($this->_profileData['has_merge_cell']) && $this->_profileData['has_merge_cell'] == 1){
						for ($y = $rowFirst; $y <= $rowLast; $y++) {
							$merge = $this->_sheet->getMerge($y,$x);
							if($merge) $val = $this->_sheet->read($merge['row_first'], $merge['col_first']);
							else $val = $this->_sheet->read($y, $x);

							if ($val === FALSE) {
								$val = '';
								$this->errors[] = '[LIB] Invalid xls data type at column [' . $tmp . '] and row [' . ($y + 1) . '] set by system to empty';
							}

							$this->_xlsItems[$tmp][] = $val;
						}
					}else{						
						$this->_xlsItems[$tmp] = $this->_sheet->readCol($x, $rowFirst, $rowLast);
					}
					if ($this->_xlsItems[$tmp] === FALSE) {
						for ($y = $rowFirst; $y <= $rowLast; $y++) {
							$val = $this->_sheet->read($y, $x);
							
							if ($val === FALSE) {
								$val = '';
								$this->_errors[] = '[LIB] Invalid xls data type at column [' . $tmp . '] and row [' . ($y + 1) . '] set by system to empty';
							}
							
							$this->_xlsItems[$tmp][] = $val;
						}
					}
				}

				$colLetter++;
			}			
		}

	}

	private function _storeData($action, $xlsData, $config, $tbl, $pkMapCol) {

		if (count($xlsData) < 1) return FALSE;

		if (is_array($pkMapCol)) {
			$pkMapColOri = $pkMapCol;
			$pkMapCol = $pkMapColOri[0];
		}
		
		$realAction = strtolower($action);
		$data = array();
		$y = 0;
		$z = 0;
		$maxData = 250;
		$rUpdate = array();
		$rInsert = array();
		$dataDeducted = count($xlsData);
		
		foreach ($xlsData as $k => $v) {
			$action = $realAction;
			$oData = new \stdClass();
			$oConfig = new \stdClass();
			$rData = array();
			
			for ($x = 0; $x < count($config->validXlsCols[$tbl]); $x++) {
				$valid = TRUE;
				$tmpXlsCol = $config->validXlsCols[$tbl][$x];
				$tmpMapCol = $config->validMapCols[$tbl][$x];
				$oData->$tmpMapCol = $this->_validateRow($tmpXlsCol, $tmpMapCol, $this->_xlsItems[$tmpXlsCol][$k], $config->validMapDtype[$tbl][$x]);
				$oConfig->action = $action;
				
				$this->_fireEvent("validatecol", array(
					$this,
					$oData,
					$oConfig
				));
				
				$action = $oConfig->action;
				$oConfig->mapId = $config->mapId[$tbl][$x];
				$oConfig->mapProfileId = $config->mapProfileId[$tbl][$x];
				$oConfig->mapTable = $tbl;
				
				$valid = $this->_fireEvent("mapping", array(
					$this,
					$oData,
					$oConfig,
					$action
				));
				
				/*if ($this->_isAuth !== FALSE) {
					if ($this->ud_name !== FALSE || $this->usg_name !== FALSE || $this->ug_name !== FALSE) {
						if (in_array($oConfig->mapId, $this->update_map_id)) $rUpdate[$tmpMapCol] = TRUE;
						if (in_array($oConfig->mapId, $this->insert_map_id)) $rInsert[$tmpMapCol] = TRUE;
					}
				}*/
				
				if ($valid !== FALSE && isset($oData->$tmpMapCol)) {
					if ($this->_isEmpty($oData->$tmpMapCol)) $rData[$tmpMapCol] = NULL;
					else $rData[$tmpMapCol] = $oData->$tmpMapCol;
				}
			}
			
			if (isset($rData[$pkMapCol])) {
				if ($this->_isEmpty($rData[$pkMapCol]) && $action == 'update') {
					$this->errors[] = '[LIB] Primary key column [' . $pkMapCol . '] data is empty';
					
					continue;
				}
			}
			
			$oConfig->action = $action;
			
			$this->_fireEvent("validaterow", array(
				$this,
				$rData,
				$oConfig
			));
			
			$action = $oConfig->action;
			
			if (isset($oConfig->key) && is_empty($oConfig->key) === FALSE && $action == 'update') {
				$tmpManual = isset($pkMapColOri) ? $pkMapColOri : $pkMapCol;
				$this->_manualStoreData($tbl, $rData, $tmpManual, $oConfig->key);
				$this->_infos->updated++;
				
				continue;
			}
			
			$execute = TRUE;

			$execute = $this->_fireEvent("executerow", array(
				$this,
				$rData,
				$oConfig
			));
			
			if ($execute !== FALSE) $data[$action][$z][$y] = $rData;
			
			if ($y >= $maxData || ($y + 1) >= $dataDeducted) {
				if ($action == 'insert' && isset($data[$action][$z]) && count($data[$action][$z]) > 0) {
					$object = json_decode(json_encode($data[$action][$z]));
					$object = (object)$object;
					$newObject = new \stdClass();
					$newObject->data = array();
					
					$this->_fireEvent("beforeinsertrow", array(
						$this,
						$object,
						$newObject
					));
					
					$array = json_decode(json_encode($object) , TRUE);
					$tempArray = array();
					$temp = array();
					$isBlank = FALSE;
					
					foreach ($array as $k_insert => $v_insert) {
						foreach ($v_insert as $k => $v) {
							if (!in_array($k, $newObject->data) && $k != $pkMapCol) {
								if ($this->_isAuth !== FALSE && !isset($rInsert[$k])) continue;
							}
							if ($this->_isEmpty($v) && $this->_ignoreBlank === TRUE) {
								$isBlank = TRUE;
								continue;
							}

							$temp[$k] = $v;
						}
						
						if ($isBlank === FALSE) $tempArray[] = $temp;
						else {

							$colInsert = [];
							$valInsert = [];

							foreach($temp as $key => $value) {
								$colInsert[] = $key;
								$valInsert[] = "'".$value."'";
							}

							$sqlInsert = "INSERT INTO ".$tbl." (".implode(',', $colInsert).") VALUES (".implode(',', $valInsert).")";

							if($this->_db->query($sqlInsert)) {
								$this->_infos->inserted = $this->_infos->inserted + 1;
							}

							// $this->CI->db->insert($tbl, $temp);
							// $this->_infos->inserted = $this->_infos->inserted + 1;

							$this->_fireEvent("afterinsertrow", array(
								$this,
								$array
							));
							
							$isBlank = FALSE;
						}
					}
					
					if (count($tempArray) > 0) {

						if($tempArray) {							

							for($i = 0, $j = count($tempArray); $i < $j; $i++) {

								$colInsert = [];
								$valInsert = [];

								foreach($tempArray[$i] as $key => $value) {
									$colInsert[] = $key;
									$valInsert[] = "'".$value."'";
								}

								if($colInsert && $valInsert) {
									$sqlInsert = "INSERT INTO ".$tbl." (".implode(',', $colInsert).") VALUES (".implode(',', $valInsert).")";

									if($this->_db->query($sqlInsert)) {
										$this->_infos->inserted = $this->_infos->inserted + 1;
									}
								}

							}
						}

						// $this->CI->db->insert_batch($tbl, $tempArray, $maxData);
						// $this->_infos->inserted = $this->_infos->inserted + count($tempArray);
						
						$this->_fireEvent("afterinsertrow", array(
							$this,
							$array
						));
					}
				}
				
				if ($action == 'update' && isset($data[$action][$z]) && count($data[$action][$z]) > 0) {
					$object = json_decode(json_encode($data[$action][$z]));
					$object = (object)$object;
					$newObject = new \stdClass();
					$newObject->data = array();
					$newPkMapCol = new \stdClass();
					$newPkMapCol->data = array();
					$old_pkMapCol = $pkMapCol;
					
					$this->_fireEvent("beforeupdaterow", array(
						$this,
						$object,
						$newObject,
						$newPkMapCol
					));
					
					$array = json_decode(json_encode($object) , TRUE);
					$tempArray = array();
					$temp = array();
					$isBlank = FALSE;

					$sqlWhere = '';
					$sqlUpdateSet = '';
					
					foreach ($array as $kUpdate => $vUpdate) {
						foreach ($vUpdate as $k => $v) {
							if (!in_array($k, $newObject->data) && !isset($rUpdate[$k]) && $k != $pkMapCol) {
								if ($this->_isAuth !== FALSE && !isset($rUpdate[$k])) continue;
							}
							if ($this->_isEmpty($v) && $this->_ignoreBlank === TRUE) {
								$isBlank = TRUE;
								continue;
							}

							$temp[$k] = $v;
						}
						
						if ($isBlank === FALSE && !isset($newPkMapCol->data[$kUpdate])) $tempArray[] = $temp;
						else {
							if (isset($newPkMapCol->data[$kUpdate])) $pkMapColValue = $newPkMapCol->data[$kUpdate];
							else {
								$pkMapColValue = isset($pkMapColOri) ? $pkMapColOri : $pkMapCol;
							}
							
							if (is_array($pkMapColValue)) {
								for ($x = 0; $x < count($pkMapColOri); $x++) {
									$tempPkMapColValue = $pkMapColOri[$x];

									$sqlWhere .= ' OR '.$tempPkMapColValue." = '".$temp[$tempPkMapColValue]."'";

									// $this->CI->db->or_where($tempPkMapColValue, $temp[$tempPkMapColValue]);
								}
							} else {

								if($sqlWhere) {
									$sqlWhere .= ' AND ';
								}

								$sqlWhere .= $pkMapCol." = '".$temp[$pkMapColValue];

								// $this->CI->db->where($pkMapCol, $temp[$pkMapColValue]);
							}

							foreach($temp as $key => $value) {

								if($sqlUpdateSet) {
									$sqlUpdateSet .= ', ';
								}

								$sqlUpdateSet .= $key." = '".$value."'";
							}

							$sqlUpdate = "UPDATE ".$tbl." ".$sqlUpdateSet." WHERE ".$sqlWhere;

							if($this->_db->query($sqlUpdate)) {
								$this->_infos->updated = $this->_infos->updated + 1;
							}

							// $this->CI->db->update($tbl, $temp);

							$this->_fireEvent("afterupdaterow", array(
								$this,
								$array
							));
							
							$isBlank = FALSE;
						}
					}
					
					if (count($tempArray) > 0) {
						$tempPkMapCol = isset($pkMapColOri) ? $pkMapColOri : $pkMapCol;

						for($i = 0, $j = count($tempArray); $i < $j; $i++) {

							$sqlWhere = '';
							$sqlUpdateSet = '';

							for($a = 0, $b = count($tempPkMapCol); $a < $b; $a++) {

								if($sqlWhere) {
									$sqlWhere .= ' AND ';
								}

								$sqlWhere .= $tempPkMapCol[$a]." = '".$tempArray[$i][$tempPkMapCol[$a]]."'";

							}

							foreach($tempArray[$i] as $key => $value) {

								if($sqlUpdateSet) {
									$sqlUpdateSet .= ', ';
								}

								$sqlUpdateSet .= $key." = '".$value."'";

							}

							$sqlUpdate = "UPDATE ".$tbl." SET ".$sqlUpdateSet." WHERE ".$sqlWhere;

							if($this->_db->query($sqlUpdate)) {
								$this->_infos->updated = $this->_infos->updated + 1;
							}

						}

						// $this->_update_batch($this->CI->db, $tbl, $tempArray, $tempPkMapCol);
						
						$this->_fireEvent("afterupdaterow", array(
							$this,
							$array
						));
					}
				}
				
				$dataDeducted = $dataDeducted - $y;
				$z++;
				$y = 0;
			}
			
			$this->_infos->$action++;
			$y++;
		}
	}

	private function _checkTableExist($tableName = null) {

		if($tableName) {

			$queryCheck = 
			"
				SELECT
				IF (COUNT(*) > 0, 1, 0) as cek
				FROM
					`INFORMATION_SCHEMA`.`TABLES`
				WHERE
					`TABLE_NAME` = '".$tableName."'
			";


			$resCheckTable = $this->_db->fetchOne($queryCheck);

			if($resCheckTable) {
				
				if($resCheckTable['cek']) {
					return TRUE;
				}
				else {
					return FALSE;
				}

			}
			else {
				return FALSE;
			}

		}
		else {
			return FALSE;
		}

	}

	private function _manualStoreData($tbl, $rData, $pkMapCol, $val) {

		$sqlWhere = '';

		if (is_array($pkMapCol)) {
			for ($x = 0; $x < count($pkMapCol); $x++) {

				if($sqlWhere) {
					$sqlWhere .= " AND ";
				}

				$sqlWhere .= $pkMapCol[$x]." = '".$val[$x]."'";

				// $this->CI->db->where($pkMapCol[$x], $val[$x]);
			}
		} else {

			if($sqlWhere) {
				$sqlWhere .= " AND ";
			}

			$sqlWhere .= $pkMapCol." = '".$val."'";

			// $this->CI->db->where($pkMapCol, $val);
		}

		$sqlUpdateSet = '';

		foreach($rData as $key => $value) {
			if($sqlUpdateSet) {
				$sqlUpdateSet .= ', ';
			}

			$sqlUpdateSet .= $key ." = '".$value."'";
		}

		$sqlUpdate = 'UPDATE '.$tbl.' SET '.$sqlUpdateSet.' WHERE '.$sqlWhere;

		return $this->_db->query($sqlUpdate);

		// return $this->CI->db->update($tbl, $rData);
	}

	private function _validateRow($colName, $fldName, $data, $type) {
		$newData = $data;
		
		if ($type == 'STRING' && $data == '0') $newData = '0';
		elseif (strtoupper($newData) == '(EMPTY STRING)' || $newData == '' || strtoupper($newData) == 'NULL' || $newData === NULL || $this->_isEmpty($newData)) $newData = '';
		else {
			switch (strtoupper($type)) {
				case 'DATE':
					if ($newData == '0') $newData = '';
					elseif (is_int($newData)) {
						if ($newData < strtotime($this->_minDate)) {
							$newData = date('Y-m-d', strtotime($this->_minDate));
						} 
						else {
							$newData = date('Y-m-d', $newData);
						}
					} 
					else {
						$newDataTemp = $newData = str_replace(' ', '', $newData);
						
						if ($newData == '-' || $newData == '_') {
							$newData = $this->_minDate;
						} 
						else {
							if (strpos($newData, '/')) {
								$newDataExp = explode('/', $newData);
							} 
							elseif (strpos($newData, '-')) {
								$newDataExp = explode('-', $newData);
							}
							
							if (isset($newDataExp)) {
								$monthEnShort = array(
									'JAN',
									'FEB',
									'MAR',
									'APR',
									'MAY',
									'JUN',
									'JUL',
									'AUG',
									'SEP',
									'OCT',
									'NOV',
									'DEC'
								);
								$monthIdShort = array(
									'JAN',
									'FEB',
									'MAR',
									'APR',
									'MEI',
									'JUN',
									'JUL',
									'AGU',
									'SEP',
									'OKT',
									'NOP',
									'DES'
								);
								$monthEnLong = array(
									'JANUARY',
									'FEBRUARY',
									'MARCH',
									'APRIL',
									'MAY',
									'JUNE',
									'JULY',
									'AUGUST',
									'SEPTEMBER',
									'OCTOBER',
									'NOVEMBER',
									'DECEMBER'
								);
								$monthIdLong = array(
									'JANUARI',
									'FEBRUARI',
									'MARET',
									'APRIL',
									'MEI',
									'JUNI',
									'JULI',
									'AGUSTUS',
									'SEPTEMBER',
									'OKTOBER',
									'NOPEMBER',
									'DESEMBER'
								);
								
								if (!isset($newDataExp[2])) {
									$newDataExp[2] = $newDataExp[1];
									$newDataExp[1] = $newDataExp[0];
									$newDataExp[0] = $newDataExp[2];
								}
								
								if ($newDataExp[0] > 0 && $newDataExp[2] >= 1988) {
									if (in_array(strtoupper($newDataExp[1]) , $monthEnShort)) {
										$newData = date('Y-m-d', strtotime($newData));
									} 
									elseif (in_array(strtoupper($newDataExp[1]) , $monthIdShort)) {
										$newData = date('Y-m-d', strtotime($newData));
									} 
									elseif (in_array(strtoupper($newDataExp[1]) , $monthEnLong)) {
										$newData = date('Y-m-d', strtotime($newData));
									} 
									elseif (in_array(strtoupper($newDataExp[1]) , $monthIdLong)) {
										$newData = date('Y-m-d', strtotime($newData));
									} 
									else {
										$newData = '';
									}
								} 
								elseif (count($newDataExp) > 2 && strtotime(implode($newDataExp)) > strtotime($this->_minDate) && $newDataExp[2] <= date('y') && $newDataExp[2] > 0 && $newDataExp[0] > 0) {
									if (in_array(strtoupper($newDataExp[1]) , $monthEnShort)) {
										$newData = date('Y-m-d', strtotime($newData));
									} 
									elseif (in_array(strtoupper($newDataExp[1]) , $monthIdShort)) {
										$newData = date('Y-m-d', strtotime($newData));
									} 
									elseif (in_array(strtoupper($newDataExp[1]) , $monthEnLong)) {
										$newData = date('Y-m-d', strtotime($newData));
									} 
									elseif (in_array(strtoupper($newDataExp[1]) , $monthIdLong)) {
										$newData = date('Y-m-d', strtotime($newData));
									} 
									else {
										$newData = '';
									}
								} 
								else {
									$newData = '';
								}
							} 
							else {
								$newData = '';
							}
						}
					}
					
					break;

				default:
					$newData = str_replace(chr(176) , '&deg;', $newData);
					$newData = strval(self::fixUtf8(trim($newData)));
					
					break;
				}
		}
		
		return $newData;
	}

	private function _fireEvent($eventName, $args = array()) {
		if (isset($this->_listeners[$eventName])) return call_user_func_array($this->_listeners[$eventName], $args);
	}
	
	static function fixUtf8($text) {
		if (is_array($text)) {
			foreach ($text as $k => $v) {
				$text[$k] = self::fixUtf8($v);
			}
			return $text;
		}
		
		$last = "";
		
		while ($last <> $text) {
			$last = $text;
			$text = self::toUtf8(utf8_decode(str_replace(array_keys(self::$_utf8ToWin1252) , array_values(self::$_utf8ToWin1252) , $text)));
		}
		
		$text = self::toUtf8(utf8_decode(str_replace(array_keys(self::$_utf8ToWin1252) , array_values(self::$_utf8ToWin1252) , $text)));
		
		return $text;
	}
	
	static function toUtf8($text) {
		if (is_array($text)) {
			foreach ($text as $k => $v) {
				$text[$k] = self::toUtf8($v);
			}
			return $text;
		} 
		elseif (is_string($text)) {
			$max = strlen($text);
			$buf = "";
			for ($i = 0; $i < $max; $i++) {
				$c1 = $text{$i};
				if ($c1 >= "\xc0") {
					$c2 = $i + 1 >= $max ? "\x00" : $text{$i + 1};
					$c3 = $i + 2 >= $max ? "\x00" : $text{$i + 2};
					$c4 = $i + 3 >= $max ? "\x00" : $text{$i + 3};
					if ($c1 >= "\xc0" & $c1 <= "\xdf") {
						if ($c2 >= "\x80" && $c2 <= "\xbf") {
							$buf.= $c1 . $c2;
							$i++;
						} 
						else {
							$cc1 = (chr(ord($c1) / 64) | "\xc0");
							$cc2 = ($c1 & "\x3f") | "\x80";
							$buf.= $cc1 . $cc2;
						}
					} 
					elseif ($c1 >= "\xe0" & $c1 <= "\xef") {
						if ($c2 >= "\x80" && $c2 <= "\xbf" && $c3 >= "\x80" && $c3 <= "\xbf") {
							$buf.= $c1 . $c2 . $c3;
							$i = $i + 2;
						} 
						else {
							$cc1 = (chr(ord($c1) / 64) | "\xc0");
							$cc2 = ($c1 & "\x3f") | "\x80";
							$buf.= $cc1 . $cc2;
						}
					} 
					elseif ($c1 >= "\xf0" & $c1 <= "\xf7") {
						if ($c2 >= "\x80" && $c2 <= "\xbf" && $c3 >= "\x80" && $c3 <= "\xbf" && $c4 >= "\x80" && $c4 <= "\xbf") {
							$buf.= $c1 . $c2 . $c3;
							$i = $i + 2;
						} 
						else {
							$cc1 = (chr(ord($c1) / 64) | "\xc0");
							$cc2 = ($c1 & "\x3f") | "\x80";
							$buf.= $cc1 . $cc2;
						}
					} 
					else {
						$cc1 = (chr(ord($c1) / 64) | "\xc0");
						$cc2 = (($c1 & "\x3f") | "\x80");
						$buf.= $cc1 . $cc2;
					}
				} 
				elseif (($c1 & "\xc0") == "\x80") {
					if (isset(self::$win1252ToUtf8[ord($c1) ])) {
						$buf.= self::$win1252ToUtf8[ord($c1) ];
					} 
					else {
						$cc1 = (chr(ord($c1) / 64) | "\xc0");
						$cc2 = (($c1 & "\x3f") | "\x80");
						$buf.= $cc1 . $cc2;
					}
				} 
				else {
					$buf.= $c1;
				}
			}
			return $buf;
		} 
		else {
			return $text;
		}
	}
	
	private function _isEmpty($str) {
		$str = trim($str);
		$str = str_replace(' ', '', $str);
		
		if ( $str === '' || $str === NULL) return TRUE;
		else return FALSE;
	}

}