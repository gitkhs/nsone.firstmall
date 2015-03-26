<?php

class locationmodel extends CI_Model {

	/* 브랜ㅡ 목록 반환  */
	public function get_all($arrWhere=array()) {
		$sql = "SELECT * FROM `fm_location`";

		if($arrWhere){
			$sql .= " where " . implode(" and ",$arrWhere);
		}

		$sql .=" ORDER BY `position` ASC";

		$query = $this->db->query($sql);
		foreach ($query->result_array() as $row){
			$returnArr[] = $row;
		}
		//$query->free_result();
		return $returnArr;
	}

	/* 지역 목록 Depth 구분하여 반환 (프론트 출력용도) */
	public function get_location_view($location_codes=array(),$maxDepth=4,$division=''){

		/* 지역 목록  */
		$params = array("level >= 2");
		if($division=='catalog' || $division=='searchForm'){
			$params[] = "hide_in_navigation != '1'";
		}elseif($division=='gnb'){
			$params[] = "hide_in_gnb != '1'";
		}else{
			$params[] = "hide != '1'";
		}
		if($location_codes) $params[] = "location_code in ('".implode("','",$location_codes)."')";
		if($maxDepth<4) $params[] = "level <= '".($maxDepth+1)."'";
		$location_list = $this->get_all($params);

		/* 텍스트,이미지 효과 */
		if(is_array($location_list)) $location_list = $this->design_set($location_list,$division);

		/* Depth별로 나눔 */
		$location = array();
		$location = divisionLocationDepths($location_list,$location);

		return $location;
	}

	public function get_list($code,$arrWhere=array()) {
		if( $code ){
			$level = strlen($code) / 4 + 2;
			$where[] = "`location_code` like ?";
			$whereVal[] = $code."%";
			$where[] = "`level` = ?";
			$whereVal[] = $level;
		}else{
			$where[] = "`level` = ?";
			$whereVal[] = "2";
		}

		$whereStr = implode(' and ',$where);

		if($arrWhere){
			$whereStr .= " and " . implode(" and ",$arrWhere);
		}

		$query = "select * from `fm_location` where $whereStr order by `position` asc, `left` asc";
		$query = $this->db->query($query,$whereVal);
		$result = array();
		foreach ($query->result_array() as $row){
			$result[] = $row;
		}
		return $result;
	}

	public function get_admin_list($code,$arrWhere=array()) {
		if( $code ){
			$level = strlen($code) / 4 + 2;
			$where[] = "`location_code` like ?";
			$whereVal[] = $code."%";
			$where[] = "`level` = ?";
			$whereVal[] = $level;
		}else{
			$where[] = "`level` = ?";
			$whereVal[] = "2";
		}

		$whereStr = implode(' and ',$where);

		if($arrWhere){
			$whereStr .= " and " . implode(" and ",$arrWhere);
		}

		$query = "select `id`,`title`,`location_code` from `fm_location` where $whereStr order by `position` asc, `left` asc";
		$query = $this->db->query($query,$whereVal);
		$result = array();
		foreach ($query->result_array() as $row){
			$result[] = $row;
		}
		return $result;
	}

	public function get_location_data($code=''){

		if($code){
			$this->db->where('location_code',$code);
		}else{
			$this->db->where('level','2');
			$this->db->order_by('position asc');
			$this->db->limit(1);
		}

		$query = $this->db->get('fm_location');
		$locationData = $query->row_array();

		return $locationData;
	}

	public function get_location_name($code) {
		$result = false;
		for($i=4;$i<=strlen($code);$i+=4){
			$codecd = substr($code,0,$i);
			$sql = "SELECT `title`,'' as `location_goods_code`  FROM `fm_location` where `location_code`='$codecd'";
			$query = $this->db->query($sql);
			foreach ($query->result_array() as $row){
				$arr[] = ($row['location_goods_code'])?$row['title'].' ('.$row['location_goods_code'].')':$row['title'];
			}
		}
		if($arr) $result = implode(" > ",$arr);
		//$query->free_result();
		return $result;
	}
	
