<?
/*

watermarkmodel
admin/skin/default/setting/watermark_setting.html
/data/watemark_preimg

CREATE TABLE `fm_watermark` (
  `watermark_seq` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '일련번호',
  `goods_seq` int(10) unsigned NULL,  
  `source_image` varchar(100) NOT NULL DEFAULT '' COMMENT '원본 이미지',
  `target_image` varchar(100) NOT NULL DEFAULT '' COMMENT '결과 이미지',
  `regist_date` datetime null,
  PRIMARY KEY (`watermark_seq`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
*/
class watermarkmodel extends CI_Model {
	var $source_image = '';
	var $target_image = '';	
	var $source_dir = '';
	var $goods_seq = '';
	var $insert_id = '';	
	
	function __construct()
	{
		$this->check_source_dir();
	}	
	
	function check_source_dir()
	{	
		$dir = ROOTPATH."data/watermark_preimg";
		$this->make_dir($dir);
		$this->source_dir = $dir."/".date("Ym");
		$this->make_dir($this->source_dir);
	}
	
	function source_image_cp()
	{		
		$this->insert_log();
		$ext = strtolower(end(explode('.', $this->target_image)));
		$this->source_image = $this->source_dir."/".date("dHi")."_".$this->insert_id.".".$ext;
		@copy($this->target_image, $this->source_image);		
	}
	
	function watermark()
	{	
		$this->update_log();	
		
		$this->load->library('watermarklib');	
		
		$arr_position[0] = TL;
		$arr_position[1] = TM;
		$arr_position[2] = TR;
		$arr_position[3] = ML;
		$arr_position[4] = MM;
		$arr_position[5] = MR;
		$arr_position[6] = BL;
		$arr_position[7] = BM;
		$arr_position[8] = BR;		
		
		
		$config_watermark = config_load('watermark');		
		//정보가없으면 경고
		if( !trim($config_watermark['watermark_image']) || !trim($config_watermark['watermark_type']) ) {
			return "ERR";
		}
		$water =  ROOTPATH.$config_watermark['watermark_image'];

		$this->watermarklib->image = '';		
		$this->watermarklib->load($this->source_image);		
		$size = getimagesize($this->source_image);		
		$this->watermarklib->isResizeWarterMark = true;
		
		/*
		$file_ext = end(explode('.', $this->source_image));
		if(trim($file_ext) == 'gif'){
			return "ERRGIF";
		}
		*/

		
		
		if($config_watermark['watermark_type']=='position'){
			$r_watermark_position = explode('|',$config_watermark['watermark_position']);			
			foreach($r_watermark_position as $p){
				$position += $arr_position[$p];
			}
			
			$offset = (int) ($size[0]/20);
			$this->watermarklib->addWaterMark($water, $position, $offset);
		}else{
			$offset = $size[1];
			if($size[0] > $size[1]){
				$offset = $size[0];
			}
			$offset = (int) ($offset/3);		
			

			$this->watermarklib->addPatternWaterMark($water, $offset);
		}
			
		$this->watermarklib->save($this->target_image, IMAGETYPE_PNG);
		return "OK";
	}
	
	function insert_log()
	{		
		$query = "insert into fm_watermark set regist_date=now()";
		$this->db->query($query,$bind);
		$this->insert_id = $this->db->insert_id();
	}
	
	function update_log()
	{	
		$bind[] = $this->goods_seq;
		$bind[] = $this->source_image;
		$bind[] = $this->target_image;
		$bind[] = $this->insert_id;
		
		$query = "update fm_watermark set `goods_seq`=? ,`source_image`=?,`target_image`=? where watermark_seq=?";
		$this->db->query($query,$bind);		
		
	}
	
	function get_log()
	{	
		
		$bind[] = $this->target_image;		
		$query = "select * from fm_watermark where target_image=? order by watermark_seq limit 1";
		$query = $this->db->query($query,$bind);
		$row = $query->row_array();				
		return $row;
	}
	
	function get_log_all()
	{
	
		$bind[0] = $this->goods_seq;
		$query = "select * from fm_watermark where goods_seq=? order by watermark_seq desc";
		$query = $this->db->query($query,$bind);
		foreach($query->result_array() as $data){
			$result[] = $data;
		}
		
		return $result;
	}
	
	function make_dir($dir)
	{
		if( !is_dir($dir) )
		{
			@mkdir($dir);
			@chmod($dir,0707);
		}
	}
	
	function del_log()
	{
		$bind[0] = $this->goods_seq;
		$query = "delete from fm_watermark where goods_seq=?";
		$this->db->query($query,$bind);
	}
	
	function recovery()
	{		
		$result = $this->get_log_all();
		$last_key = count($result)-1;
		
		foreach($result as $k => $data)
		{	
			@copy($data['source_image'],$data['target_image']);
			@unlink($data['source_image']);
		}
		
		$this->del_log();
	}
	
	function move_target_image($from,$to)
	{
		$bind[0] = $to;
		$bind[1] = $from;
		$query = "update fm_watermark set target_image=? where target_image=?";
		$this->db->query($query,$bind);		
	}
	
	function watermark_setting()
	{
		$this->load->library('upload');
		$uploaded = true;
		
		if (is_uploaded_file($_FILES['watermark_file']['tmp_name']))
		{
			$config['upload_path']		= ROOTPATH."data/icon/watermark";			
			$this->make_dir($config['upload_path']);
				
			$file_ext = end(explode('.', $_FILES['watermark_file']['name']));//확장자추출
			$config['allowed_types']	= 'png';
			$config['overwrite']			= TRUE;
			$config['file_name']			= substr(microtime(), 2, 6).'.'.$file_ext;
			$this->upload->initialize($config);			
				
			if ($this->upload->do_upload('watermark_file'))
			{
				@chmod($config['upload_path'].$config['file_name'], 0707);	
				
				config_save('watermark',array('watermark_image'=>'/data/icon/watermark/'.$config['file_name']));
			}else{
				$err = $this->upload->display_errors('', '');
				$callback = "";
				openDialogAlert($err,400,140,'parent',$callback);
				$uploaded = false;
			}	
		}else{
			$msg = "워터마크 이미지를 선택해 주세요.";
			openDialogAlert($msg,400,140,'parent',$callback);
			exit;
		}
		
		if($uploaded){
			if($_POST['watermark_type'] == 'position' && $_POST['watermark_position'])
			{
				$watermark_position = implode("|",$_POST['watermark_position']);
			}
		
			config_save('watermark',array('watermark_type'=>$_POST['watermark_type']));
			config_save('watermark',array('watermark_position'=>$watermark_position));
			openDialogAlert("설정이 저장 되었습니다.",400,140,'parent',$callback);
		}else{
			$msg = "워터마크 이미지 및 스타일을 정확히 선택해 주세요.";
			openDialogAlert($msg,400,140,'parent',$callback);
			exit;
		}
		
	}
}
