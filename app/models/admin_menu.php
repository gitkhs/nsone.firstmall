<?php
class admin_menu extends CI_Model {
	var $arr_menu = array();
	var $arr_menu2		= array();

	public function __construct(){
		$arr_menu = $this->load_menu_ini();
		$this->get_menu_array($arr_menu);
		$arr_menu2 = $this->load_menu_ini2();
		$this->get_menu_array2($arr_menu2);
	}

	private function load_menu_ini(){
		$arr_menu = parse_ini_file(APPPATH."config/_pc_menu.ini");

		return $arr_menu;
	}

	private function load_menu_ini2(){
		$arr_menu2 = parse_ini_file(APPPATH."config/_pc_menu2.ini", true);

		return $arr_menu2;
	}

	private function get_menu_array($arr_menu){
		foreach($arr_menu as $k=>$v){
			$this->arr_menu[$k]['folders'] = array();

			foreach($v as $k2=>$v2){
				$tmp = explode(":",$v2);
				/*if($tmp[1] == '../coupon/catalog'){
					$tmp[0] .= '(온라인, 오프라인)';//중괄호문구추가@2012-07-02
				}*/

				$tmp2 = explode("/",$tmp[1]);
				if(!in_array(trim($tmp2[1]),$this->arr_menu[$k]['folders'])) $this->arr_menu[$k]['folders'][] = trim($tmp2[1]);

				$this->arr_menu[$k]['childs'][] = array('name'=>$tmp[0],'url'=>$tmp[1],'folder'=>$tmp2[1]);
			}
		}
	}

	private function get_menu_array2($arr_menu2){
		foreach($arr_menu2 as $k=>$v){
			foreach($v as $k2=>$v2){
				if	($k2 == 0)
					$this->arr_menu2['menu_titles'][$k]	= array('name'=>$v2[0],'url'=>$v2[1],'alt'=>$v2[2], 'required'=>$v2[3], 'etype'=>$v2[4], 'url2'=>$v2[5]);
				else
					$this->arr_menu2[$k][]				= array('name'=>$v2[0],'url'=>$v2[1],'alt'=>$v2[2], 'required'=>$v2[3], 'etype'=>$v2[4], 'url2'=>$v2[5], 'lines'=>$v2[6]);
			}
		}
	}

}
?>