	//지역코드 
	public function get_location_goods_code($code,$type='view') {
		$result = false;
		for($i=4;$i<=strlen($code);$i+=4){
			$codecd = substr($code,0,$i);
			if($type == 'modify' && strlen($code) == $i ) break;
			$sql = "SELECT '' as `location_goods_code` FROM `fm_location` where `location_code`='$codecd'";
			$query = $this->db->query($sql);
			foreach ($query->result_array() as $row){
				$arr[] = $row['location_goods_code'];
			}
		}
		if($arr) $result = implode(" > ",$arr);
		return $result;
	}


	public function get_location_name_href($code) {
		$result = false;
		for($i=4;$i<=strlen($code);$i+=4){
			$codecd = substr($code,0,$i);
			$sql = "SELECT `title` FROM `fm_location` where `location_code`='$codecd'";
			$query = $this->db->query($sql);
			foreach ($query->result_array() as $row){
				$arr[] = '<a href="/goods/catalog?code='.$codecd.'" target="_blank">'.$row['title'].'</a>';
			}
		}
		if($arr) $result = implode(" > ",$arr);		
		//$query->free_result();
		return $result;
	}

	public function one_location_name($code) {
		$sql = "SELECT `title` FROM `fm_location` where `location_code`='$code'";
		$query = $this->db->query($sql);
		list($row) = $query->result_array();
		return $row['title'];
	}

	public function get_location_code($id) {
		$sql = "SELECT `location_code` FROM `fm_location` where `id`='$id'";
		$query = $this->db->query($sql);
		list($row) = $query->result_array();
		return $row['location_code'];
	}

	// 카테고리 코드를 차수 별로 나눈다
	public function split_location($code){
		for($i=4;$i<=strlen($code);$i+=4){
			$location[] = substr($code,0,$i);
		}
		return $location;
	}

	public function get_next_positon($parent_id){
		$query = "".
			"select max(`position`) max from `fm_location` ".
			"where `parent_id` = ?";

		$query = $this->db->query($query,array($parent_id));
		list($tmp) = $query->result_array();

		if($tmp['max']){
			$tmp['max'] += 1;
		}
		return $tmp['max'];
	}


	public function get_next_location(){
		$qry = "select max(substring(location_code,1,4))+1 as location_code from fm_location";
		$query = $this->db->query($qry);
		list($cate) = $query->result_array();
		if(strlen($cate['location_code'])<4){
			for($i=0;$i<(4-strlen($cate['location_code']));$i++){
				$location .= "0";
			}
			$location .= $cate['location_code'];
		}
		return $location;
	}


	public function get_next_left(){
		$qry = "select max(`right`)+1 as max_left from fm_location;";
		$query = $this->db->query($qry);
		list($cate) = $query->result_array();
		return $cate['max_left'];
	}

	public function get_location_groups($location){
		$qry = "select * from fm_location_group where location_code=?;";
		$query = $this->db->query($qry,$location);
		return $query->result_array();
	}

	public function get_location_group_for_goods($goods_seq)
	{
		$query = "select group_seq from fm_location_group where
			location_code = (select location_code from fm_location_link where goods_seq=? and link=1 order by length(location_code) desc limit 1)";
		$query = $this->db->query($query,$goods_seq);
		foreach($query->result_array() as $data){
			$result[] = $data['group_seq'];
		}
		return $result;
	}
	
	/* 지역 목록 반환  */
	public function get_location_title() {
		$sql = "SELECT * FROM `fm_location` where id='2' and  parent_id='1'";
		
		$query = $this->db->query($sql);
		$data = $query->row_array();
			
		$return = $data['title'];
		//$query->free_result();
		return $return;
	}
	
	public function get_location_recommend_display_seq($locationCode){
		$query = $this->db->query("select * from fm_location where level>0 and location_code = ?",$locationCode);
		$locationData = $query->row_array();
		
		$query = $this->db->query("select * from fm_design_display where display_seq = ?",$locationData['recommend_display_seq']);
		$displayData = $query->row_array();

		if($locationData['recommend_display_seq'] && $displayData){
			$query = "update fm_design_display set kind='location' where display_seq=?";
			$this->db->query($query,array($locationData['recommend_display_seq']));
			return $locationData['recommend_display_seq'];
		}else{
			$data = array(
				'kind' => 'location',
				'count_h' => '1',
				'regdate' => date('Y-m-d H:i:s'),
			);
			$this->db->insert('fm_design_display', $data);
			$recommend_display_seq = $this->db->insert_id();
			
			$data = array(
				'display_seq' => $recommend_display_seq,
				'display_tab_index' => '0',
			);
			$this->db->insert('fm_design_display_tab', $data);
			
			$query = "update fm_location set recommend_display_seq=? where location_code=?";
			$this->db->query($query,array($recommend_display_seq,$locationCode));

			return $recommend_display_seq;
		}
	}
	
