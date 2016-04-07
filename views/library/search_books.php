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
				<b>Search Books</b>
				<div>
					<i>(Search Condition Grouping : (Part of ISBN OR/AND Part of TITLE) AND/OR (Part of author name))</i>
				</div>
			</p>
			<?php 
				$attributes = array('id' => 'searchbooks', 'method' => 'get');
				echo form_open(base_url('library/books/search'), $attributes);
			?>
				<div class="pad10">
					<span>ISBN :</span>
					<span>
						<?php echo form_input('isbn', isset($post_data['isbn'])?$post_data['isbn']:''); ?>
					</span>
					<div class="pad10">
						<?php
						echo form_dropdown('isbn_condition', array('0'=>'OR','1'=>'AND'),
							isset($post_data['isbn_condition'])?$post_data['isbn_condition']:'');
						?>
					</div>
				</div>
				<div class="pad10">
						<span>Title :</span>
						<span>
							<?php				
								echo form_input('title', isset($post_data['title'])?$post_data['title']:'');
							?>
						</span>
					<div class="pad10">
						<?php
						echo form_dropdown('title_condition', array('0'=>'OR','1'=>'AND'),
							isset($post_data['title_condition'])?$post_data['title_condition']:'');
						?>	
					</div>
				</div>
				<div class="pad10">
					<span>Author :</span>
					<span>
						<?php echo form_input('author', isset($post_data['author'])?$post_data['author']:''); ?>
					</span>
				</div>
				<div class="pad10">
					<span></span>
					<span>
						<?php echo form_submit('mysubmit', 'Search Book!'); ?>
					</span>
				</div>
				<?php echo form_close(); ?>
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
					$this->table->set_heading('', 'ISBN', 'Branch Name', 'Author', 'Title', 'Copies Available');
					foreach($result as $key=>$value){
						$this->table->add_row(
							form_radio('isbn', $value['book_id'].'-'.$value['branch_id']),
							$value['book_id'], 
							$value['branch_name'], 
							$value['authors'], 
							$value['title'], 
							$value['no_of_copies']
						);
					}
					$attributes = array('id' => 'loans');
					echo form_open('library/books/checkout', $attributes);
					?>
					<div>
						<?php echo $this->table->generate(); ?>
					</div>

					<div class="pad10">
					<?php
						echo form_input('card_no', isset($post_data['card_no'])?$post_data['card_no']:'');
						echo form_button('mysubmit', 'Check out!');
					?>
					</div>
					<?php echo form_close();
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
				url:'checkout',
				dataType:'json',
				success:function(result,status,xhr){
					if(result.error){
						jQuery('#message').html(result.error);return;
					}
					switch(result.result){
						case 'no_books':
							jQuery('#message').html('Book currently not available in the branch.');
							break;
						case 'invalid_card':
							jQuery('#message').html('Please check the card number.');
							break;
						case 'max_loan':
							jQuery('#message').html('Card holder exceeded the maximum borrow limit.');
							break;
						default:
							jQuery('#message').html('Checked in successfully!');
					}
				},error: function(r,e){
					console.log(r.responseText);
				}
			});
		});
	});
//]]>
</script>
