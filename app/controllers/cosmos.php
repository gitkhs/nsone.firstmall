<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH ."controllers/base/front_base".EXT);
class cosmos extends front_base {
	public function __construct(){
		parent::__construct();
		
		set_time_limit(0);

		$this->load->model('goodsmodel');
		$this->load->model('categorymodel');
		$this->load->model('brandmodel');
		$this->load->model('providermodel');
		

		// error_reporting(E_ALL);

		$tmp = explode('.',$_SERVER['HTTP_HOST']);
		$this->mallid = $tmp[0];		
	}

	public function category()
	{
		
		
		header("Content-type: text/xml;charset=euc-kr");
		echo "<?xml version='1.0' encoding='euc-kr'?>\n";
		
		echo "<CateCust>\n";
		$r_where[] = "category_code != ''";
		$data_category = $this->categorymodel->get_all($r_where);
		
		foreach($data_category as $k => $row_category){
			$category = array();
			for($i=4;$i<=16;$i+=4){
				$start = $i - 4;
				$str = substr($row_category['category_code'],$start ,4);
				if($str) $category[] = $str;
				else $category[] = 0;
			}

			$title = mb_convert_encoding($row_category['title'], "euc-kr", "utf-8");
			$title = str_replace("<","@lt;",$title);
			$title = str_replace(">","@gt;",$title);
			$title = str_replace("'","@quot;",$title);
			$title = str_replace("''","@dquot;",$title);
			$title = str_replace("&","@amp;",$title);
			$row_category['title'] = $title;
			$row_category['category'] = $category;
			$row_category['depth'] = strlen($row_category['category_code'])/4;
						
			$str_category = '';
			echo "<Category>\n";
			echo "<CustID>".$this->mallid."</CustID>\n";
			foreach($category as $key => $str_category)
			{				
				$step = $key+1;
				echo "<Ccode".$step.">".$str_category."</Ccode".$step.">\n";
			}			
			echo "<Depth>".$row_category['depth']."</Depth>\n";
			echo "<CateName>".$row_category['title']."</CateName>\n";
			echo "</Category>\n";		
			
		}

		echo "</CateCust>\n";
	}

