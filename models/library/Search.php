<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Search extends CI_Model {

	/**
	* Encapsulated function for Search books 
	* 
	*
	*/
	public function searchResults()
	{
		return $this->searchBooks();
	}
	/**
	* Prepare the author sql query string to append
	* with main search query.
	* @param
	* @return string $authors_sql
	**/
	protected function authorSearch(){
		$authors_sql = $this->db->select('book_id,group_concat(distinct author_name) as authors')
			->from('book_authors')
			->group_by('book_id')
			->get_compiled_select();
		return $authors_sql;
	}
	/**
	* Book search process happens here.
	* 
	* @param
	* @return array $result_array
	**/
	protected function searchBooks(){
		$authors_sql = $this->authorSearch();
		
		$data = $this->input->get();
		
		$rawConditionArray = array(array('isbn'=>'books.book_id'), 
									array('title'=>'books.title'),
									array('author' => 'authors')
								);
		$conditions = array('isbn_condition','title_condition');
		$this->prepareConditionCombination($rawConditionArray, $conditions);		
		
		$sql = $this->db
			->select('books.book_id, library_branch.branch_name, 
						book_authors.authors, books.title, no_of_copies,library_branch.branch_id')
			->from('books')
			->join('('.$authors_sql.') as book_authors','book_authors.book_id = books.book_id','left')
			->join('book_copies','books.book_id = book_copies.book_id and no_of_copies > 0','left')
			->join('library_branch','book_copies.branch_id = library_branch.branch_id','left')
			->where('!isnull(book_copies.branch_id)')
			->get();
		return $sql->result_array();
		
	}
	/**
	* Prepare Conditions combination.
	* Shared with Bookslans class
	* @param
	* @return Void
	**/
	public function prepareConditionCombination($rawConditionArray, $conditions=array()){
		
		$data = $this->input->get();
		$conditions_count = count($conditions);
		$rawConditionsCount = count($rawConditionArray);
		$condition_loop = 0;
		$group_started = '';
		foreach($rawConditionArray as $key=>$field_table_relation_array){
			foreach($field_table_relation_array as $field=>$table_relation){				
				if($data[$field]){
					if(is_array($table_relation)){
						foreach($table_relation as $col_key=>$table_col){
							if($col_key == 0){
								if(!$data[$conditions[$key-1]])
									$this->db->or_group_start();	
								else
									$this->db->group_start();
								$this->db->like($table_col,$data[$field]);
							}
							else
								$this->db->or_like($table_col,$data[$field]);
							
						}
						$this->db->group_end();	
					}else{
						if($condition_loop==0){
							$this->db->group_start();
							$this->db->group_start(); $group_started = 2;
						}
						if(!$condition_loop || ($data[$conditions[$key-1]] && $key>0)){				
							$this->db->like($table_relation,$data[$field]);
						}
						else if(!$data[$conditions[$key-1]]){
							$this->db->or_like($table_relation,$data[$field]);
						}
					}
					$condition_loop++;
				}
			}
			if(($key == $conditions_count-1 || $key == $rawConditionsCount-1) && $group_started){
				$this->db->group_end();$group_started--;
			}
			if($group_started && $key==count($rawConditionArray)-1){
				$this->db->group_end();$group_started--;
			}
		}				
	}
}
