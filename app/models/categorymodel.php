<?php
class categorymodel extends CI_Model {

	/* 카테고리 목록 반환  */
	public function get_all($arrWhere=array()) {
		$sql = "SELECT * FROM `fm_category`";

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

	/* 카테고리 목록 Depth 구분하여 반환 (프론트 출력용도) */
	public function get_category_view($category_codes=array(),$maxDepth=4,$division=''){

		/* 카테고리 목록  */
		$params = array("level >= 2");
		if($division=='catalog' || $division=='searchForm'){
			$params[] = "hide_in_navigation != '1'";
		}elseif($division=='gnb'){
			$params[] = "hide_in_gnb != '1'";
		}else{
			$params[] = "hide != '1'";
		}
		if($category_codes) $params[] = "category_code in ('".implode("','",$category_codes)."')";
		if($maxDepth<4) $params[] = "level <= '".($maxDepth+1)."'";
		$category_list = $this->get_all($params);

		/* 텍스트,이미지 효과 */
		if(is_array($category_list)) $category_list = $this->design_set($category_list,$division);

		/* Depth별로 나눔 */
		$category = array();
		$category = divisionCategoryDepths($category_list,$category);

		return $category;
	}

	public function get_list($code,$arrWhere=array()) {
		if( $code ){
			$level = strlen($code) / 4 + 2;
			$where[] = "`category_code` like ?";
			$whereVal[] = $code."%";
			$where[] = "`level` = ?";
			$whereVal[] = $level;
		}else{
			$where[] = "`level` = ?";
			$whereVal[] = "2";
		}
		$where[] = "`hide` != '1'";

		$whereStr = implode(' and ',$where);

		if($arrWhere){
			$whereStr .= " and " . implode(" and ",$arrWhere);
		}

		$query = "select * from `fm_category` where $whereStr order by `position` asc, `left` asc";
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
			$where[] = "`category_code` like ?";
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

		$query = "select `id`,`title`,`category_code` from `fm_category` where $whereStr order by `position` asc, `left` asc";
		$query = $this->db->query($query,$whereVal);
		$result = array();
		foreach ($query->result_array() as $row){
			$result[] = $row;
		}
		return $result;
	}

	public function get_category_data($code=''){

		if($code){
			$this->db->where('category_code',$code);
			$query = $this->db->get('fm_category');
			$categoryData = $query->row_array();
		}else{
			$this->db->where('level','2');
			$this->db->order_by('position asc');
			$this->db->limit(1);
			$query = $this->db->get('fm_category');
			$categoryData = $query->row_array();
			unset($categoryData['category_code']);
			unset($categoryData['title']);
		}

		return $categoryData;
	}

	public function get_category_name($code) {
		$result = false;
		for($i=4;$i<=strlen($code);$i+=4){
			$codecd = substr($code,0,$i);
			$sql = "SELECT `title`,`category_goods_code` FROM `fm_category` where `category_code`='$codecd'";
			$query = $this->db->query($sql);
			foreach ($query->result_array() as $row){
				$arr[] = ($row['category_goods_code'])?$row['title'].' ('.$row['category_goods_code'].')':$row['title'];
			}
		}
		if($arr) $result = implode(" > ",$arr);
		//$query->free_result();
		return $result;
	}

	public function get_category_goods_code($code,$type='view') {
		$result = false;
		for($i=4;$i<=strlen($code);$i+=4){
			$codecd = substr($code,0,$i);
			if($type == 'modify' && strlen($code) == $i ) break;
			$sql = "SELECT `category_goods_code` FROM `fm_category` where `category_code`='$codecd'";
			$query = $this->db->query($sql);
			foreach ($query->result_array() as $row){
				$arr[] = $row['category_goods_code'];
			}


		}
		if($arr) $result = implode(" > ",$arr);
		//$query->free_result();
		return $result;
	}

	public function get_category_name_href($code) {
		$result = false;
		for($i=4;$i<=strlen($code);$i+=4){
			$codecd = substr($code,0,$i);
			$sql = "SELECT `title` FROM `fm_category` where `category_code`='$codecd'";
			$query = $this->db->query($sql);
			foreach ($query->result_array() as $row){
				$arr[] = '<a href="/goods/catalog?code='.$codecd.'" target="_blank">'.$row['title'].'</a>';
			}
		}
		if($arr) $result = implode(" > ",$arr);
		//$query->free_result();
		return $result;
	}

	public function one_category_name($code) {
		$sql = "SELECT `title` FROM `fm_category` where `category_code`='$code'";
		$query = $this->db->query($sql);
		list($row) = $query->result_array();
		return $row['title'];
	}

	public function get_category_code($id) {
		$sql = "SELECT `category_code` FROM `fm_category` where `id`='$id'";
		$query = $this->db->query($sql);
		list($row) = $query->result_array();
		return $row['category_code'];
	}

	// 카테고리 코드를 차수 별로 나눈다
	public function split_category($code){
		for($i=4;$i<=strlen($code);$i+=4){
			$category[] = substr($code,0,$i);
		}
		return $category;
	}

	public function get_next_positon($parent_id){
		$query = "".
			"select max(`position`) max from `fm_category` ".
			"where `parent_id` = ?";

		$query = $this->db->query($query,array($parent_id));
		list($tmp) = $query->result_array();

		if($tmp['max']){
			$tmp['max'] += 1;
		}
		return $tmp['max'];
	}


	public function get_next_category(){
		$qry = "select max(substring(category_code,1,4))+1 as category_code from fm_category";
		$query = $this->db->query($qry);
		list($cate) = $query->result_array();
		$category = sprintf("%04d",$cate['category_code']);
		return $category;
	}


	public function get_next_left(){
		$qry = "select max(`right`)+1 as max_left from fm_category;";
		$query = $this->db->query($qry);
		list($cate) = $query->result_array();
		return $cate['max_left'];
	}

	public function get_category_groups($category){
		$qry = "select * from fm_category_group where category_code=?;";
		$query = $this->db->query($qry,$category);
		return $query->result_array();
	}

	public function get_category_group_for_goods($goods_seq)
	{
		$query = "select group_seq from fm_category_group where
			category_code = (select category_code from fm_category_link where goods_seq=? and link=1 order by length(category_code) desc limit 1  )";
		$query = $this->db->query($query,$goods_seq);
		foreach($query->result_array() as $data){
			$result[] = $data['group_seq'];
		}
		return $result;
	}
	
	public function get_category_recommend_display_seq($categoryCode){
		$query = $this->db->query("select * from fm_category where level>0 and category_code = ?",$categoryCode);
		$categoryData = $query->row_array();
		
		$query = $this->db->query("select * from fm_design_display where display_seq = ?",$categoryData['recommend_display_seq']);
		$displayData = $query->row_array();

		if($categoryData['recommend_display_seq'] && $displayData){
			$query = "update fm_design_display set kind='category' where display_seq=?";
			$this->db->query($query,array($categoryData['recommend_display_seq']));
			return $categoryData['recommend_display_seq'];
		}else{
			$data = array(
				'kind' => 'category',
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
			
			$query = "update fm_category set recommend_display_seq=? where category_code=?";
			$this->db->query($query,array($recommend_display_seq,$categoryCode));

			return $recommend_display_seq;
		}
	}
	
	public function set_category_recommend($categoryCode,$params){
		$this->load->model('goodsdisplay');
		
		$recommend_display_seq = $this->get_category_recommend_display_seq($categoryCode);
		
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
	
	public function childset_category($div=null,$category_code=''){
		
		$query = $this->db->query("select * from fm_category where level>0 and ifnull(category_code,'')=?",$category_code);
		$categoryData = $query->row_array();
		
		switch($div){
			case "top_html":
				$this->db->query("update fm_category set top_html = ?, update_date=now() where category_code like '{$category_code}%' and category_code!='{$category_code}'",$categoryData['top_html']);
			break;
			case "recommend":
				$this->load->model('goodsdisplay');
				
				$query = $this->db->query("select * from fm_design_display where display_seq = ?",$categoryData['recommend_display_seq']);
				$recommend_display_data = $query->row_array();
				
				$query = $this->db->query("select * from fm_category where category_code like '{$category_code}%' and length(category_code)>".strlen($category_code));
				foreach($query->result_array() as $childCategoryData){
					
					if($childCategoryData['recommend_display_seq']==$categoryData['recommend_display_seq']) continue;
					
					/* 하위카테고리에 설정된 상품디스플레이 제거*/
					$this->db->query("delete from fm_design_display where display_seq=?",$childCategoryData['recommend_display_seq']);
					$this->db->query("delete from fm_design_display_tab where display_seq=?",$childCategoryData['recommend_display_seq']);
					$this->db->query("delete from fm_design_display_tab_item where display_seq=?",$childCategoryData['recommend_display_seq']);
					
					/* 사용시 하위복사 */
					if($categoryData['recommend_display_seq']){
						$child_recommend_display_seq = $this->goodsdisplay->copy_display($categoryData['recommend_display_seq']);
						if($child_recommend_display_seq){
							$this->db->query("update fm_category set recommend_display_seq='{$child_recommend_display_seq}', update_date=now() where category_code = '{$childCategoryData['category_code']}'");
						}
									
					/* 미사용시 하위제거 */
					}else{
						if($childCategoryData['recommend_display_seq']){
							$this->db->query("update fm_category set recommend_display_seq='', update_date=now() where category_code = '{$childCategoryData['category_code']}'");
						}
					}
					
				}
			break;
			case "category":
				$data = array(
					'list_default_sort'			=> $categoryData['list_default_sort'],
					'list_style'				=> $categoryData['list_style'],
					'list_count_w'				=> $categoryData['list_count_w'],
					'list_count_w_lattice_b'	=> $categoryData['list_count_w_lattice_b'],
					'list_count_h'				=> $categoryData['list_count_h'],
					'list_image_size'			=> $categoryData['list_image_size'],
					'list_text_align'			=> $categoryData['list_text_align'],
					'list_image_decorations'	=> $categoryData['list_image_decorations'],
					'list_info_settings'		=> $categoryData['list_info_settings'],
					'list_goods_status'			=> $categoryData['list_goods_status'],
					'update_date'				=> date('Y-m-d H:i:s'),
				);
				$this->db->update('fm_category', $data, "category_code like '{$category_code}%' and length(category_code)>".strlen($category_code));
			break;
		}
		
	}

	public function chkDupleSort($categoryCode){
		$query			= $this->db->query("select count(category_link_seq) as cnt from fm_category_link where category_code = ? group by sort having cnt > 1 limit 1", $categoryCode);
		$chkData		= $query->row_array();
		if	($chkData)	return true;
		else			return false;
	}

	public function reSortAll($categoryCode){

		$query = $this->db->query("select l.* from fm_goods_option o, fm_goods g, fm_category_link l where g.goods_view='look' AND g.goods_type = 'goods' and o.goods_seq=g.goods_seq and l.goods_seq = g.goods_seq and o.default_option ='y' and l.category_code = ? group by g.goods_seq order by l.sort asc, g.regist_date desc", $categoryCode);
		$sort	= 0;
		foreach($query->result_array() as $linkData){
			if	($linkData['category_link_seq']){
				$this->db->query("update fm_category_link set sort = ? where category_link_seq = ? ", array($sort, $linkData['category_link_seq']));
				$sort++;
			}
		}

		config_save('mig_sort_category',array($categoryCode=>'Y'));
	}

	public function getSortValue($categoryCode, $type){
		switch($type){
			case 'min':
				$query		= $this->db->query("select min(l.sort) sortVal from fm_goods_option o, fm_goods g, fm_category_link l where g.goods_view='look' AND g.goods_type = 'goods' and o.goods_seq=g.goods_seq and l.goods_seq = g.goods_seq and o.default_option ='y' and l.category_code = ? ", $categoryCode);
				$sortData	= $query->row_array();
			break;
			case 'max':
				$query		= $this->db->query("select max(l.sort) sortVal from fm_goods_option o, fm_goods g, fm_category_link l where g.goods_view='look' AND g.goods_type = 'goods' and o.goods_seq=g.goods_seq and l.goods_seq = g.goods_seq and o.default_option ='y' and l.category_code = ? ", $categoryCode);
				$sortData	= $query->row_array();
			break;
			case 'cnt':
				$query		= $this->db->query("select count(l.category_link_seq) sortVal from fm_goods_option o, fm_goods g, fm_category_link l where g.goods_view='look' AND g.goods_type = 'goods' and o.goods_seq=g.goods_seq and l.goods_seq = g.goods_seq and o.default_option ='y' and l.category_code = ? group by g.goods_seq", $categoryCode);
				$sortData	= $query->row_array();
			break;
		}

		return $sortData['sortVal'];
	}

	public function rangeUpdateSort($categoryCode, $sSort, $eSort, $addSort){
		if	(!is_null($sSort))
			$addWhere	.= " and sort > ".$sSort." ";
		if	(!is_null($eSort))
			$addWhere	.= " and sort < ".$eSort." ";
		$this->db->query("update fm_category_link set sort=sort".$addSort." where category_code = ? ".$addWhere, $categoryCode);
	}

	public function chgCategorySort($category_link_seq, $sort){
		if	($category_link_seq){
			$this->db->query("update fm_category_link set sort=? where category_link_seq = ? ", array($sort, $category_link_seq));
		}
	}

	public function design_set($childCategoryData,$division='catalog'){

		if($division=='searchForm') $division = 'catalog';

		$division = $division ? '_'.$division.'_' : '_';

		foreach($childCategoryData as $k=>$row){
			$childCategoryData[$k]['title'] = htmlspecialchars($childCategoryData[$k]['title']);
			$childCategoryData[$k]['ori_title'] = $childCategoryData[$k]['title'];

			if($childCategoryData[$k]['node'.$division.'type']=='text'){
				if($childCategoryData[$k]['node'.$division.'text_normal']){
					$attrStyle = font_decoration_attr($childCategoryData[$k]['node'.$division.'text_normal'],'css','style');
					$attrOnmouseover = font_decoration_attr($childCategoryData[$k]['node'.$division.'text_over'],'script','onmouseover');
					$attrOnmouseout = font_decoration_attr($childCategoryData[$k]['node'.$division.'text_normal'],'script','onmouseout');

					$childCategoryData[$k]['title'] = "<span {$attrStyle} {$attrOnmouseover} {$attrOnmouseout}>{$childCategoryData[$k]['title']}</span>";
				}
			}

			if($childCategoryData[$k]['node'.$division.'type']=='image'){
				$attrSrc ='';
				$attrOnmouseover ='';
				$attrOnmouseout ='';
				if($childCategoryData[$k]['node'.$division.'image_normal']){
					$attrSrc = 'src="'.$childCategoryData[$k]['node'.$division.'image_normal'].'"';
				}
				if($childCategoryData[$k]['node'.$division.'image_over']){
					$attrOnmouseover = 'onmouseover="this.src=\''.$childCategoryData[$k]['node'.$division.'image_over'].'\'"';
					$attrOnmouseout = 'onmouseout="this.src=\''.$childCategoryData[$k]['node'.$division.'image_normal'].'\'"';
				}

				$childCategoryData[$k]['title'] = "<img {$attrSrc} {$attrOnmouseover} {$attrOnmouseout} />";
			}

			$childCategoryData[$k]['name'] = $childCategoryData[$k]['title'];
		}
		return $childCategoryData;
	}

	public function get_represent_category_for_goods($goods_seq){

		$query = "select c.* from fm_category as c, fm_category_link as cl where
					c.category_code = cl.category_code and cl.link = 1 and cl.goods_seq = ? 
					limit 1 ";
		$query = $this->db->query($query,$goods_seq);
		$result	= $query->result_array();

		return $result[0];
	}
	
	public function getChildCategory($code,$exactly=false,$division='catalog'){
		$arrWhere = array("level >= 2");
		
		if($division=='catalog' || $division=='searchForm'){
			$arrWhere[] = "hide_in_navigation != '1'";
		}elseif($division=='gnb'){
			$arrWhere[] = "hide_in_gnb != '1'";
		}else{
			$arrWhere[] = "hide != '1'";
		}
		
		$childCategoryData = $this->get_list($code,$arrWhere);
		
		if($division=='searchForm'){
			if(!$childCategoryData && !$exactly /* && strlen($code)>4*/){
				$childCategoryData = $this->get_list(substr($code,0,strlen($code)-4),$arrWhere);
			}
		}else{
			if(!$childCategoryData && !$exactly && strlen($code)>4){
				$childCategoryData = $this->get_list(substr($code,0,strlen($code)-4),$arrWhere);
			}
		}
	
		$childCategoryData = $this->design_set($childCategoryData,$division);
	
		return $childCategoryData;
	}
	
	public function getChildBrand($code){
		if($code){
			$sqlInner = "
				select b.category_code from fm_category_link as a
					inner join fm_brand_link as b on (a.category_code=? and a.goods_seq = b.goods_seq and b.link=1)
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
		return $goodsBrands;
	}
}

/* End of file category.php */
/* Location: ./app/models/category */