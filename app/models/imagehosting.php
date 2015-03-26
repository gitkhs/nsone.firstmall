<?php
/**
 * 이미지호스팅 관련 모듈
 * @author gabia
 * @since version 1.0 -2014-05-27
 */
class Imagehosting extends CI_Model {
	
	var $imagehostingdir			= '/firstmall_goods';
	var $gabiaimagehostingurl	= '.speedgabia.com';
	var $imagehostingftp = array(
			'hostname'=>'',
			'username'=>'',
			'password'=>'',
			'port'=>21,
			'passive'=>FALSE,
			'debug'=>FALSE
		);//TRUE FALSE
	var $table_imghosting = 'fm_imagehostingfiles';
	var $imgmatch = "/<IMG[^>]*src=[\"']?([^>\"']+)[\"']?[^>]*>/i";

	function __construct() {
		parent::__construct(); 
		$this->load->library('ftp'); 
		$imagehostingconf = config_load('imagehosting'); 
		$this->imagehostingftp['imagehostingdir']	= $this->imagehostingdir; 
		$this->imagehostingftp['gabiaimagehostingurl']	= $this->gabiaimagehostingurl;
		$this->imagehostingftp['hostnameid']		= (trim($imagehostingconf['hostname']))?trim($imagehostingconf['hostname']):trim($_POST['hostname']);
		if( $_POST['hostname'] ) {
			$this->imagehostingftp['hostname']		= trim($_POST['hostname']).$this->imagehostingftp['gabiaimagehostingurl'];
		}else{
			$this->imagehostingftp['hostname']		= (trim($imagehostingconf['hostname']))?trim($imagehostingconf['hostname']).$this->imagehostingftp['gabiaimagehostingurl']:'';
		}
		$this->imagehostingftp['username']		= (trim($_POST['username']))?trim($_POST['username']):'';
		$this->imagehostingftp['password']		= (trim($_POST['password']))?trim($_POST['password']):'';
		$this->imagehostingftp['imagedelete']		= (trim($_POST['imagedelete']))?trim($_POST['imagedelete']):'';
	}