	public function set_location_recommend($locationCode,$params){
		$this->load->model('goodsdisplay');
		
		$recommend_display_seq = $this->get_location_recommend_display_seq($locationCode);
		
		$recommend_image_decoration = isset($params['recommend_image_decoration']) ? $params['recommend_image_decoration'] : '';
		$recommend_info_setting = isset($params['recommend_info_setting']) ? $params['recommend_info_setting'] : array();
		
		$recommendData = $this->goodsdisplay->get_display($recommend_display_seq);
		
		$params['style'] = $params['recommend_style'];
		$params['count_w'] = $params['recommend_count_w'];
		$params['count_h'] = $params['recommend_count_h'];
		$params['image_size'] = $params['recommend_image_size'];
		$params['text_align'] = $params['recommend_text_align'];
		$params['image_decorations'] = $recommend_image_decoration;
		$params['info_settings'] = "[".implode(",",$recommend_info_setting)."]";
		
		$data = filter_keys($params, $this->db->list_fields('fm_design_display'));
		unset($data['auto_use']);
		$this->db->update('fm_design_display', $data, "display_seq = {$recommend_display_seq}");

		$this->db->query("delete from fm_design_display_tab where display_seq=?",$recommend_display_seq);
		if(!$params['tab_title']) $params['tab_title'] = array('');
		if(count($params['tab_title'])>1){
			foreach($params['tab_title'] as $tab_index => $tab_title){
				$tab_data = array();
				$tab_data['display_seq'] = $recommend_display_seq;
				$tab_data['display_tab_index'] = $tab_index;

				$tab_data['tab_title'] = count($params['tab_title']) > 1 ? $tab_title : '';

				// 이미지업로드
				if($params['popup_tab_design_kind']=='image'){
					if($_FILES['new_tab_title_img']['tmp_name'][$tab_index]){
						$config['file_name'] = "tab_{$recommend_display_seq}_{$tab_index}";
						$this->upload->initialize($config);
						$this->upload->do_upload('new_tab_title_img',$tab_index);
						$res = $this->upload->data();
						$tab_data['tab_title_img'] = $res['file_name']?$res['file_name']:$params['tab_title_img'][$tab_index];
					}else{
						$tab_data['tab_title_img'] = $params['tab_title_img'][$tab_index];
					}

					if($_FILES['new_tab_title_img_on']['tmp_name'][$tab_index]){
						$config['file_name'] = "tab_{$recommend_display_seq}_{$tab_index}_on";
						$this->upload->initialize($config);
						$this->upload->do_upload('new_tab_title_img_on',$tab_index);
						$res = $this->upload->data();
						$tab_data['tab_title_img_on'] = $res['file_name']?$res['file_name']:$params['tab_title_img_on'][$tab_index];
					}else{
						$tab_data['tab_title_img_on'] = $params['tab_title_img_on'][$tab_index];
					}
				}

				$tab_data['contents_type'] = $params['contents_type'][$tab_index];
				$tab_data['auto_use'] = $params['contents_type'][$tab_index] == 'auto' ? 'y' : 'n';
				$tab_data['auto_criteria'] = $params['auto_criteria'][$tab_index];
				$tab_data['tab_contents'] = $params['tab_contents'][$tab_index];
				$tab_data['tab_contents_mobile'] = $params['tab_contents_mobile'][$tab_index];
				$this->db->insert('fm_design_display_tab', $tab_data);
			}
		}elseif(isset($params['contents_type'])){
			$tab_data = array();
			$tab_data['display_seq'] = $recommend_display_seq;
			$tab_data['display_tab_index'] = 0;
			$tab_data['tab_title'] = '';
			$tab_data['tab_title_img'] = '';
			$tab_data['tab_title_img_on'] = '';
			$tab_data['contents_type'] = $params['contents_type'][0];
			$tab_data['auto_use'] = $params['contents_type'][0] == 'auto' ? 'y' : 'n';
			$tab_data['auto_criteria'] = $tab_data['auto_use'] == 'y' ? $params['auto_criteria'][0] : '';
			$tab_data['tab_contents'] = $params['tab_contents'][0];
			$tab_data['tab_contents_mobile'] = $params['tab_contents_mobile'][0];
			$this->db->insert('fm_design_display_tab', $tab_data);
		}

		$this->db->query("delete from fm_design_display_tab_item where display_seq=?",$recommend_display_seq);
		if(isset($params['auto_goods_seqs'])){
			foreach($params['auto_goods_seqs'] as $tab_index=>$auto_goods_seqs){
				$arr_goods_seqs = explode(",",$auto_goods_seqs);
				foreach($arr_goods_seqs as $goods_seq){
					if($goods_seq){
						$data = array(
							"display_seq" => $recommend_display_seq,
							"display_tab_index" => $tab_index,
							"goods_seq" => $goods_seq
						);
						$this->db->insert('fm_design_display_tab_item', $data);
					}
				}
			}
		}
		
		return $recommend_display_seq;
	}
	
