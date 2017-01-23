<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Welcome extends CI_Controller
{
    public function index()
    {
        $this->load->database();
        $len = count($this->db->queries); 
        $query = $len > 1 ? $this->db->queries[$len - 1] : '';
        echo $query;
        $this->stdreturn->ok();
    }
}