	// 이미지호스팅 > 상세설명 
	function set_contents($fieldname ='contents', $contents, $goods_seq)
	{
		$cnt = preg_match_all($this->imgmatch,$contents, $matches); 
		$changenum = $totalnum =0;
		foreach($matches[1] as $img_key => $ori_img) {
			$iskor = false;
			if(preg_match("/[\xA1-\xFE\xA1-\xFE]/",$ori_img)) {//한글파일명처리 //$ori_img = iconv('utf-8','cp949',$ori_img);
				$iskor = true;
			}

			$img = $ori_img;
			$t_arr_img = explode(' ',$ori_img);
			$ori_img = $t_arr_img[0];

			if( preg_match('/http:\/\//',$img) ){
				$arr_img = explode('/',$img);
				unset($arr_img[0],$arr_img[1],$arr_img[2]);
				$img = implode('/',$arr_img);
			}else{
				if(substr($img,0,1) == '/') $img = substr($img,1);
			}
			$img_tag = '<img src="'.$ori_img.'" border="0" />'; 
			$size = @getimagesize($img); 
			if( $size ){
				$totalnum++;  
				if( !strstr($ori_img,"data/editor/") ) {//다른위치의 파일이라면 폴더생성 - 파일겹치지 않게 하기 위해 
					$folder = $this->ftpnewdir();//최초에만 저장
				}else{
					$folder = '';
				}

				$nx=explode('.',$img);
				$file_ext = $nx[count($nx)-1];
				$fileNamear = explode('/',$nx[0]);
				if( $iskor) {//한글파일
					$fileName = "temp_".time().sprintf("%04d",rand(0,9999));
				}else{
					$fileName = $fileNamear[count($fileNamear)-1];
				}

				if( preg_match('/http:\/\//',$img) || preg_match('/https:\/\//',$img) ){
					$newimg = $img;
				}else{
					$newimg = ROOTPATH.$img;
				}

				$imgserver = $this->ftpupload($newimg, $folder,  $fileName.".".$file_ext);
				if( $imgserver['return'] === true ) {
					$img_tag = '<img src="http://'.$this->imagehostingftp['hostname'].$imgserver['folder'].$imgserver['fileName'].'" border="0"  />';//width="'.$size[0].'" height="'.$size[1].'"
					$sc['select']		= ' seq ';
					$sc['whereis']	= ' and goods_seq = "'.$goods_seq.'" and ori_name = "'.$ori_img.'" '; 
					$getdata = $this->filestable_get_data($sc); 
					if( !$getdata ) {
						$filesparams['goods_seq']			= $goods_seq;
						$filesparams['file_ext']					= $file_ext;
						$filesparams['url']						= 'http://'.$this->imagehostingftp['hostname'];
						$filesparams['folder']					= $imgserver['folder'];
						$filesparams['ori_name']				= $ori_img;
						$filesparams['tmpname']				= $imgserver['fileName'];//
						$filesparams['r_date']					=  date("Y-m-d H:i:s");
						$filesparams['image_width']		= $size[0];
						$filesparams['image_height']		= $size[1];
						$filesparams['local_file_delete']	= $this->imagehostingftp['imagedelete'];
						$this->filestable_write($filesparams); 
					}
					$changenum++;
					if( $this->imagehostingftp['imagedelete'] == 1 ) {//원본이미지삭제
						//@unlink($ori_img);
					}
				}else{
					continue;
				}
			}
			$replace[$img_key] = $img_tag;
		}//endforeach

		//if($changenum){
			$new_contents = str_replace($matches[0],$replace,$contents);
			$this->get_contents_cnt($new_contents,$changeimg,$orgimg);//변환이미지, 미변환이미지 
			$query = "update fm_goods set ".$fieldname."=?,convert_image_cnt=?,noconvert_image_cnt=?,convert_image_date=? where goods_seq=?";
			$this->db->query($query,array($new_contents,$changeimg,$orgimg,date("Y-m-d H:i:s"),$goods_seq));
		//}
		$result = array('totalnum' => $totalnum, 'changenum' => $changenum,'newcontents'=>$new_contents);
		return $result;
	}
	
	function ftpconn(){
		$this->ftp->connect($this->imagehostingftp);
		
		$changebasedir = $this->ftp->changedir($this->imagehostingdir.'/');//ftp_chdir  
		//debug_var($changebasedir.'/');
		if( $changebasedir == false ) {//기본디렉토리점검
			$this->ftp->mkdir($this->imagehostingdir.'/');//ftp_mkdir;
			$this->ftp->changedir($this->imagehostingdir.'/');//ftp_chdir
		}
	}

	function ftpclose(){
		$this->ftp->close(); 
	}

	//savedir
	function ftpnewdir(){
		$today = date("Ymd");
		$savePath1	= $this->imagehostingdir.'/'.substr($today,0,4);
		$savePath2	= $savePath1.'/'.substr($today,4,2);
		$savePath3	= $savePath2.'/'.substr($today,6,2);
		$folder		= substr($today,0,4).'/'.substr($today,4,2).'/'.substr($today,6,2);   

		for ($i = 1; $i < 4; $i++)
		{
			$dir = ${'savePath'.$i};// debug_var($dir.'/');
			$changebasedir = $this->ftp->changedir($dir.'/');
			if( $changebasedir == false ) {//기본디렉토리점검
				$this->ftp->mkdir($dir.'/');//ftp_mkdir
				$this->ftp->changedir($dir.'/');//ftp_chdir
			}
		}
		return $folder;
	}