	public function childset_location($div=null,$location_code=''){
		
		$query = $this->db->query("select * from fm_location where level>0 and ifnull(location_code,'')=?",$location_code);
		$locationData = $query->row_array();
		
		switch($div){
			case "top_html":
				$this->db->query("update fm_location set top_html = ?, update_date=now() where location_code like '{$location_code}%' and location_code!='{$location_code}'",$locationData['top_html']);
			break;
			case "recommend":
				$this->load->model('goodsdisplay');
				
				$query = $this->db->query("select * from fm_design_display where display_seq = ?",$locationData['recommend_display_seq']);
				$recommend_display_data = $query->row_array();
				
				$query = $this->db->query("select * from fm_location where location_code like '{$location_code}%' and length(location_code)>".strlen($location_code));
				foreach($query->result_array() as $childLocationData){
					
					if($childLocationData['recommend_display_seq']==$locationData['recommend_display_seq']) continue;
					
					/* 하위카테고리에 설정된 상품디스플레이 제거*/
					$this->db->query("delete from fm_design_display where display_seq=?",$childLocationData['recommend_display_seq']);
					$this->db->query("delete from fm_design_display_tab where display_seq=?",$childLocationData['recommend_display_seq']);
					$this->db->query("delete from fm_design_display_tab_item where display_seq=?",$childLocationData['recommend_display_seq']);
					
					/* 사용시 하위복사 */
					if($locationData['recommend_display_seq']){
						$child_recommend_display_seq = $this->goodsdisplay->copy_display($locationData['recommend_display_seq']);
						if($child_recommend_display_seq){
							$this->db->query("update fm_location set recommend_display_seq='{$child_recommend_display_seq}', update_date=now() where location_code = '{$childLocationData['location_code']}'");
						}
									
					/* 미사용시 하위제거 */
					}else{
						if($childLocationData['recommend_display_seq']){
							$this->db->query("update fm_location set recommend_display_seq='', update_date=now() where location_code = '{$childLocationData['location_code']}'");
						}
					}
					
				}
			break;
			case "location":
				$data = array(
					'list_default_sort'			=> $locationData['list_default_sort'],
					'list_style'				=> $locationData['list_style'],
					'list_count_w'				=> $locationData['list_count_w'],
					'list_count_w_lattice_b'	=> $locationData['list_count_w_lattice_b'],
					'list_count_h'				=> $locationData['list_count_h'],
					'list_image_size'			=> $locationData['list_image_size'],
					'list_text_align'			=> $locationData['list_text_align'],
					'list_image_decorations'	=> $locationData['list_image_decorations'],
					'list_info_settings'		=> $locationData['list_info_settings'],
					'list_goods_status'			=> $locationData['list_goods_status'],
					'update_date'				=> date('Y-m-d H:i:s'),
				);
				$this->db->update('fm_location', $data, "location_code like '{$location_code}%' and length(location_code)>".strlen($location_code));
			break;
		}
		
	}

