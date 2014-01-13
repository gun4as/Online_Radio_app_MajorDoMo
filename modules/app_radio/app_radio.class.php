<?php
/**
* Online Radio Application
*
* module for MajorDoMo project
* @author Fedorov Ivan <4fedorov@gmail.com>
* @copyright Fedorov I.A.
* @version 0.1 January 2014
*/

class app_radio extends module {

/**
* radio
*
* Module class constructor
*
* @access private
*/
function app_radio() {
  $this->name="app_radio";
  $this->title="Онлайн радио";
  $this->module_category="<#LANG_SECTION_APPLICATIONS#>";
  $this->checkInstalled();
}
/**
* saveParams
*
* Saving module parameters
*
* @access public
*/
function saveParams() {
 $p=array();
 if (IsSet($this->id)) {
  $p["id"]=$this->id;
 }
 if (IsSet($this->view_mode)) {
  $p["view_mode"]=$this->view_mode;
 }
 if (IsSet($this->edit_mode)) {
  $p["edit_mode"]=$this->edit_mode;
 }
 if (IsSet($this->tab)) {
  $p["tab"]=$this->tab;
 }
 return parent::saveParams($p);
}
/**
* getParams
*
* Getting module parameters from query string
*
* @access public
*/
function getParams() {
  global $id;
  global $mode;
  global $view_mode;
  global $edit_mode;
  global $tab;
  if (isset($id)) {
   $this->id=$id;
  }
  if (isset($mode)) {
   $this->mode=$mode;
  }
  if (isset($view_mode)) {
   $this->view_mode=$view_mode;
  }
  if (isset($edit_mode)) {
   $this->edit_mode=$edit_mode;
  }
  if (isset($tab)) {
   $this->tab=$tab;
  }
}
/**
* Run
*
* Description
*
* @access public
*/
function run() {
 global $session;
  $out=array();
  if ($this->action=='admin') {
   $this->admin($out);
  } else {
   $this->usual($out);
  }
  if (IsSet($this->owner->action)) {
   $out['PARENT_ACTION']=$this->owner->action;
  }
  if (IsSet($this->owner->name)) {
   $out['PARENT_NAME']=$this->owner->name;
  }
  $out['VIEW_MODE']=$this->view_mode;
  $out['EDIT_MODE']=$this->edit_mode;
  $out['MODE']=$this->mode;
  $out['ACTION']=$this->action;
  if ($this->single_rec) {
   $out['SINGLE_REC']=1;
  }
  $this->data=$out;
  $p=new parser(DIR_TEMPLATES.$this->name."/".$this->name.".html", $this->data, $this);
  $this->result=$p->result;
}
/**
* BackEnd
*
* Module backend
*
* @access public
*/
function admin(&$out) {
	if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
		$out['SET_DATASOURCE']=1;
	}
	if ($this->data_source=='app_radio' || $this->data_source=='') {
		if ($this->view_mode=='' || $this->view_mode=='view_stations') {
			$this->view_stations($out);
		}
		if ($this->view_mode=='edit_stations') {
			$this->edit_stations($out, $this->id);
		}
		if ($this->view_mode=='delete_stations') {
			$this->delete_stations($this->id);
			$this->redirect("?");
		}
		if ($this->view_mode=='import_stations') {
			$this->import_stations($out);
		}
	}
}
/**
* FrontEnd
*
* Module frontend
*
* @access public
*/
function usual(&$out) {

 $this->view_stations($out);
 
 $current_volume=getGlobal('RadioSetting.VolumeLevel');
 $last_stationID=getGlobal('RadioSetting.LastStationID');
 $out['VOLUME']=$current_volume;
 //$out['MODE']=$this->mode;
		
//echo '=>'.$out['MODE'].'<=';
		if($last_stationID){
			 for($i=0;$i<count($out['RESULT']);$i++) {
				if($last_stationID==$out['RESULT'][$i]['ID']){
					$out['RESULT'][$i]['SELECT']=1;
					break;
				}
			} 
		} else {
			$out['RESULT'][0]['SELECT']=1;
		}	
		
	global $ajax;
	if ($ajax!='') {
		global $cmd;
		if ($cmd!='') {
			if (!$this->intCall) {
				echo $cmd.' ';
			}
			global $s_id;
			//echo $s_id;
			if ($s_id!='') {
				for($i=0;$i<count($out['RESULT']);$i++) {
					if($s_id==$out['RESULT'][$i]['ID']){
						$out['PLAY'] = trim($out['RESULT'][$i]['stations']);
						$last_stationID=$out['RESULT'][$i]['ID'];
						setGlobal('RadioSetting.LastStationID', $last_stationID);
						break;
					} 
				}
			} else {
				if($out['RESULT'][0]['ID']){
					$out['PLAY'] = trim($out['RESULT'][0]['stations']);
					$last_stationID=$out['RESULT'][0]['ID'];
					setGlobal('RadioSetting.LastStationID', $last_stationID);
				}
			}
			global $volume;
			if ($volume!='') {
				setGlobal('RadioSetting.VolumeLevel', $volume);
			}
				
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//-----------
			$terminals=SQLSelect("SELECT * FROM terminals WHERE CANPLAY=1 ORDER BY TITLE");
			$terminal=$terminals[0];
		    if ($terminal['PLAYER_USERNAME'] && $terminal['PLAYER_PASSWORD']) {
				curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC ) ;
				curl_setopt($ch, CURLOPT_USERPWD, $terminal['PLAYER_USERNAME'].':'.$terminal['PLAYER_PASSWORD']);
			}

		    if (!$terminal['PLAYER_PORT'] && $terminal['PLAYER_TYPE']=='foobar') {
				$terminal['PLAYER_PORT']='8888';
		    } elseif (!$terminal['PLAYER_PORT'] && $terminal['PLAYER_TYPE']=='xbmc') {
				$terminal['PLAYER_PORT']='8080';
		    } elseif (!$terminal['PLAYER_PORT'] && $terminal['PLAYER_TYPE']=='mpd') {
				$terminal['PLAYER_PORT']='6600';
			} elseif (!$terminal['PLAYER_PORT']) {
				$terminal['PLAYER_PORT']='80';
		    }
			
			if ($terminal['PLAYER_TYPE']=='vlc' || $terminal['PLAYER_TYPE']=='') {
				include(DIR_MODULES.'app_radio/player/vlc.php');
				if ($cmd=='play') {
				
				}
				if ($cmd=='play') {
				
				}
			} elseif ($terminal['PLAYER_TYPE']=='xbmc') {
				include(DIR_MODULES.'app_radio/player/xbmc.php');
			} elseif ($terminal['PLAYER_TYPE']=='foobar') {
				include(DIR_MODULES.'app_radio/player/foobar.php');
			} elseif ($terminal['PLAYER_TYPE']=='vlcweb') {
				include(DIR_MODULES.'app_radio/player/vlcweb.php');
			} elseif ($terminal['PLAYER_TYPE']=='mpd') {
				include(DIR_MODULES.'app_radio/player/mpd.php');
			}
			
			curl_close($ch);
		}
	
		if (!$this->intCall) {
			echo "OK_Radio";
			if ($res) {
				echo $res;
			}
			exit;
		}
	}
}


	function view_stations(&$out) {
		//require(DIR_MODULES.$this->name.'/view_stations.php');
		$table_name='app_radio';
		$res=SQLSelect("SELECT * FROM $table_name");
		if ($res[0][ID]) {
			$out['RESULT']=$res;
		}
	}

	function edit_stations(&$out, $id) {
		//require(DIR_MODULES.$this->name.'/view_stations.php');
		$table_name='app_radio';
		$rec=SQLSelectOne("SELECT * FROM $table_name WHERE ID='$id'");
		
		if ($this->mode=='update') {
		$ok=1;
		  //updating 'stations' (text, required)
		global $stations;
		global $name;
		$rec['stations']=$stations;
		$rec['name']=$name;
			if ($rec['stations']=='' || $rec['name']=='') {
				$out['ERR_stations']=1;
				$ok=0;
			}
			//UPDATING RECORD
			if ($ok) {
				if ($rec['ID']) {
				SQLUpdate($table_name, $rec); // update
				} else {
					$new_rec=1;
					$rec['ID']=SQLInsert($table_name, $rec); // adding new record
				}
			$out['OK']=1;
			} else {
				$out['ERR']=1;
			}
		}
		outHash($rec, $out);
	}
	
	function import_stations(&$out) {
		//require(DIR_MODULES.$this->name.'/app_quotes_import.inc.php');
		$table_name='app_radio';
		if ($this->mode=='update') {
			global $file;
			if (file_exists($file)) {
				$tmp=LoadFile($file);
				//$tmp=str_replace("\r", '', $tmp);
				$lines=mb_split("\n", $tmp);
				$total_lines=count($lines);
				for($i=0;$i<$total_lines;$i++) {
					$rec=array();
					$rec_ok=1;
					list($rec['name'], $rec['stations']) = explode(";", $lines[$i]);
					if ($rec['stations']=='') {
						$rec_ok=0;
					}
					if ($rec_ok) {
						$old=SQLSelectOne("SELECT ID FROM ".$table_name." WHERE stations LIKE '".DBSafe($rec['stations'])."'");
						if ($old['ID']) {
						$rec['ID']=$old['ID'];
							SQLUpdate($table_name, $rec);
						} else {
							SQLInsert($table_name, $rec);
						}
						$out["TOTAL"]++;
					}
				}
			} else {
				$out['ERR']=1;
			}
		}
	}
	
	function delete_stations($id) {
		$table_name='app_radio';
		$rec=SQLSelectOne("SELECT * FROM $table_name WHERE ID='$id'");
		SQLExec("DELETE FROM $table_name WHERE ID='".$rec['ID']."'");
	}