	public function goods()
	{
		// error_reporting(E_ALL);
		$bind = array();
		
		/*
		$_POST = array(
		  "CustID"=>"polomix",
		  "ProdInc"=>"2000001",
		  "MallKind"=>"US",
		  "MallID"=>"jcrew",
		  "ServiceID"=>"MALL",
		  "Pname"=>"Seamount toggle jacket",
		  "Brand"=>"Jcrew",
		  "ItemCode"=>"11745_BL8133",
		  "Sku"=>"11745_BL8133",
		  "Nation"=>"",
		  "Maker"=>"",
		  "Lprice"=>"325.00",
		  "Sprice"=>"200.00",
		  "Ccode1"=>"0001",
		  "Ccode2"=>"0001",
		  "Ccode3"=>"0002",
		  "Ccode4"=>"0002",
		  "Depth"=>"4",
		  "Purl"=>"http://www.jcrew.com/mens_category/outerwear/wool/PRDOVR~11745/11745.jsp",
		  "Pimg"=>"http://s7.jcrew.com/is/image/jcrew/11745_BL8133_m?\$ary_tn250\$",
		  "PimgP"=>"http://s7.jcrew.com/is/image/jcrew/11745_BL8133_m?\$ary_tn250\$",
		  "PimgD"=>"http://s7.jcrew.com/is/image/jcrew/11745_BL8133_m?\$pdp_fs418\$",
		  "LogoImg"=>"http://www.jcrew.com/media/images/common/logo_v2_m56577569830502393.gif",
		  "Story"=>"Inspired by the campus classic, our toggle jacket is crafted in ultrasoft wool, with a textured finish that shows the natural grain of the weave (a traditional touch our designers discovered while digging through our vintage archives). Built for three-season warmth, it:s topped off with an adjustable hood. Wool.Hood with tab collar.Toggle closure.Flap pockets.Functional buttons at cuffs.Dry clean.Import.",
		  "ColorInfo"=>"navy~~http://s7.jcrew.com/is/image/jcrew/11745_BL8133_m?\$pdp_fs418\$^http://s7.jcrew.com/is/image/jcrew/11745_BL8133_m_sw?\$pdp_sw20\$^http://s7.jcrew.com/is/image/jcrew/11745_BL8133_m?\$pdp_fs418\$^^^^^^^^^^^***",
		  "StillInfo"=>"http://s7.jcrew.com/is/image/jcrew/11745_BL8133_s?\$pdp_fs418\$^http://s7.jcrew.com/is/image/jcrew/11745_BL8133_b?\$pdp_fs418\$^",
		  "SizeInfo"=>"navy~~medium^large^x-large^",
		  "MDID"=>"test",
		  "LpriceP"=>"325.00",
		  "SpriceP"=>"200.00",
		  "Lrprice"=>"0.00",
		  "Srprice"=>"0.00",
		  "Lsprice"=>"0.00",
		  "Ssprice"=>"0.00",
		  "ExchangeRate"=>"0.00",
		  "Shipping"=>"0.00",
		  "Trate"=>"0.0000",
		  "Weight"=>"0.00",
		  "DeliveryFee"=>"0.00",
		  "Drate"=>"0.000",
		  "Dadd"=>"0.00",
		  "Krate"=>"0.000",
		  "Kadd"=>"0.00",
		  "Crate"=>"0.000",
		  "CstdD"=>"0.00",
		  "CstdK"=>"0",
		  "CstdType"=>"D",
		  "Customs"=>"0.00",
		  "AddTax"=>"0.00",
		  "Prate"=>"1.000",
		  "Profit"=>"0.00"
		);
		*/

		$regist_date = date('Y-m-d H:i:s');
		$ProdInc = $_POST['ProdInc'];
		$cur_price 	= (int) $_POST['Lrprice'];
		$price 		= (int) $_POST['Srprice'];

		$query = $this->db->query("select * from fm_goods where ProdInc=? limit 1",array($ProdInc));
		list($data_goods) =  $query->result_array();
		$bind[] = $data_goods['goods_seq'];
		$query = "delete from fm_category_link where goods_seq=?";
		$this->db->query($query,$bind);
		$query = "delete from fm_brand_link where goods_seq=?";
		$this->db->query($query,$bind);
		$query = "delete from fm_goods_option where goods_seq=?";
		$this->db->query($query,$bind);
		$query = "delete from fm_goods_supply where goods_seq=?";
		$this->db->query($query,$bind);
		$query = "delete from fm_goods_image where goods_seq=?";
		$this->db->query($query,$bind);
		$query = "delete from fm_goods_addition where goods_seq=?";
		$this->db->query($query,$bind);
		$query = "delete from fm_goods where goods_seq=?";
		$this->db->query($query,$bind);

		/*
		ob_start();
		var_dump($_POST);
		$somecontent = ob_get_contents();
		ob_end_clean();
		$mypath=BASEPATH."../data/";
		$filename = $mypath.'message.txt';
		$handle = fopen($filename,"w");
		fwrite($handle,$somecontent);
		fclose($handle);
		@chmod($filename,0777);
		*/
		
		$new_goods['goods_status']		=	'normal';
		$new_goods['goods_view']		=	'look';
		$new_goods['goods_code']		=	$_POST['Sku'];
		$new_goods['goods_name']		=	mb_convert_encoding($_POST['Pname'], "utf-8", "euc-kr");
		$new_goods['contents']			=	"<div class='story_contents'>".mb_convert_encoding($_POST['Story'], "utf-8", "euc-kr")."</div>";
		$new_goods['mobile_contents']	=	"<div class='story_contents'>".mb_convert_encoding($_POST['Story'], "utf-8", "euc-kr")."</div>";
		$new_goods['realgoods_url']		=	mb_convert_encoding($_POST['Purl'], "utf-8", "euc-kr");
		$new_goods['goods_weight']		=	$_POST['Weight'];
		$new_goods['MallKind'] 			= $_POST['MallKind'];
		$new_goods['MallID'] 			= $_POST['MallID'];
		$new_goods['Lprice'] 			= $_POST['Lprice'];
		$new_goods['Sprice'] 			= $_POST['Sprice'];
		$new_goods['unit_price'] 		= $_POST['Sprice'];
		$new_goods['ProdInc'] 			= $ProdInc;		
		$new_goods['regist_date']		=	$regist_date;
		$new_goods['update_date']		=	$regist_date;
		$new_goods['relation_type']		=	'AUTO';				

		$contents = $new_goods['contents'];
		$mobile_contents = $new_goods['mobile_contents'];

		$ar_keys=array();
		$ar_vals=array();
		foreach($new_goods as $key=>$value) {
			$ar_keys[]="`$key` = ?";
			$ar_vals[]=$value?$value:'';
		}

		$query = "insert into fm_goods set ".implode(" , ",$ar_keys);
		$this->db->query($query,$ar_vals);
		$goods_seq = $this->db->insert_id();

		$varCategoryCode = $_POST['Ccode1'].$_POST['Ccode2'].$_POST['Ccode3'].$_POST['Ccode4'];
		for($i=0;$i<strlen($varCategoryCode);$i+=4)
		{
			$end = $i+4;
			$tmp = substr($varCategoryCode,0,$end);
			$link = 0;
			if(strlen($varCategoryCode) == $end) $link = 1;

			$minsort	= $this->categorymodel->getSortValue($tmp, 'min');
			$sort	= $minsort-1;
			$queryGoodsLink="insert into fm_category_link set goods_seq=?, category_code=?, sort=?, link=?";
			$this->db->query($queryGoodsLink,array($goods_seq,$tmp,$sort,$link));
		}

		if($_POST['Brand']){
			$brandnm = str_replace("'","",$_POST['Brand']);
			$brandcode = $this->set_brand($brandnm);
			
			$minsort	= $this->brandmodel->getSortValue($code, 'min');
			$sort		= $minsort-1;		

			$queryGoodsLink="insert into fm_brand_link  set goods_seq=?, category_code=?, sort=?, link=1";
			$this->db->query($queryGoodsLink,array($goods_seq,$brandcode,$sort));
			
		}		

		// 색상값으로 상세 설명 조합
		$tmp_colorinfo = explode('***',$_POST['ColorInfo']);
		foreach($tmp_colorinfo as $tmp2_colorinfo){
			$tmp3_colorinfo = explode('~~',$tmp2_colorinfo);
			if($tmp3_colorinfo[1]){
				$tmp4_colorinfo = explode('^',$tmp3_colorinfo[1]);
				foreach($tmp4_colorinfo as $tmp5_colorinfo){
					if( $tmp5_colorinfo ) $r_colorInfo[$tmp3_colorinfo[0]][] = $tmp5_colorinfo;
				}
			}
		}		
		$arr_contents_image = array();
		$color_contents = '';
		foreach($r_colorInfo as $color_name => $r_image){
			$color_contents .= "<div class='color_title'>".$color_name."</div>";
			foreach($r_image as $k=>$image){
				if($k!=1){
					if(!in_array($image,$arr_contents_image)){
						$color_contents .= "<div class='color_image'><img src='".$image."' border=0></div>";
						$arr_contents_image[] = $image;
					}
				}
			}
		}
		if($_POST['StillInfo']){
			$r_img = explode('^',$_POST['StillInfo']);
			foreach($r_img as $k => $new_image){
				if( !$new_image ) continue;
				if(!in_array($new_image,$arr_contents_image)){
					$color_contents .= "<div class='color_image'><img src='".$new_image."' border=0></div>";
					$arr_contents_image[] = $new_image;
				}
			}
		}

		$contents = $color_contents . $new_goods['contents'];
		$mobile_contents = $color_contents . $new_goods['mobile_contents'];

		$tmp = explode('***',$_POST['SizeInfo']);
		$option_data = array();
		$option_title = "색상,사이즈";

		if(count($tmp)){
			foreach($tmp as $tmp1){
				$r_opt = explode('^',$tmp1);
				if($r_opt){
					foreach($r_opt as $k => $opt){
						$size = $opt;
						if($k == 0){
							$t_opt = explode('~~',$opt);
							$color = $t_opt[0];
							$size = $t_opt[1];
						}
						if($color && $size){
							$option_data[] = array($color,$size);
						}
					}
				}
			}
		}else{
			$sql = "insert into fm_goods_option set
			goods_seq = '{$goods_seq}',
			default_option = '".($k==0?'y':'n')."',
			consumer_price = '{$cur_price}',
			price = '{$price}'
			";
			$this->db->query($sql);
			$option_seq = $this->db->insert_id();
		}

		if($option_data){
			foreach($option_data as $k=>$r){
				$sql = "insert into fm_goods_option set
				goods_seq = '{$goods_seq}',
				default_option = '".($k==0?'y':'n')."',
				option_title = '{$option_title}',
				option1 = '{$r[0]}',
				option2 = '{$r[1]}',
				consumer_price = '{$cur_price}',
				price = '{$price}'
				";
				$this->db->query($sql);
				$option_seq = $this->db->insert_id();

				$sql = "insert into fm_goods_supply set
				goods_seq = '{$goods_seq}',
				option_seq = '{$option_seq}',
				supply_price = '0',
				stock = '100'
				";
				$this->db->query($sql);
			}
		}

		$cut = 1;
		$arr_image_done = array();
		if($_POST['PimgD']){			
			$new_image = $_POST['PimgD'];
			$this->_cut_image_insert($new_image,$goods_seq,$cut);
			$arr_image_done[] = $new_image;
			$cut++;
		}
		
		foreach($r_colorInfo as $color_name => $r_image){			
			foreach($r_image as $k=>$image){
				if( !$image ) continue;
				// if( $k == 1 ) continue;
				if( in_array($image,$arr_image_done) ) continue;				
				$this->_cut_image_insert($image,$goods_seq,$cut);	
				$arr_image_done[] = $image;
				$cut++;				
			}
		}

		if($_POST['StillInfo']){
			$r_img = explode('^',$_POST['StillInfo']);
			foreach($r_img as $k => $new_image){
				if( !$new_image ) continue;
				if( in_array($new_image,$arr_image_done) ) continue;				
				$this->_cut_image_insert($new_image,$goods_seq,$cut);
				$arr_image_done[] = $new_image;
				$cut++;
			}
		}

		if($row['Nation']){
			$this->db->query("insert into fm_goods_addition set
			goods_seq='{$goods_seq}',
			type='orgin',
			title='',
			contents=?
			",$_POST['Nation']);
		}

		if($row['Maker']){
			$this->db->query("insert into fm_goods_addition set
			goods_seq='{$goods_seq}',
			type='manufacture',
			title='',
			contents=?
			",$_POST['Maker']);
		}

		$new_goods = array();
		$new_goods['option_use']			=	$option_seq ? '1' : '0';
		$new_goods['option_view_type']		=	$option_seq ? 'divide' : 'join';
		$new_goods['contents'] = $contents;
		$new_goods['mobile_contents'] =  $mobile_contents;

		$ar_keys=array();
		$ar_vals=array();
		foreach($new_goods as $key=>$value) {
			$ar_keys[]="`$key` = ?";
			$ar_vals[]=$value?$value:'';
		}

		$query = "update fm_goods set ".implode(" , ",$ar_keys)." where goods_seq={$goods_seq}";
		$this->db->query($query,$ar_vals);
		echo "OK";		
	}

	public function stock_list()
	{
		header("Content-type: text/html;charset=euc-kr");
		if($_GET['Stock']){
			switch($_GET['Stock']){
				case "1":
					$where[] = "g.goods_status != 'runout'";
				
					break;
				case "0":
					$where[] = "g.goods_status = 'runout'";
					
					break;
			}
		}
		if($_GET['MallKind']){
			$where[] = "g.MallKind='".$_GET['MallKind']."'";			
		}
		if($_GET['MallID']){
			$r_MallID = explode(',',$_GET['MallID']);
			foreach($r_MallID as $MallID){
				$MallID 	=	str_replace('.','',$MallID);
				$where_mallid[]	=	"g.MallID='".$MallID."'";
			}
			if($where_mallid){
				$where[] = "(".implode(' OR ',$where_mallid).")";
			}
		}
		if($_GET['PcodeS']){
			$where[] = "g.ProdInc>='".$_GET['PcodeS']."'";			
		}
		if($_GET['PcodeE']){
			$where[] = "g.ProdInc<='".$_GET['PcodeE']."'";			
		}
		if($_GET['TopCnt']){
			$limit_str = "limit ".$_GET['TopCnt'];
		}

		if($_GET['Ccode1'] == 'all') $_GET['Ccode1'] = '';
		if($_GET['Ccode2'] == 'all') $_GET['Ccode2'] = '';
		if($_GET['Ccode3'] == 'all') $_GET['Ccode3'] = '';
		if($_GET['Ccode4'] == 'all') $_GET['Ccode4'] = '';
		$category_code = $_GET['Ccode1'].$_GET['Ccode2'].$_GET['Ccode3'].$_GET['Ccode4'];
		if($category_code) $where[] = "cl.category_code like '".$category_code."%'";

		$query = "select * from fm_goods g,fm_goods_image gi,fm_category_link cl left join fm_category c on cl.category_code=c.category_code
			where g.MallKind is not null and g.goods_seq=cl.goods_seq and g.goods_seq=gi.goods_seq and gi.image_type='list1' and gi.cut_number=1 and link";
		if($where) $query .= ' and ' .implode(' and ',$where);
		
		$res = mysql_query($query);
		
		echo "
		<table border=1>";
		while($data_goods = mysql_fetch_array($res)){
			$data_goods['goods_name'] = mb_convert_encoding($data_goods['goods_name'],"euc-kr", "utf-8");
			$data_goods['category'] = $this->split_code($data_goods['category_code']);
			$data_goods['stock_msg'] = ($data_goods['goods_status']=='runout') ? 0 : 1;			
			
			echo "
			<tr>
			<td class=MallKind>".$data_goods['MallKind']."</td>
			<td class=MallID>".$data_goods['MallID']."</td>
			<td class=Pcode>".$data_goods['ProdInc']."</td>
			<td class=Pname>".$data_goods['goods_name']."</td>
			<td class=Lprice>".$data_goods['Lprice']."</td>
			<td class=Sprice>".$data_goods['Sprice']."</td>
			<td class=Stock>".$data_goods['stock_msg']."</td>
			<td class=Purl>".$data_goods['realgoods_url']."</td>
			<td class=Pimg>".$data_goods['image']."</td>
			<td class=Ccode1>".$data_goods['category'][0]."</td>
			<td class=Ccode2>".$data_goods['category'][1]."</td>
			<td class=Ccode3>".$data_goods['category'][2]."</td>
			<td class=Ccode4>".$data_goods['category'][3]."</td>
			</tr>	";
		}
		echo "
		</table>";

	}

	public function price()
	{
		$cur_price 	= (int) $_POST['Lrprice'];
		$price 		= (int) $_POST['Srprice'];
		$ProdInc	= $_POST['ProdInc'];

		$query = $this->db->query("select * from fm_goods where ProdInc=? limit 1",array($ProdInc));
		list($data_goods) =  $query->result_array();
		$goods_seq = $data_goods['goods_seq'];

		$query = "update fm_goods set unit_price=?,Sprice=?,Lprice=?,update_date=now() where goods_seq=?";
		$this->db->query($query,array($price,$price,$cur_price,$goods_seq));

		$sql = "update fm_goods_option set consumer_price=?,price=? where goods_seq=?";
		$this->db->query($sql,array($cur_price,$price,$goods_seq));		
		echo "OK";
	}

	public function stock()
	{
		/*
		$_POST = array(
		'CustID'=>'polomix',
'ProdInc'=>'90001735',
'Pcode'=>'2000457',
'MallKind'=>'US',
'MallID'=>'ralphlauren',
'ServiceID'=>'MALL',
'Purl'=>'http://www.ralphlauren.com/product/index.jsp?productId=18679356',
'Pimg'=>'http://www.ralphlauren.com/graphics/product_images/pPOLO2-14995959_standard_t240.jpg',
'Pname'=>'Pleated Polo Dress',
'Brand'=>'Polo Ralph Lauren',
'ItemCode'=>'#18679356',
'Sku'=>'#18679356',
'Nation'=>'',
'Maker'=>'',
'LpriceL'=>'45.00',
'SpriceL'=>'34.99',
'Lprice'=>'45.00',
'Sprice'=>'34.99',
'LpriceChange'=>'0',
'SpriceChange'=>'0',
'LogoImg'=>'http://polo.imageg.net/images/topnav_logo.gif',
'Story'=>'This sleeveless polo dress features a preppy pleated hem and our signature pony embroidery. Ribbed polo collar. Six-button placket.<br> Racerback silhouette. <br> Box-pleated hem.<br> Our signature embroidered pony accents the left chest.<br> 100% cotton. Machine washable. Imported.<br>',
'Stock'=>'1',
'StockMsg'=>'정상',
'OutMsg'=>'',
'StockL'=>'1',
'StockChange'=>'0',
'ColorCnt'=>'4',
'SizeCnt'=>'11',
'StillCnt'=>'1',
'ColorStock'=>'Belmont Pink~~http://www.ralphlauren.com/graphics/product_images/pPOLO2-14995944_lifestyle_v360x480.jpg^http://www.ralphlauren.com/graphics/product_images/pPOLO2-14995944_swatch_t50.jpg^http://www.ralphlauren.com/graphics/product_images/pPOLO2-14995944_standard_dt.jpg^^^^^^^^^^^***Royal Violet~~http://www.ralphlauren.com/graphics/product_images/pPOLO2-14995966_lifestyle_v360x480.jpg^http://www.ralphlauren.com/graphics/product_images/pPOLO2-14995966_swatch_t50.jpg^http://www.ralphlauren.com/graphics/product_images/pPOLO2-14995966_standard_dt.jpg^^^^^^^^^^^***White~~http://www.ralphlauren.com/graphics/product_images/pPOLO2-14995951_lifestyle_v360x480.jpg^http://www.ralphlauren.com/graphics/product_images/pPOLO2-14995951_swatch_t50.jpg^http://www.ralphlauren.com/graphics/product_images/pPOLO2-14995951_standard_dt.jpg^^^^^^^^^^^***Vineyard Green~~http://www.ralphlauren.com/graphics/product_images/pPOLO2-14995959_lifestyle_v360x480.jpg^http://www.ralphlauren.com/graphics/product_images/pPOLO2-14995959_swatch_t50.jpg^http://www.ralphlauren.com/graphics/product_images/pPOLO2-14995959_standard_dt.jpg^^^^^^^^^^^***',
'SizeStock'=>'Belmont Pink~~SMALL^MEDIUM^LARGE^X-LARGE^***Royal Violet~~SMALL^MEDIUM^LARGE^X-LARGE^***Vineyard Green~~X-LARGE^***White~~LARGE^X-LARGE^',
'StillStock'=>'http://www.ralphlauren.com/graphics/product_images/pPOLO2-14995951_alternate4_dt.jpg^',
'MDID'=>'현경',
'LpriceP'=>'45.00',
'SpriceP'=>'34.99',
'Lrprice'=>'54900.00',
'Srprice'=>'42700.00',
'Lsprice'=>'54900.00',
'Ssprice'=>'42700.00',
'ExchangeRate'=>'1220.00',
'Shipping'=>'0.00',
'Trate'=>'0.0000',
'Weight'=>'1.00',
'DeliveryFee'=>'0.00',
'Drate'=>'1.000',
'Dadd'=>'0.00',
'Krate'=>'1.000',
'Kadd'=>'0.00',
'Crate'=>'1.000',
'CstdD'=>'0.00',
'CstdK'=>'0',
'CstdType'=>'D',
'Customs'=>'0.00',
'AddTax'=>'0.00',
'Prate'=>'1.000',
'Profit'=>'0.00'
		);

		$_POST = array(
			
			'ProdInc'=>'90000029',
			'Pcode'=>'2001117',
			'MallKind'=>'US',
			'MallID'=>'ralphlauren',
			'ServiceID'=>'MALL',
			'Purl'=>'http://www.ralphlauren.com/product/index.jsp?productId=17536036',
			'Pimg'=>'http://www.ralphlauren.com/graphics/product_images/pPOLO2-14690333_standard_t240.jpg',
			'Pname'=>'Slim-Fit Woven-Collar Polo',
			'Brand'=>'Polo Ralph Lauren',
			'ItemCode'=>'#17536036',
			'Sku'=>'#17536036',
			'Nation'=>'',
			'Maker'=>'',
			'LpriceL'=>'88.00',
			'SpriceL'=>'79.00',
			'Lprice'=>'89.50',
			'Sprice'=>'64.99',
			'LpriceChange'=>'1',
			'SpriceChange'=>'2',
			'LogoImg'=>'http://polo.imageg.net/images/topnav_logo.gif',
			'Story'=>'This slim-fitting golf polo is crafted from ultra-soft stretch Pima cotton and designed with a woven collar. Woven point collar with gingham lining. Three-button placket. <br> Sewn short sleeves. Uneven vented hem.<br> Buttoned flap pocket at the left chest. <br> 97% Pima cotton. 3% elastane. Machine washable. Imported. <br>',
			'Stock'=>'1',
			'StockMsg'=>'정상',
			'OutMsg'=>'',
			'StockL'=>'1',
			'StockChange'=>'0',
			'ColorCnt'=>'2',
			'SizeCnt'=>'10',
			'StillCnt'=>'0',
			'ColorStock'=>'Purple~~http://www.ralphlauren.com/graphics/product_images/pPOLO2-14690333_lifestyle_v360x480.jpg^http://www.ralphlauren.com/graphics/product_images/pPOLO2-14690333_swatch_t50.jpg^http://www.ralphlauren.com/graphics/product_images/pPOLO2-14690333_standard_dt.jpg^^^^^^^^^^^***French Navy~~http://www.ralphlauren.com/graphics/product_images/pPOLO2-14690338_lifestyle_v360x480.jpg^http://www.ralphlauren.com/graphics/product_images/pPOLO2-14690338_swatch_t50.jpg^http://www.ralphlauren.com/graphics/product_images/pPOLO2-14690338_standard_dt.jpg^^^^^^^^^^^***',
			'SizeStock'=>'French Navy~~SMALL^MEDIUM^LARGE^X-LARGE^XX-LARGE^***Purple~~SMALL^MEDIUM^LARGE^X-LARGE^XX-LARGE^',
			'StillStock'=>'',
			'MDID'=>'현경',
			'LpriceP'=>'89.50',
			'SpriceP'=>'64.99',
			'Lrprice'=>'120800.00',
			'Srprice'=>'87700.00',
			'Lsprice'=>'120800.00',
			'Ssprice'=>'87700.00',
			'ExchangeRate'=>'1350.00',
			'Shipping'=>'0.00',
			'Trate'=>'0.0000',
			'Weight'=>'1.00',
			'DeliveryFee'=>'0.00',
			'Drate'=>'1.000',
			'Dadd'=>'0.00',
			'Krate'=>'1.000',
			'Kadd'=>'0.00',
			'Crate'=>'1.000',
			'CstdD'=>'0.00',
			'CstdK'=>'0',
			'CstdType'=>'D',
			'Customs'=>'0.00',
			'AddTax'=>'0.00',
			'Prate'=>'1.000',
			'Profit'=>'0.00'
		);
		
		
	*/	

		$cur_price 	= (int) $_POST['Lrprice'];
		$price 		= (int) $_POST['Srprice'];
		$ProdInc	= $_POST['Pcode'];		

		$query = $this->db->query("select * from fm_goods where ProdInc=? limit 1",array($ProdInc));
		list($data_goods) =  $query->result_array();
		$goodsseq = $data_goods['goods_seq'];
		
		$bind_seq = array();
		$bind_seq[] = $goodsseq;

		$bind = array();
		$bind[] = (!$_POST['Stock'])?'runout':'normal'; // goods_status
		$bind[] = (!$_POST['Stock'])?'notLook':'look'; // goods_view
		$bind[] = $_POST['Sprice']; // unit_price
		$bind[] = $_POST['Sprice']; // Sprice
		$bind[] = $_POST['Lprice']; // Lprice
		$bind[] = $goodsseq;
		$query = "update fm_goods set goods_status=?,goods_view=?,unit_price=?,Sprice=?,Lprice=?,update_date=now() where goods_seq=?";
		$this->db->query($query,$bind);

		// 상품상태 폴로믹스로 전달
		$data_goods = $this->goodsmodel->get_goods($goodsseq);
		$ItemCode	= $data_goods['goods_code'];		
				
		
		$tmp = explode('***',$_POST['SizeStock']);
		$option_data = array();
		$option_title = "색상,사이즈";
		if(count($tmp)){
			foreach($tmp as $tmp1){
				$r_opt = explode('^',$tmp1);
				if($r_opt){
					foreach($r_opt as $k => $opt){
						$size = $opt;
						if($k == 0){
							$t_opt = explode('~~',$opt);
							$color = $t_opt[0];
							$size = $t_opt[1];
						}
						if($color && $size){
							$option_data[] = array($color,$size);
						}
					}
				}
			}
		}

		if($option_data && $price){
			
			$query = "delete from fm_goods_option where goods_seq=?";
			$this->db->query($query,$bind_seq);
			$query = "delete from fm_goods_supply where goods_seq=?";
			$this->db->query($query,$bind_seq);
			
			foreach($option_data as $k=>$r){
				
				$sql = "insert into fm_goods_option set
				goods_seq = '{$goodsseq}',
				default_option = '".($k==0?'y':'n')."',
				option_title = '{$option_title}',
				option1 = '{$r[0]}',
				option2 = '{$r[1]}',
				consumer_price = '{$cur_price}',
				price = '{$price}'
				";
				$this->db->query($sql);
				$option_seq = $this->db->insert_id();

				$sql = "insert into fm_goods_supply set
				goods_seq = '{$goodsseq}',
				option_seq = '{$option_seq}',
				supply_price = '0',
				stock = '100'
				";
				$this->db->query($sql);				
			}
			
		}
		echo "OK";
	}

	public function delete_list()
	{
		$query = "select * from fm_cosmos_deletelog";
		$res = mysql_query($query);
		while( $data = mysql_fetch_array($res) ){
			echo $data['MallKind'].":".$data['MallID'].":".$data['ProdInc'].":".$data['DeleteDate8'].",";			
		}
	}

	public function delete_prod()
	{
		$query = "delete from fm_cosmos_deletelog where MallKind=? and MallID=? and ProdInc=?";
		$this->db->query($query,array($_POST['MallKind'],$_POST['MallID'],$_POST['ProdInc']));
	}


	public function prod_list()
	{		
		$query = "select * from fm_goods";		
		
		if( $_GET['MallKind']!="" ) {
			$arr_out[] = "MallKind[".$_GET['MallKind']."]";
			$arr_where[] = "MallKind='".$_GET['MallKind']."'";
		}
		if( $_GET['MallID']!="" ) {
			$arr_out[] = "MallID[".$_GET['MallID']."]";
			$arr_where[] = "MallID='".$_GET['MallID']."'";
		}
		if( $_GET['ProdIncS']!="" ){
			$arr_out[] = "ProdIncS[".$_GET['ProdIncS']."]";
			$arr_where[] = "ProdInc>='".$_GET['ProdIncS']."'";
		}
		if( $_GET['ProdIncE']!="" ){
			$arr_out[] = "ProdIncE[".$_GET['ProdIncE']."]";
			$arr_where[] = "ProdInc<='".$_GET['ProdIncE']."'";
		}
	
		if($arr_where){
			$query .= " where ".implode(' and ',$arr_where);			
			
			$res = mysql_query($query);
			echo implode(' / ',$arr_out)."\n";
			
			$i = 0;
			while( $data = mysql_fetch_array($res) ){
				if($i>0) echo ",";
				echo $data['ProdInc'];
				$i++;
			}
			echo "[EOF]";
		}
		
		
	}
	public function split_code($code)
	{
		for($i=0;$i < strlen($code);$i+=4){
			$start = $i;
			$r_code[] = substr($code,$start,4);
		}
		return $r_code;
	}

	public function set_brand($brandnm){

		$query = "select category_code from fm_brand where title=? limit 1";
		$query = $this->db->query($query,array($brandnm));
		$data = $query->row_array();
		if($data['category_code']){
			$catecode = $data['category_code'];
		}else{
		
			$max_key	= 0;
			$parent_id	= 2;
			$position	= $this->brandmodel->get_next_positon($parent_id);
			$category	= $this->brandmodel->get_next_brand();
			$left		= $this->brandmodel->get_next_left();
			$depth_chk	= $max_key+1;
			$right		= $left + ($depth_chk + ($depth_chk-1));
			
			$level = (strlen($category)/4) + 1;
			$data = array (
				'parent_id'		=> $parent_id,
				'position'		=> $position,
				'title'			=> $brandnm,
				'type'			=> 'folder',
				'left'			=> $left,
				'right'			=> $right,
				'level'			=> $level,
				'category_code' => $category,
				'regist_date'	=> date("Y-m-d H:i:s")
			);
			$result		= $this->db->insert('fm_brand', $data);
			$parent_id	= $this->db->insert_id();
			$catecode = $this->brandmodel->get_brand_code($parent_id);
			$catename = $this->brandmodel->get_brand_name($catecode);
		}
		
		return $catecode;
		
	}
	public function makectno($no){
		$no-=0;
		while(strlen($no)<4) $no="0".$no;
		return $no;
	}

	public function _cut_image_insert($new_image,$goods_seq,$cut)
	{
		$sql = "insert into fm_goods_image set goods_seq = ?,cut_number = ?,image_type = 'list1',image = ?;";
		$this->db->query($sql,array($goods_seq,$cut,$new_image));
		$sql = "insert into fm_goods_image set goods_seq = ?,cut_number = ?,image_type = 'list2',image = ?;";
		$this->db->query($sql,array($goods_seq,$cut,$new_image));
		$sql = "insert into fm_goods_image set goods_seq = ?,cut_number = ?,image_type = 'thumbView',image = ?;";
		$this->db->query($sql,array($goods_seq,$cut,$new_image));		
		$sql = "insert into fm_goods_image set goods_seq = ?,cut_number = ?,image_type = 'large',image = ?;";
		$this->db->query($sql,array($goods_seq,$cut,$new_image));
		$sql = "insert into fm_goods_image set goods_seq = ?,cut_number = ?,image_type = 'view',image = ?;";
		$this->db->query($sql,array($goods_seq,$cut,$new_image));
		if($cut == 1){
			$sql = "insert into fm_goods_image set goods_seq = ?,cut_number = ?,image_type = 'thumbCart', image = ?;";
			$this->db->query($sql,array($goods_seq,$cut,$new_image));
			$sql = "insert into fm_goods_image set goods_seq = ?,cut_number = ?,image_type = 'thumbScroll', image = ?;";
			$this->db->query($sql,array($goods_seq,$cut,$new_image));		
		}
	}
}

/* End of file cosmos.php */
/* Location: ./app/controllers/cosmos.php */