	public function chkDupleSort($locationCode){
		$query			= $this->db->query("select count(location_link_seq) as cnt from fm_location_link where location_code = ? group by sort having cnt > 1 limit 1", $locationCode);
		$chkData		= $query->row_array();
		if	($chkData)	return true;
		else			return false;
	}

	public function reSortAll($locationCode){

		$query = $this->db->query("select l.* from fm_goods_option o, fm_goods g, fm_location_link l where g.goods_view='look' AND g.goods_type = 'goods' and o.goods_seq=g.goods_seq and l.goods_seq = g.goods_seq and o.default_option ='y' and l.location_code = ? group by g.goods_seq order by l.sort asc, g.regist_date desc", $locationCode);
		$sort	= 0;
		foreach($query->result_array() as $linkData){
			if	($linkData['location_link_seq']){
				$this->db->query("update fm_location_link set sort = ? where location_link_seq = ? ", array($sort, $linkData['location_link_seq']));
				$sort++;
			}
		}

		config_save('mig_sort_location',array($locationCode=>'Y'));
	}

	public function getSortValue($locationCode, $type){
		switch($type){
			case 'min':
				$query		= $this->db->query("select min(l.sort) sortVal from fm_goods_option o, fm_goods g, fm_location_link l where g.goods_view='look' AND g.goods_type = 'goods' and o.goods_seq=g.goods_seq and l.goods_seq = g.goods_seq and o.default_option ='y' and l.location_code = ? ", $locationCode);
				$sortData	= $query->row_array();
			break;
			case 'max':
				$query		= $this->db->query("select max(l.sort) sortVal from fm_goods_option o, fm_goods g, fm_location_link l where g.goods_view='look' AND g.goods_type = 'goods' and o.goods_seq=g.goods_seq and l.goods_seq = g.goods_seq and o.default_option ='y' and l.location_code = ? ", $locationCode);
				$sortData	= $query->row_array();
			break;
			case 'cnt':
				$query		= $this->db->query("select count(l.location_link_seq) sortVal from fm_goods_option o, fm_goods g, fm_location_link l where g.goods_view='look' AND g.goods_type = 'goods' and o.goods_seq=g.goods_seq and l.goods_seq = g.goods_seq and o.default_option ='y' and l.location_code = ? group by g.goods_seq", $locationCode);
				$sortData	= $query->row_array();
			break;
		}

		return $sortData['sortVal'];
	}

	public function rangeUpdateSort($locationCode, $sSort, $eSort, $addSort){
		if	(!is_null($sSort))
			$addWhere	.= " and sort > ".$sSort." ";
		if	(!is_null($eSort))
			$addWhere	.= " and sort < ".$eSort." ";
		$this->db->query("update fm_location_link set sort=sort".$addSort." where location_code = ? ".$addWhere, $locationCode);
	}

	public function chgLocationSort($location_link_seq, $sort){
		if	($location_link_seq){
			$this->db->query("update fm_location_link set sort=? where location_link_seq = ? ", array($sort, $location_link_seq));
		}
	}

	public function design_set($childLocationData,$division='catalog'){

		if($division=='searchForm') $division = 'catalog';

		$division = $division ? '_'.$division.'_' : '_';

		foreach($childLocationData as $k=>$row){
			$childLocationData[$k]['title'] = htmlspecialchars($childLocationData[$k]['title']);
			$childLocationData[$k]['ori_title'] = $childLocationData[$k]['title'];

			if($childLocationData[$k]['node'.$division.'type']=='text'){
				if($childLocationData[$k]['node'.$division.'text_normal']){
					$attrStyle = font_decoration_attr($childLocationData[$k]['node'.$division.'text_normal'],'css','style');
					$attrOnmouseover = font_decoration_attr($childLocationData[$k]['node'.$division.'text_over'],'script','onmouseover');
					$attrOnmouseout = font_decoration_attr($childLocationData[$k]['node'.$division.'text_normal'],'script','onmouseout');

					$childLocationData[$k]['title'] = "<span {$attrStyle} {$attrOnmouseover} {$attrOnmouseout}>{$childLocationData[$k]['title']}</span>";
				}
			}

			if($childLocationData[$k]['node'.$division.'type']=='image'){
				$attrSrc ='';
				$attrOnmouseover ='';
				$attrOnmouseout ='';
				if($childLocationData[$k]['node'.$division.'image_normal']){
					$attrSrc = 'src="'.$childLocationData[$k]['node'.$division.'image_normal'].'"';
				}
				if($childLocationData[$k]['node'.$division.'image_over']){
					$attrOnmouseover = 'onmouseover="this.src=\''.$childLocationData[$k]['node'.$division.'image_over'].'\'"';
					$attrOnmouseout = 'onmouseout="this.src=\''.$childLocationData[$k]['node'.$division.'image_normal'].'\'"';
				}

				$childLocationData[$k]['title'] = "<img {$attrSrc} {$attrOnmouseover} {$attrOnmouseout} />";
			}

			$childLocationData[$k]['name'] = $childLocationData[$k]['title'];
		}

		return $childLocationData;
	}