/**
* Install
*
* Module installation routine
*
* @access private
*/
 function install($parent_name="") {
	$className='Radio';
	$objectName='RadioSetting';
	//$propertis=array;
	// $propertis['NAME']=array('LastStationID','VolumeLevel');
	// $propertis['VALUE']=array(0,0);
	
	
	// $propertis=array('NAME' => array('LastStationID',
									 // 'VolumeLevel'
									 // ),
					 // 'VALUE'=> array(0,
									 // 0
									 // )
					// );
	
	 $propertis=array('LastStationID','VolumeLevel');

	$rec=SQLSelectOne("SELECT ID FROM classes WHERE TITLE LIKE '".DBSafe($className)."'");
	if (!$rec['ID']) {
		$rec=array();
		$rec['TITLE']=$className;
		//$rec['PARENT_LIST']='0';
		$rec['DESCRIPTION']='Онлайн радио';
		$rec['ID']=SQLInsert('classes',$rec);
		
	}

	$obj_rec=SQLSelectOne("SELECT ID FROM objects WHERE CLASS_ID='".$rec['ID']."' AND TITLE LIKE '".DBSafe($objectName)."'");
	if (!$obj_rec['ID']) {
		$obj_rec=array();
		$obj_rec['CLASS_ID']=$rec['ID'];
		$obj_rec['TITLE']=$objectName;
		$obj_rec['DESCRIPTION']='Настройки';
		$obj_rec['ID']=SQLInsert('objects',$obj_rec);
	}
	
	for($i=0; $i<count($propertis); $i++){
	$prop_rec=SQLSelectOne("SELECT ID FROM properties WHERE OBJECT_ID='".$obj_rec['ID']."' AND TITLE LIKE '".DBSafe($propertis[$i])."'");
		if (!$prop_rec['ID']) {
			$prop_rec=array();
			$prop_rec['TITLE']=$propertis[$i];
			$prop_rec['OBJECT_ID']=$obj_rec['ID'];
			$prop_rec['ID']=SQLInsert('properties',$prop_rec);
		}
	//$pvalues_rec=SQLSelectOne("SELECT ID FROM properties WHERE OBJECT_ID='".$obj_rec['ID']."' AND TITLE LIKE '".DBSafe($propertiName[$i])."'");
	}
  parent::install($parent_name);
 }
 
 function dbInstall($data) {

  $data = <<<EOD
 app_radio: ID int(10) unsigned NOT NULL auto_increment
 app_radio: stations text
 app_radio: name text
EOD;
  parent::dbInstall($data);
 }
// --------------------------------------------------------------------
}
?>