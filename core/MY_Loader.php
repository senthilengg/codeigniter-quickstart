<?php
	/**
	* This class overrides the existing loader and add the template to the existing object.
	* MY_ is the static prefix and Class filename we try to override is the suffix.
	* MY_ can be changed but potentially it will break other existing modules if any.
	**/
	class MY_Loader extends CI_Loader {
		public function template($template_name, $vars = array(), $return = FALSE)
		{
			if($return):
			$content  = $this->view('library/header', $vars, $return);
			$content .= $this->view($template_name, $vars, $return);
			$content .= $this->view('library/footer', $vars, $return);

			return $content;
		else:
			$this->view('library/header', $vars);
			$this->view($template_name, $vars);
			$this->view('library/footer', $vars);
		endif;
		}
	}