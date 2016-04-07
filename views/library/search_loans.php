<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
	<div id="body">
		<div class="red">
			<?php 
				echo validation_errors();
				echo $this->session->flashdata('error_msg'); 
			?>
		</div>
		<div>
			<p>
				<b>Search Loans</b>
				<div>
					<i>(Search Condition Grouping : (Part of ISBN OR/AND Part of Card No.) AND/OR (Part of borrower name))</i>
				</div>
			</p>
			<?php 
				$attributes = array('id' => 'searchloans', 'method' => 'get');
				echo form_open(base_url('library/books/searchloans'), $attributes);
			?>
				<div class="pad10">
					<span>ISBN :</span>
					<span>
						<?php echo form_input('isbn', isset($post_data['isbn'])?$post_data['isbn']:''); ?>
					</span>
					<div class="pad10">';
						<?php 
							echo form_dropdown('isbn_condition', array('0'=>'OR','1'=>'AND'),
							isset($post_data['isbn_condition'])?$post_data['isbn_condition']:'');
						?>
					</div>
				</div>
				<div class="pad10">
					<span>Card No :</span>
					<span>
						<?php echo form_input('card_no', isset($post_data['card_no'])?$post_data['card_no']:''); ?>
				
					</span>
					<div class="pad10">
						<?php 
							echo form_dropdown('title_condition', array('0'=>'OR','1'=>'AND'),
								isset($post_data['title_condition'])?$post_data['title_condition']:'');
						?>
					</div>
				</div>
				<div class="pad10">
					<span>Borrower Name :</span>
					<span>
						<?php echo form_input('borrower', isset($post_data['borrower'])?$post_data['borrower']:''); ?>
					</span>
				</div>
				<div class="pad10">
					<span></span>
					<span>
						<?php echo form_submit('mysubmit', 'Search Loan!'); ?>
					</span>
				</div>
			<?php	echo form_close(); ?>
			<div class="fright">
				<?php	
					echo isset($pagination)?$pagination:'';
				?>
			</div>
			<div class="clearall"></div>
			<div>
			<?php		
				if(isset($result) && !empty($result)){
					$template = array(
							'table_open' => '<table border="1" borderColor="#D0D0D0" cellpadding="5" cellspacing="0">'
					);
					$this->table->set_template($template);
					$this->table->set_heading('', 'ISBN', 'Card No', 'Name', 'Branch Name', 'Due Date');
					foreach($result as $key=>$value){
						$this->table->add_row(
							form_radio('isbn', $value['id']),
							$value['book_id'], 
							$value['card_no'], 
							$value['name'], 
							$value['branch_name'], 
							$value['due_date']
						);
					}
					$attributes = array('id' => 'loans');
					echo form_open('library/books/checkin', $attributes);
					
					echo '<div>';
						echo $this->table->generate();
					echo '</div>';
					
					echo '<div class="pad10">';
						echo form_input('date_in', isset($post_data['date_in'])?$post_data['date_in']:date('Y-m-d'));
						echo form_button('mysubmit', 'Check In!');
					echo '</div>';
					echo form_close();
				}
			?>
			<span class="red" id="message"></span>
			</div>
			<div class="fright">
				<?php	
					echo isset($pagination)?$pagination:'';
				?>
				<div class="clearall"></div>
			</div>
		</div>
	</div>
	
<script type="text/javascript">
//<![CDATA[
	jQuery(document).ready(function(){
		jQuery("[type='button'][name='mysubmit']").click(function(){
			jQuery('#message').html('processing please wait....');
			jQuery.ajax({
				method:'post',
				data:jQuery('form#loans').serialize(),
				url:'checkin',
				dataType:'json',
				success:function(result,status,xhr){
					if(result.error){
						jQuery('#message').html(result.error);return;
					}else{
						jQuery('#message').html('Registered successfully!');
					}					
				},error: function(r,e){
					console.log(r.responseText);
				}
			});
		});
	});
//]]>
</script>
