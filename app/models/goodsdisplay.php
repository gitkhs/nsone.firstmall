<?php
class Goodsdisplay extends CI_Model {

	var $title;				# 디스플레이 타이틀
	var $style;				# 리스트 스타일
	var $platform;			# 플랫폼(pc/mobile)
	var $count_w;			# 가로 출력수
	var $count_w_lattice_b;	# 격자형B 가로 출력수
	var $count_w_swipe;		# 모바일 스와이프형 가로 출력수
	var $count_h_swipe;		# 모바일 스와이프형 세로 출력수
	var $count_max_swipe;	# 모바일 스와이프형 최대 출력수
	var $count_h;			# 세로 출력수
	var $perpage;			# 출력상품수
	var $image_size;		# 이미지 사이즈
	var $displayGoodsList;	# 상품 리스트
	var $displayTabsList;	# 탭 상품 리스트
	var $display_key;
	var $image_decorations;	# 이미지꾸미기 세팅값
	var $info_settings;		# 상품정보 세팅값
	var $text_align;		# 상품정보 정렬
	var $target;			# 링크 타겟
	var $tab_design_type;	# 탭 디자인타입
	var $navigation_paging_style ;	# 모바일 네비게이션 디자인타입
	var $is_bigdata_display;	# 빅데이터용 디스플레이 여부

	var $kind;			# 링크 타겟
	var $goods_video_type;			# 링크 타겟
	var $videosize_w;			# 링크 타겟
	var $videosize_h;			# 링크 타겟

	var $auto_use;			# 상품 자동노출 여부
	var $auto_order;		# 자동노출 순서
	var $auto_category_code;# 카테고리 조건
	var $auto_brand_code;	# 브랜드 조건
	var $auto_goods_status;	# 상품상태
	var $auto_term_type;	# 기간 타입(relative,absolute)
	var $auto_term;			# 기간 n일
	var $auto_start_date;	# 기간 시작일
	var $auto_end_date;		# 기간 종료일

	var $displayCachDir	= 'data/display_cach/';	# 디스플레이 캐싱파일 저장 경로

	# 리스트 스타일 종류
	var $styles = array(
		'lattice_a'		=>	array('name'=>'격자형A','count_w'=>4),
		'lattice_b'		=>	array('name'=>'격자형B','count_w'=>2),
		'list'			=>	array('name'=>'리스트형','count_w'=>1,'count_w_fixed'=>true),
		'rolling_h'		=>	array('name'=>'수평롤링형','count_w'=>4),
		/*
		'rolling_v'		=>	array('name'=>'수직롤링형'),
		'scroll'		=>	array('name'=>'스크롤형'),
		'tab_h'			=>	array('name'=>'가로탭형'),
		'tab_v'			=>	array('name'=>'세로탭형')
		*/

	);

	# 모바일 스타일 종류
	var $mobilestyles = array(
		'newmatrix'			=>	array('name'=>'기본형','count_w'=>2),
		'newswipe'			=>	array('name'=>'스와이프형','count_w'=>2),
	);

	# 동영상 스타일 종류
	var $videostyles = array(
		'video_lattice_a'		=>	array('name'=>'격자형A','count_w'=>4),
		'video_lattice_b'		=>	array('name'=>'격자형B','count_w'=>2,'count_w_fixed'=>true),
		'video_list'			=>	array('name'=>'리스트형','count_w'=>1,'count_w_fixed'=>true),
		'video_rolling_h'		=>	array('name'=>'수평롤링형','count_w'=>4)
	);

	# 리스트 정렬순서
	var $orders = array(
		'popular'		=> '인기순',
		'newly'			=> '최근등록순',
		'popular_sales'	=> '판매인기순',
		'low_price'		=> '낮은가격순',
		'high_price'	=> '높은가격순',
		'review'		=> '상품평많은순',
		/*
		''				=> '찜한순',
		''				=> '높은클릭순',
		*/
	);

