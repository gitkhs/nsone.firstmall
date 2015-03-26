<?php 
class searchwordmodel extends CI_Model {
	public function insert_word($page_yn,$page,$word,$search_result,$search_result_link,$search_result_target)
	{
		$bind = array();
		$bind[] = ($page_yn) ? $page_yn : 'n';
		$bind[] = $page;
		$bind[] = $word;
		$bind[] = $search_result;
		$bind[] = $search_result_link;
		$bind[] = $search_result_target;
		$query = "insert into fm_search_word set page_yn=?,page=?,word=?,search_result=?,search_result_link=?,search_result_target=?,regist_date=now()";
		$this->db->query($query,$bind);
	}

	public function truncate_word()
	{
		$query = "truncate table fm_search_word";
		$this->db->query($query);
	}
	
	public function get_word_by_page($page)
	{
		$bind[] = $page;
		$query = "select * from fm_search_word where page=? and page_yn='y' order by rand() limit 1";
		$query = $this->db->query($query,$bind);
		return $query->result_array();
	}
}
?>