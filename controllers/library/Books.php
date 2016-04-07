<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Books extends CI_Controller {
	
	/**
	* Loading the required library & helper.
	**/
	public function __construct(){
		parent::__construct();
		$this->load->helper(array('form','url')); //load form helper on demand.
		$this->load->library(array('form_validation','session')); //loading form validation library
	}
	/** Searching books with author / title / book id
	* @param 
	* @return void 
	**/
	public function search()
	{		
		$data = array();
		
		if($this->input->get()){
			
			$post_data = $this->input->get();
			if(isset($post_data['isbn']) && isset($post_data['author']) && isset($post_data['title']))
			{
				$post_data = array('isbn' => $post_data['isbn'],
									'author' => $post_data['author'],
									'title'  => $post_data['title'],
									'title_condition' => $post_data['title_condition'],
									'isbn_condition'  => $post_data['isbn_condition']
								);
				
				$validation = true;
				if(empty($post_data['isbn']) && empty($post_data['author']) && empty($post_data['title']))
					$validation = $this->form_validation->set_data($post_data)
							->set_rules('isbn', 'ISBN or Title or Author', 'required')->run();
				
				if ($validation == TRUE)
				{
					$this->load->model('library/search', 'search');
					$data['result'] = $this->search->searchResults();
					$base_url = base_url("library/books/search");
					$rows = count($data['result']);
					$data['post_data'] = $post_data;
					if($rows){
						$this->session->set_flashdata('error_msg','');
						$data['pagination'] = $this->createPageLinks($base_url,$rows);
					}else{
						$this->session->set_flashdata('error_msg','Sorry the book you are looking for is not available!'); 
					}
					$this->load->library('table');
				}
						
			}
		}
		$this->load->template('library/search_books',$data);
	}
	
	/**
	* Check out books here.
	* @param
	* @return json
	**/
	public function checkout(){
		$post_data = $this->input->post();
		$this->form_validation->set_data($post_data)
							->set_rules('isbn', 'Radio', 'required',
								 array('required' => 'Please select a book!')
							);
		$this->form_validation->set_data($post_data)
							->set_rules('card_no', 'card numer', 'required');
		if($this->form_validation->run() == FALSE){
			$data['error'] = validation_errors();
		}else{
			$this->load->model('library/bookloans', 'book_loans');
			$data['result'] = $this->book_loans->checkout();
		}
		
		$this->output
		->set_content_type('application/json')
		->set_output(json_encode($data));
	}
	
	/**
	* Check in / return books here.
	* @param
	* @return json
	**/
	public function checkin(){
		$post_data = $this->input->post();
		$this->form_validation->set_data($post_data)
							->set_rules('isbn', 'Radio', 'required',
								 array('required' => 'Please select a book and ensure card number matches!')
							);
		$this->form_validation->set_data($post_data)
							->set_rules('date_in', 'Date', 
							array('required','regex_match[/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/]'));
		if($this->form_validation->run() == FALSE){
			$data['error'] = validation_errors();
		}else{
			$this->load->model('library/bookloans', 'book_loans');
			$data['result'] = $this->book_loans->checkin();
		}
		
		$this->output
		->set_content_type('application/json')
		->set_output(json_encode($data));
	}
	
	/** Searching Loans with isbn / title / card no
	* @param 
	* @return void 
	**/
	public function searchloans()
	{		
		$data = array();
		
		if($this->input->get()){
			
			$post_data = $this->input->get();
			if(isset($post_data['isbn']) && isset($post_data['borrower']) && isset($post_data['card_no']))
			{
				$post_data = array('isbn' => $post_data['isbn'],
									'borrower' => $post_data['borrower'],
									'card_no' => $post_data['card_no'],
									'title_condition' => $post_data['title_condition'],
									'isbn_condition'  => $post_data['isbn_condition']
								);
				
				$validation = true;
				$data['post_data'] = $post_data;
				if(empty($post_data['borrower']) && empty($post_data['card_no']) && empty($post_data['isbn']))
					$validation = $this->form_validation->set_data($post_data)
							->set_rules('card_no', 'ISBN or borrower name or card number', 'required')->run();
							
				if ($validation == TRUE)
				{
					$this->load->model('library/bookloans', 'bookloans');
					$this->load->model('library/search', 'search');
					$data['result'] = $this->bookloans->searchLoanResult();
					$base_url = base_url("library/books/search");
					$rows = count($data['result']);
					
					if($rows){
						$this->session->set_flashdata('error_msg','');
						$data['pagination'] = $this->createPageLinks($base_url,$rows);
					}else{
						$this->session->set_flashdata('error_msg','No book loans found!'); 
					}
					$this->load->library('table');
				}
						
			}
		}
		$this->load->template('library/search_loans',$data);
	}
	
	/** Creating Page links
	*
	* @param string $base_url
	* @param int $rows
	* @return mixed
	**/
	private function createPageLinks($base_url,$rows){
		$this->load->library('pagination');
		$config['base_url'] = $base_url;
		$config['total_rows'] = $rows;
		$config['per_page'] = 20;
		$config['reuse_query_string'] = true;

		$this->pagination->initialize($config);
		return $this->pagination->create_links();
	}
}