	# 상품자동노출 타입
	var $auto_orders = array(
		'newly'			=> '최근등록순(신상품 순서)',
		'deposit_price'	=> '판매 인기순(구매금액)',
		'deposit'		=> '판매 인기순(구매갯수)',
		'review'		=> '상품평 많은순',
		'cart'			=> '장바구니 담기 많은순',
		'wish'			=> '위시리스트 담기 많은순',
		'discount'		=> '할인율 높은순',
		'view'			=> '상품조회 많은순',
	);

	function __construct() {
		parent::__construct();
	}

	function set($k,$v){
		$this->$k = $v;
	}

	function make_display_key(){
		$this->display_key = "designDisplay_".uniqid();
		return $this->display_key;
	}

	function get_styles(){
		$styles = $this->styles;

		$working_skin_path = ROOTPATH."data/skin/".$this->designWorkingSkin;
		$files = directory_map($working_skin_path."/_modules/display",true,false);
		$custom_files = array();

		foreach($files as $file){
			if(!preg_match("/^goods_display_(.*).html$/i",$file)) continue;
			
			$chk_name = str_replace(array("goods_display_",".html"),"",$file);

			if($chk_name=='person' || strstr($chk_name,'video_') || strstr($chk_name,'mobile_') ) continue;

			if(!in_array($chk_name,array_keys($this->styles)) && preg_match("/^goods_display_/",$file)){
				$tmp = explode(".",$file);
				$style = preg_replace("/^goods_display_/","",$tmp[0]);
				$styles[$style] = array(
					'custom' => true,
					'name' => preg_replace("/^goods_display_/","",$style)
				);
			}
		}

		return $styles;
	}

	function get_mobilestyles(){
		$styles = $this->mobilestyles;

		$working_skin_path = ROOTPATH."data/skin/".$this->designWorkingSkin;
		$files = directory_map($working_skin_path."/_modules/display",true,false);
		$custom_files = array();

		foreach($files as $file){
			if(!preg_match("/^goods_display_mobile_(.*).html$/i",$file)) continue;
			
			$chk_name = str_replace(array("goods_display_mobile_",".html"),"",$file);

			if(!in_array($chk_name,array_keys($this->mobilestyles)) && preg_match("/^goods_display_mobile_/",$file)){
				$tmp = explode(".",$file);
				$style = preg_replace("/^goods_display_mobile_/","",$tmp[0]);
				$styles[$style] = array(
					'custom' => true,
					'name' => preg_replace("/^goods_display_mobile_/","",$style)
				);
			}
		}

		return $styles;
	}

	function get_videostyles(){
		$styles = $this->videostyles;

		$working_skin_path = ROOTPATH."data/skin/".$this->designWorkingSkin;
		$files = directory_map($working_skin_path."/_modules/display",true,false);
		$custom_files = array();

		foreach($files as $file){
			if(!preg_match("/^goods_display_(.*).html$/i",$file)) continue;
			
			$chk_name = str_replace(array("goods_display_",".html"),"",$file);

			if($chk_name=='person' || $chk_name=='video_person') continue;

			if(!in_array($chk_name,array_keys($this->videostyles)) && preg_match("/^goods_display_/",$file) && strstr($chk_name,'video_')){
				$tmp = explode(".",$file);
				$style = preg_replace("/^goods_display_/","",$tmp[0]);
				$styles[$style] = array(
					'custom' => true,
					'name' => preg_replace("/^goods_display_/","",$style)
				);
			}
		}

		return $styles;
	}
	/* 상품디스플레이 이미지꾸미기 아이콘 목록 반환 */
	function get_image_icons($childDir=''){
		$this->load->helper('directory');
		$path = 'data/icon/goodsdisplay';
		if($childDir) $path .= '/'.$childDir;
		
		if(!is_dir($path)) {
			@mkdir($path);
			@chmod($path,0777);
		}
		
		$map = directory_map(ROOTPATH.$path, TRUE);
		$icons = array();
		foreach($map as $name){
			if(preg_match("/(.*)\.(gif|jpg|png|bmp|jpeg)$/",$name)) $icons[] = $name;
		}
		return $icons;
	}

