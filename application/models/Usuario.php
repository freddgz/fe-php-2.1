<?php
class Usuario extends CI_Model{
	function __construct()
	{
		parent::__construct();
	}
	function getUsers()
	{
		$query=$this->db->get('usuario');
		return $query->result_array();
	}
	function getUser($id)
	{
		$this->db->where('id',$id);
		$query=$this->db->get('usuario');
		return $query->row();
	}
}