	public function get_represent_location_for_goods($goods_seq){

		$query = "select b.* from fm_location as b, fm_location_link as bl where
					b.location_code = bl.location_code and bl.link = 1 and bl.goods_seq = ? 
					limit 1 ";

		$query = $this->db->query($query,$goods_seq);
		$result	= $query->result_array();

		return $result[0];
	}
	
	public function getChildLocation($code,$exactly=false,$division='catalog'){
		$arrWhere = array("level >= 2");
		
		if($division=='catalog' || $division=='searchForm'){
			$arrWhere[] = "hide_in_navigation != '1'";
		}elseif($division=='gnb'){
			$arrWhere[] = "hide_in_gnb != '1'";
		}else{
			$arrWhere[] = "hide != '1'";
		}
		
		$childLocationData = $this->get_list($code,$arrWhere);
		
		if(!$childLocationData && !$exactly /* && strlen($code)>4*/){
			$childLocationData = $this->get_list(substr($code,0,strlen($code)-4),$arrWhere);
		}
	
		$childLocationData = $this->design_set($childLocationData,$division);

		return $childLocationData;
	}
	
	public function getChildCategory($code){
		if($code){
			$sqlInner = "
				select b.category_code from fm_location_link as a
					inner join fm_category_link as b on (a.location_code=? and a.goods_seq = b.goods_seq and b.link=1)
					inner join fm_goods as c on (b.goods_seq=c.goods_seq and c.goods_view='look')
	
			";
			if(!empty($this->categoryData['list_goods_status'])) $sqlInner .= " where c.goods_status in ('".str_replace('|',"','",$this->categoryData['list_goods_status'])."')";
			$sql = "
				select * from (
					{$sqlInner}
					group by b.category_code
				) a
				inner join fm_category as b on a.category_code = b.category_code
			";
			$query = $this->db->query($sql,$code);
			$goodsCategories = $query->result_array();
		}else{
			$this->load->model('categorymodel');
			$goodsCategories = $this->categorymodel->getChildCategory('');
		}

		$goodsCategories = $this->design_set($goodsCategories,'searchForm');

		return $goodsCategories;
	}

	public function getChildBrand($code){
		if($code){
			$sqlInner = "
				select b.category_code from fm_location_link as a
					inner join fm_brand_link as b on (a.location_code=? and a.goods_seq = b.goods_seq and b.link=1)
					inner join fm_goods as c on (b.goods_seq=c.goods_seq and c.goods_view='look')
	
			";
			if(!empty($this->categoryData['list_goods_status'])) $sqlInner .= " where c.goods_status in ('".str_replace('|',"','",$this->categoryData['list_goods_status'])."')";
			$sql = "
				select * from (
					{$sqlInner}
					group by b.category_code
				) a
				inner join fm_brand as b on a.category_code = b.category_code
			";
			$query = $this->db->query($sql,$code);
			$goodsBrands = $query->result_array();
		}else{
			$this->load->model('brandmodel');
			$goodsBrands = $this->brandmodel->getChildBrand('');
		}

		$goodsBrands = $this->design_set($goodsBrands,'searchForm');

		return $goodsBrands;
	}
}

/* End of file location.php */
/* Location: ./app/models/location */