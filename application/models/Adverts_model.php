<?php
defined('BASEPATH') or exit('Direct access to script not allowed');

/* ===== Documentation ===== 
Name: Adverts_model
Role: Model
Description: Controls the DB processes of Adverts_model from admin panel
Controller: Adverts, Home
Author: Sylvester Nmakwe
Date Created: 24th April, 2023
*/



class Adverts_model extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
	}



	public function get_advert_details($id)
	{ //get adverts details
		return $this->db->get_where('adverts', array('id' => $id))->row();
	}


	public function get_adverts($limit, $offset)
	{ //limit to be used as per_page, offset to be used as pagination segment
		$this->db->limit($limit, $offset); 
		$this->db->order_by("date_added", "DESC"); //order by date_unix ASC so that the dates appear chronologically
		$query = $this->db->get_where('adverts');
		if ($query->num_rows() > 0) {
			foreach ($query->result() as $row) {
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}


	public function get_published_adverts($limit, $offset)
	{ //get published adverts
		$this->db->limit($limit, $offset); //limit to be used as per_page, offset to be used as pagination segment
		$this->db->order_by("date_added", "DESC"); //order by date_unix DESC so that the dates appear chronologically
		$query = $this->db->get_where('adverts', array('published' => 'true'));
		if ($query->num_rows() > 0) {
			foreach ($query->result() as $row) {
				$data[] = $row;
			}
			return $data;
		}
		return false;
	}


	public function get_recent_published_adverts($limit)
	{ //get recent published adverts
		$this->db->order_by('date_added', 'DESC');
		$this->db->limit($limit);
		return $this->db->get_where('adverts', array('published' => 'true'))->result();
	}


	public function count_adverts()
	{
		return $this->db->get_where('adverts')->num_rows();
	}


	public function count_published_adverts()
	{
		return $this->db->get_where('adverts', array('published' => 'true'))->num_rows();
	}


	public function count_unpublished_adverts()
	{
		return $this->db->get_where('adverts', array('published' => 'false'))->num_rows();
	}



	/* ========== Admin Actions: Galleries ============= */
	public function upload_advert($photo)
	{
		$data = array(
			'photo' => $photo,
		);
		return $this->db->insert('adverts', $data);
	}


	public function publish_advert($photo_id)
	{
		$data = array(
			'published' => 'true',
		);
		$this->db->where('id', $photo_id);
		return $this->db->update('adverts', $data);
	}


	public function unpublish_advert($photo_id)
	{
		$data = array(
			'published' => 'false',
		);
		$this->db->where('id', $photo_id);
		return $this->db->update('adverts', $data);
	}


	public function delete_advert($photo)
	{
		$p = $this->get_advert_details($photo);
		//delete photo from folder
		unlink('./assets/adverts/' . $p->photo);
		//delete record from database
		$this->db->delete('adverts', array('id' => $photo));
	}


	public function bulk_actions_adverts()
	{
		$selected_rows = count($this->input->post('check_bulk_action', TRUE));
		$bulk_action_type = $this->input->post('bulk_action_type', TRUE);
		$row_id = $this->input->post('check_bulk_action', TRUE);
		$photos = ($selected_rows == 1) ? 'Advert' : 'Adverts';
		foreach ($row_id as $photo_id) {
			switch ($bulk_action_type) {
				case 'publish':
					$this->publish_advert($photo_id);
					$this->session->set_flashdata('status_msg', "{$selected_rows} {$photos} Published Successfully.");
					break;
				case 'unpublish':
					$this->unpublish_advert($photo_id);
					$this->session->set_flashdata('status_msg', "{$selected_rows} {$photos} Unpublished Successfully.");
					break;
				case 'delete':
					$this->delete_advert($photo_id);
					$this->session->set_flashdata('status_msg', "{$selected_rows} {$photos} Deleted Successfully.");
					break;
			}
		}
	}



}