	/* 디자인 상품디스플레이 정보 반환 */
	function get_display($display_seq){
		$query = $this->db->query("select * from fm_design_display where display_seq = ?",$display_seq);
		$result = $query->row_array();
		if($result){
			$result['auto_goods_status'] = explode('|',$result['auto_goods_status']);
		}
		return $result;
	}
	
	/* 디자인 상품디스플레이 정보 반환 */
	function get_display_tab($display_seq,$tabIndex=null){
		$sql = "select b.* from fm_design_display a left join fm_design_display_tab b on a.display_seq=b.display_seq where a.display_seq = ? ";
		if(!is_null($tabIndex)) $sql .= " and b.display_tab_index='".((int)$tabIndex)."'";
		$sql .= " order by b.display_tab_index asc";
		$query = $this->db->query($sql,$display_seq);
		$result = $query->result_array();
		return $result;
	}

	/* 디자인 상품디스플레이 상품목록 반환 */
	function get_display_item($display_seq,$display_tab_index=0){
		$display_item = array();

		// 상품 아이콘 서브쿼리
		$goods_icon_subquery = "
		select group_concat(c.codecd) from fm_goods_icon c where c.goods_seq=g.goods_seq and
		(
			(ifnull(start_date,'0000-00-00') = '0000-00-00' and ifnull(end_date,'0000-00-00') = '0000-00-00')
			or
			(curdate() between start_date and end_date)
			or
			(start_date <= curdate() and ifnull(end_date,'0000-00-00') = '0000-00-00')
		)
		";

		$query  = $this->db->query("
		select
			g.goods_seq,
			g.goods_name,
			g.summary,
			g.string_price_use,
			g.string_price,
			(select image from fm_goods_image where goods_seq=g.goods_seq and cut_number=1 and image_type=a.image_size limit 1) as image,
			o.consumer_price,
			o.price,
			o.reserve_rate,
			o.reserve_unit,
			o.reserve,
			({$goods_icon_subquery}) as icons
		from
			fm_design_display a
			inner join fm_design_display_tab_item d on a.display_seq=d.display_seq
			inner join fm_goods g on (d.goods_seq=g.goods_seq and g.goods_view='look')
			left join fm_goods_option o on (o.goods_seq=g.goods_seq and o.default_option ='y')
		where d.display_seq = ? and d.display_tab_index = ?
		order by d.display_tab_item_seq asc",array($display_seq,$display_tab_index));
		foreach ($query->result_array() as $row) $display_item[] = $row;

		return $display_item;
	}

	function decode_image_decorations($image_decorations_string){
		/* 이미지 꾸미기 값 파싱 */
		$image_decorations = json_decode(base64_decode($image_decorations_string));

		/* 이미지 사이즈 기본값 */
		if(!isset($image_decorations->image_size) || !$image_decorations->image_size){
			$image_decorations->image_size = "list1";
		}

		/* 이미지 테두리두께 기본값 */
		if(!isset($image_decorations->image_border_width) || !$image_decorations->image_border_width){
			$image_decorations->image_border_width = "1";
		}

		return $image_decorations;
	}

	function info_settings_have_eventprice($info_settings){
		$info_settings = json_decode($info_settings);

		foreach((array)$info_settings as $k=>$v){
			if($v->kind=='event_text') return true;
		}

		return false;
	}

	function print_($return=false){

		//if(count($this->displayGoodsList)==0) return;

		if(!$this->goodsStatusImage){
			$data = code_load('goodsStatusImage');
			$this->goodsStatusImage = array();
			foreach($data as $row){
				$this->goodsStatusImage[$row['codecd']] = $row['value'];
			}
		}

		$display_key = $this->display_key ? $this->display_key : $this->make_display_key();

		if(!$this->style){
			echo '디스플레이 설정값이 누락되었습니다.';
			return;
		}

		if(!$this->count_w) $this->count_w = $this->styles['lattice_a']['count_w'];
		if(!$this->count_h) $this->count_h = 3;
		if(!$this->count_w_lattice_b) $this->count_w_lattice_b = $this->styles['lattice_b']['count_w'];
		if($this->count_w*$this->count_h < $this->perpage){
			$this->count_h = ceil($this->perpage / $this->count_w);
		}
		if(!$this->platform) $this->platform = 'pc';

		$goodsImageSize = config_load('goodsImageSize');

		/* 모바일모드일 경우*/
		if($this->mobileMode || $this->storemobileMode){
			if($this->platform=='mobile'){
				if(!$this->style) $this->style = 'newmatrix';
				$this->style = 'mobile_'.$this->style;
			}else{

				if(in_array(uri_string(),array('goods/catalog','goods/brand','goods/location','goods/search'))){
					$this->style = $_GET['display_style'] ? $_GET['display_style'] : 'mobile_list';
				}else if($this->style!='person'){
					$this->style = $_GET['display_style'] ? $_GET['display_style'] : 'mobile_lattice_a';
				}

				if($this->count_w >= 3 ) {
					$this->count_h = ceil(($this->count_w*$this->count_h)/3);
					$this->count_w = '3';
				}

				$goodsImageSize[$this->image_size]['width'] = round(100/$this->count_w).'%';
			}
		}

		if($this->style=='lattice_b'){
			$perpage = $this->count_w * $this->count_h;
			$this->count_w = $this->count_w_lattice_b;
			$this->count_h = ceil($perpage/$this->count_w);
		}
		if($this->style=='list'){
			$perpage = $this->count_w * $this->count_h;
			$this->count_w = 1;
			$this->count_h = ceil($perpage/$this->count_w);
		}

		/* 격자형 스타일 틀*/
		$grid = array();
		for($i=0;$i<$this->count_h;$i++){
			for($j=0;$j<$this->count_w;$j++){
				$idx = $i*$this->count_w+$j;

				if($idx < count($this->displayGoodsList)){
					$grid[$i][$j] = true;
				}else{
					$grid[$i][$j] = false;
				}
			}
			if($idx >= count($this->displayGoodsList)-1) break;
		}
		
		/* 격자형 스타일 탭별 틀 */
		foreach($this->displayTabsList as $k=>$v){
			$tabGrid = array();
			for($i=0;$i<$this->count_h;$i++){
				for($j=0;$j<$this->count_w;$j++){
					$idx = $i*$this->count_w+$j;
	
					if($idx < count($v['record'])){
						$tabGrid[$i][$j] = true;
					}else{
						$tabGrid[$i][$j] = false;
					}
				}
				if($idx >= count($v['record'])-1) break;
			}
			$this->displayTabsList[$k]['grid'] = $tabGrid;			

			foreach($v['record'] as $k2=>$v2){
				if(!empty($v2['icons']) && !is_array($v2['icons'])){
					$this->displayTabsList[$k]['record'][$k2]['icons'] = explode(",",$v2['icons']);
				}
			}
		}
		

		foreach($this->displayGoodsList as $k=>$v){
			if(!empty($v['icons']) && !is_array($v['icons'])){
				$this->displayGoodsList[$k]['icons'] = explode(",",$v['icons']);
			}
		}

		$this->info_settings = json_decode($this->info_settings);
		$this->info_settings_data = array();
		
		foreach((array)$this->info_settings as $k=>$info_setting){
			if(!empty($this->info_settings[$k]->font_decoration)){
				$this->info_settings[$k]->name_css = font_decoration_attr($this->info_settings[$k]->font_decoration,'css','style');
			}
		
			$this->info_settings_data[$info_setting->kind] = $this->info_settings[$k];
		}
		
		if($this->info_settings_data['color']){
			foreach($this->displayTabsList as $k=>$record){
				foreach($record['record'] as $j=>$row){
					if($row['colors']){
						$colors = array_notnull(array_unique(explode(",",$row['colors'])));
						$this->displayTabsList[$k]['record'][$j]['colors'] = $colors;
					}					
				}
			}

			foreach($this->displayGoodsList as $k=>$row){
				if($row['colors']){
					$colors = array_notnull(array_unique(explode(",",$row['colors'])));
					$this->displayGoodsList[$k]['colors'] = $colors;
				}					
			}
		}
		
		$goodsImageSize = array_merge($goodsImageSize,$goodsImageSize[$this->image_size]);

		if($this->perpage){
			$this->template->assign(array(
				'perpage'			=>$this->perpage,
				'orders'			=>$this->orders,
			));
		}

		$decorations_obj = $this->decode_image_decorations($this->image_decorations);
		$decorations = array();
		foreach($decorations_obj as $k=>$v) $decorations[$k] = $v;
		$decorations['quick_shopping_data'] = explode(",",str_replace(array("'","[","]"),"",$decorations['quick_shopping']));

		$this->template->assign(array(
			'display_key'		=>$display_key,
			'displayGoodsList'	=>$this->displayGoodsList,
			'displayTabsList'	=>$this->displayTabsList,
			'grid'				=>$grid,
			'title'				=>$this->title,
			'count_w'			=>$this->count_w,
			'count_h'			=>$this->count_h,
			'text_align'		=>$this->text_align,
			'kind'				=>$this->kind,
			'goods_video_type'				=>$this->goods_video_type,
			'videosize_w'				=>$this->videosize_w,
			'videosize_h'				=>$this->videosize_h,
			'image_decorations'	=>$this->image_decorations,
			'decorations'		=>$decorations,
			'target'			=>$this->target,
			'info_settings'		=>array('list'=>$this->info_settings,'data'=>$this->info_settings_data),
			'goodsImageSize'	=>$goodsImageSize,
			'goodsStatusImage'	=>$this->goodsStatusImage,
			'tab_design_type'	=>$this->tab_design_type,
			'navigation_paging_style'	=>$this->navigation_paging_style,
			'is_bigdata_display'	=>$this->is_bigdata_display
		));

		$this->template->define(array($display_key=>$this->skin."/_modules/display/goods_display_{$this->style}.html"));

		if($return){
			return $this->template->fetch($display_key);
		}else{
			$this->template->print_($display_key);
		}
	}

	public function copy_display($display_seq){
		$query = $this->db->query("select * from fm_design_display where display_seq = ?",$display_seq);
		$data = $query->row_array();

		if($data){
			unset($data['display_seq']);
			$data['regdate'] = date('Y-m-d H:i:s');

			$query = $this->db->insert_string('fm_design_display', $data);
			$this->db->query($query);

			$new_display_seq = $this->db->insert_id();

			$this->db->query("delete from fm_design_display_tab where display_seq=?",$new_display_seq);
			$this->db->query("delete from fm_design_display_tab_item where display_seq=?",$new_display_seq);		

			/* 상품탭 목록 */
			$query = $this->db->query("select * from fm_design_display_tab where display_seq=?",$display_seq);
			$display_list = $query->result_array();

			foreach($display_list as $k=>$row){
				$data = $row;
				$data['display_seq'] = $new_display_seq;

				$query = $this->db->insert_string('fm_design_display_tab', $data);
				$this->db->query($query);
			}
			
			/* 상품목록 */
			$query = $this->db->query("select * from fm_design_display_tab_item where display_seq=?",$display_seq);
			$display_list = $query->result_array();

			foreach($display_list as $k=>$row){
				$data = $row;

				unset($data['display_tab_item_seq']);
				$data['display_seq'] = $new_display_seq;

				$query = $this->db->insert_string('fm_design_display_tab_item', $data);
				$this->db->query($query);
			}

			return $new_display_seq;
		}

		return;
	}
	
	// 자동노출 검색조건 파라미터 가공
	public function search_condition($criteria, $sc, $kind='display'){
		$sc['auto_use']='y';

		if($kind=='recommend'){
			unset($sc['selectCategory1']);
			unset($sc['category']);
		}
		if($kind=='relation'){
			unset($sc['selectCategory1']);
			unset($sc['selectBrand1']);
			unset($sc['selectLocation1']);
			unset($sc['category']);
			unset($sc['brand']);
			unset($sc['location']);
		}

		foreach(explode(',',$criteria) as $v){
			list($k,$v) = explode('=',$v);
			if(preg_match("/(.*)\[\]$/",$k,$matches)){
				if($v!=='') $sc[$matches[1]][] = urldecode($v);
			}else{
				if($v!=='') $sc[$k] = urldecode($v);
			}
		}
		
		if($sc['selectGoodsName']){
			$sc['search_text'] = $sc['selectGoodsName'];
			unset($sc['selectGoodsName']);
		}
		
		if($sc['selectCategory1']){
			if($sc['selectCategory1']) $sc['category'] = $sc['selectCategory1'];
			if($sc['selectCategory2']) $sc['category'] = $sc['selectCategory2'];
			if($sc['selectCategory3']) $sc['category'] = $sc['selectCategory3'];
			if($sc['selectCategory4']) $sc['category'] = $sc['selectCategory4'];
			unset($sc['selectCategory1']);
			unset($sc['selectCategory2']);
			unset($sc['selectCategory3']);
			unset($sc['selectCategory4']);
		}
		
		if($sc['selectBrand1']){
			if($sc['selectBrand1']) $sc['brand'] = $sc['selectBrand1'];
			if($sc['selectBrand2']) $sc['brand'] = $sc['selectBrand2'];
			if($sc['selectBrand3']) $sc['auto_brand_code'] = $sc['selectBrand3'];
			if($sc['selectBrand4']) $sc['brand'] = $sc['selectBrand4'];
			unset($sc['selectBrand1']);
			unset($sc['selectBrand2']);
			unset($sc['selectBrand3']);
			unset($sc['selectBrand4']);
		}
		
		if($sc['selectLocation1']){
			if($sc['selectLocation1']) $sc['location'] = $sc['selectLocation1'];
			if($sc['selectLocation2']) $sc['location'] = $sc['selectLocation2'];
			if($sc['selectLocation3']) $sc['location'] = $sc['selectLocation3'];
			if($sc['selectLocation4']) $sc['location'] = $sc['selectLocation4'];
			unset($sc['selectLocation1']);
			unset($sc['selectLocation2']);
			unset($sc['selectLocation3']);
			unset($sc['selectLocation4']);
		}
		
		if($sc['selectGoodsStatus']){
			$sc['goods_status'] = $sc['selectGoodsStatus'];
			unset($sc['selectGoodsStatus']);
		}
		
		if(isset($sc['selectStartPrice'])){
			if($sc['selectStartPrice']>0) $sc['start_price'] = $sc['selectStartPrice'];
			unset($sc['selectStartPrice']);
		}
		
		if(isset($sc['selectEndPrice'])){
			if($sc['selectEndPrice']>0) $sc['end_price'] = $sc['selectEndPrice'];
			unset($sc['selectEndPrice']);
		}
		
		if($sc['file_key_w']){
			$sc['auto_file_key_w'] = $sc['file_key_w'];
			unset($sc['file_key_w']);
		}
		if($sc['video_use']){
			$sc['auto_video_use'] = $sc['video_use'];
			unset($sc['video_use']);
		}
		if($sc['videototal']){
			$sc['auto_videototal'] = $sc['videototal'];
			unset($sc['file_key_w']);
		}

		if(!$sc['auto_order']) {
			$sc['auto_order'] = 'newly';
			if(!$sc['auto_term_type']) $sc['auto_term_type'] = 'relative';
			if(!$sc['auto_term']) $sc['auto_term'] = '365';
		}

		return $sc;
	}

	## 디스플레이 캐싱 삭제
	function delete_display_cach($display_seq = '', $tab_idx=0){
		$dir	= ROOTPATH.$this->displayCachDir;
		$dobj	= opendir($dir);
		while (($file = readdir($dobj)) !== false){
			if	(is_file($dir.$file)){
				if	($display_seq > 0){
					if	(preg_match('/^'.$display_seq.'\_'.$tab_idx.'\_/', $file))	@unlink($dir.$file);
				}else{
					@unlink($dir.$file);
				}
			}
		}
	}

	## 상품 디스플레이 캐싱 파일이 있는지 체크
	function checkDesignDisplayCach($display_seq, $tab_idx=0, $perpage = null, $kind = null){
		$write_dir			= ROOTPATH.$this->displayCachDir;
		$params				= $this->getDesignDisplayParams($display_seq, $tab_idx, $perpage, $kind);
		$file_name			= $write_dir.$this->getDesignDisplayCachFileName($display_seq, $tab_idx, $params);

		if	(file_exists($file_name))	return $file_name;
		else							return false;
	}

	## 상품 디스플레이 cach파일 생성
	function createDesignDisplayCach($display_seq, $tab_idx=0, $perpage = null, $kind = null){
		$write_dir	= ROOTPATH.$this->displayCachDir;
		if(!is_dir($write_dir)){
			@mkdir($write_dir);
			@chmod($write_dir,0777);
		}
		$params		= $this->getDesignDisplayParams($display_seq, $tab_idx, $perpage, $kind, 'user');

		ob_start();
		$this->template->include_('showDesignDisplay'); 
		showDesignDisplay($display_seq, $perpage, $kind, 'cach');
		$contents = ob_get_contents();
		ob_end_clean();

		$file_name	= $write_dir.$this->getDesignDisplayCachFileName($display_seq,$tab_idx, $params);
		$fobj		= fopen($file_name, 'w+');
		fwrite($fobj, $contents);
		fclose($fobj);

		return $file_name;
	}

	## 캐싱을 위한 GET Parameter를 동일하게 만들기 위한 함수
	function getDesignDisplayParams($display_seq, $tab_idx=0, $perpage = null, $kind = null, $user = null){
		$params		= $_SERVER['QUERY_STRING'];
		$params		= ($params) ? '?'.$params . '&display_seq='.$display_seq.'&tab_idx='.$tab_idx : '?display_seq='.$display_seq.'&tab_idx='.$tab_idx;
		if	($perpage)					$params	.= '&set_perpage='.$perpage;
		if	($kind && $kind != 'cach')	$params	.= '&set_kind='.$kind;
		if	($user && $this->userInfo['member_seq'] > 0)	$params	.= '&userInfo='.base64_encode(serialize($this->userInfo));

		return $params;
	}

	## 캐싱 파일명
	function getDesignDisplayCachFileName($display_seq, $tab_idx=0, $params = ''){
		// 회원 등급
		$member_group		= '0';
		if($this->userInfo['group_seq'] > 0)	$member_group		= $this->userInfo['group_seq'];
		// 접속환경
		$platform			= 'P';
		if($this->_is_mobile_agent)				$platform			= 'M';
		// 접속모드
		$viewMode			= 'P';
		if($this->mobileMode)					$viewMode			= 'M';

		$result	= $display_seq
				. '_' . $tab_idx
				. '_' . date('YmdH') . $member_group . $platform . $viewMode
				. '_' . $this->skin
				. '_' . md5($params) . '.html';
		return $result;
	}

	## 공통 사용 디스플레이 추출용 ( for kind )
	function get_design_display_kind($kind = 'design'){
		if	(!$kind)	$kind	= 'design';
		$sql	= "select * from fm_design_display where kind = ?";
		$query	= $this->db->query($sql, array($kind));

		return $query->row_array();
	}
}
?>