	function ftpupload($filepath, $folder=NULL, $fileName) {
		if( $fileName ) {
			$fileName = str_replace(" ","_",$fileName);
			$folder = ($folder)?'/'.$folder.'/':'/';
			unset($imgserver);
			//debug_var($filepath."===>".$this->imagehostingdir.$folder.$fileName);//exit;
			if (  file_exists($filepath))
			{
				$imgupload = $this->ftp->upload($filepath,$this->imagehostingdir.$folder.$fileName,'binary');//ftp_put 
				//debug_var($imgupload);
				if( $imgupload === true ){
					$imgserver['return']		= $imgupload; 
					$imgserver['folder']		= $this->imagehostingdir.$folder;
					$imgserver['fileName'] = $fileName;
				}
			}
			//debug_var($imgserver);
		}
		return $imgserver;
	}
	
	function get_contents_cnt($contents,&$changeimg,&$orgimg)
	{
		$changeimg = $orgimg = 0;
		$cnt = preg_match_all($this->imgmatch,$contents, $matches);
		foreach($matches[1] as $img_key => $ori_img){
			if( strstr($ori_img,$this->imagehostingdir) && strstr($ori_img,$this->imagehostingftp['gabiaimagehostingurl']) ) {//변환된갯수 
				$changeimg++;
			}else{
				$orgimg++;
			}
		}
	}

	/*
	 * 파일정보
	 * @param
	*/
	public function filestable_get_data($sc) {
		$sc['select'] = (!$sc['select'])?" * ":$sc['select'];
		$sql = "select ".$sc['select']." from  ".$this->table_imghosting."  where 1 ". $sc['whereis'];
		if(isset($sc['seq'])) $sql.= ' and seq='.$sc['seq'];

		$sql .=" order by seq desc ";
		$query = $this->db->query($sql);
		$data = $query->row_array(); 
		return $data;
	}

	/*
	 * 파일생성
	 * @param
	*/
	public function filestable_write($params) {
		$data = filter_keys($params, $this->db->list_fields($this->table_imghosting));
		$result = $this->db->insert($this->table_imghosting, $data);
		return $this->db->insert_id();
	}

	/*
	 * 파일수정
	 * @param
	*/
	public function filestable_modify($params) {
		if(empty($params['seq']))return false;
		$data = filter_keys($params, $this->db->list_fields($this->table_imghosting));
		$result = $this->db->update($this->table_imghosting, $data,array('seq'=>$params['seq']));
		return $result;
	}

	/*
	 * 파일복사
	 * @param
	*/
	public function filestable_copy($goods_seq, $newgoods_seq, $filepath, $params) {
		$now = date("Y-m-d H:i:s");
		$sql = "INSERT INTO ".$this->table_imghosting."
			(goods_seq, file_ext, url, folder, ori_name, tmpname, r_date, image_width, image_height)
		SELECT
		'{$newgoods_seq}', file_ext , url, '{$filepath}', ori_name, tmpname, '{$now}', image_width, image_height
		FROM
			".$this->table_imghosting."
		WHERE
			parentseq = '{$goods_seq}' ";
		$result = $this->db->query($sql);
		return $this->db->insert_id();
	}

	/*
	 * 파일이동
	 * @param
	*/
	public function filestable_move($params) {
		if(empty($params['seq']))return false;
		$data = filter_keys($params, $this->db->list_fields($this->table_imghosting));
		$result = $this->db->update($this->table_imghosting, $data,array('seq'=>$params['seq']));
		return $result;
	}

	/*
	 * 파일삭제
	 * @param
	*/
	public function filestable_delete($seq) {
		if(empty($seq))return false;
		$result = $this->db->delete($this->table_imghosting, array('seq' => $seq));
		return $result;
	}

	// 파일총건수
	public function filestable_get_item_total_count()
	{
		$sql = 'select seq from '.$this->table_imghosting;
		$query = $this->db->query($sql);
		return $query->num_rows();
	}

}
/* End of file imagehosting.php */
/* Location: ./app/models/imagehosting */