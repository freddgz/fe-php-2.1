<?php
class Usuario extends CI_Model{

	function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	function insert_hash($comprobante, $hash){
		$arr = array(
			'CPP_Codigo' => $comprobante,
			'Cod_Hash'   => $hash
		);
		$this->db->insert("cji_comprobante_hash", $arr);
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