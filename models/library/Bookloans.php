<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Bookloans extends CI_Model {
	
	/** Constans **/
	const max_loan = 3; //maximum allowed books to be borrowed
	const max_days = 14; // max days a can book can be borrowed
	
	/**
	* Book checkout validation / registration(entry) process.
	* @param
	* @return mixed
	**/
	public function checkout(){
		$error = $this->maxLoanValidation();
		if($error === false){
			$post_data = $this->input->post();
			$bookbranch_array = explode("-",$post_data['isbn']);
			$rows = $this->availabilityInBranch($bookbranch_array);
			if($rows > 0){
				$array = array(
							'book_id' =>$bookbranch_array[0],
							'branch_id' =>$bookbranch_array[1],
							'card_no' =>$post_data['card_no'],
							'date_out' =>date('Y-m-d'),
							'due_date' =>date('Y-m-d', strtotime("+".static::max_days." days"))
						);
				$this->db->set($array);
				$this->db->insert('book_loans');
				return true;
			}
			else
				$error = 'no_books';
		}
		return $error;
	}
	
	/**
	* Book check in update process.
	* @param
	* @return mixed
	**/
	public function checkin(){
		$post_data = $this->input->post();
		$this->db->set('date_in',$post_data['date_in']);
		$this->db->where('id',$post_data['isbn']);
		$this->db->update('book_loans');
		return true;
	}
	/**
	* Maximum allowed books and card number validation.
	* @param
	* @return mixed
	**/
	protected function maxLoanValidation(){
		$sql = $this->db->select('borrower.card_no,count(book_id)as books_on_loan')
			->from('borrower')
			->join('book_loans','book_loans.card_no = borrower.card_no','left')
			->where('borrower.card_no', $this->input->post('card_no'))
			->where('date_in')
			->get();
		$row = $sql->row();
		if(!$row->card_no)
			return 'invalid_card';
		else if($row->books_on_loan >= static::max_loan)
			return 'max_loan';
		else
			return false;

	}
	/**
	* Book availability in the specific selected branch.
	* @param
	* @return mixed
	**/
	protected function availabilityInBranch($bookbranch_array){
		$sql = $this->db->select('book_copies.book_id, count(book_loans.book_id)as books_on_loan, no_of_copies, book_copies.branch_id')
			->from('book_copies')
			->join('book_loans','book_loans.book_id = book_copies.book_id  
					AND book_loans.branch_id = book_copies.branch_id AND ISNULL(book_loans.date_in)','left')
			->where('book_copies.book_id', $bookbranch_array[0])
			->where('book_copies.branch_id', $bookbranch_array[1])
			//->where('book_loans.date_in')
			->group_by('book_id,branch_id')
			->get();
		$row = $sql->row();
		return $row->no_of_copies-$row->books_on_loan;
	}
	/**
	* Encapsulated function for loan search.
	* @param
	* @return array
	**/
	public function searchLoanResult(){
		return $this->searchLoans();
	}
	/**
	* Search loans from DB which has date_in is null.
	* @param
	* @return array
	**/
	protected function searchLoans(){
		
		$data = $this->input->get();
		
		$rawConditionArray = array(array('isbn'=>'book_loans.book_id'), 
											array('card_no' => 'borrower.card_no'),
										array('borrower'=>array('borrower.first_name','borrower.last_name'))
									);
		$conditions = array('isbn_condition','title_condition');
		$this->search->prepareConditionCombination($rawConditionArray,$conditions);
		
		$sql = $this->db->select('book_loans.id, book_loans.book_id,CONCAT(borrower.first_name,borrower.last_name) as name,due_date,
									book_loans.card_no,library_branch.branch_name,library_branch.branch_id')
			->from('book_loans')
			->join('borrower','borrower.card_no = book_loans.card_no','left')
			->join('books','books.book_id = book_loans.book_id','left')
			->join('library_branch','book_loans.branch_id = library_branch.branch_id','left')
			->where('date_in')
			->get();
		$row = $sql->result_array();
		return $row;
	}
	
}
