<?php
namespace modules\dashboard;
class controller extends \Controller {

	function get() {
		echo $this->render('index');
	}

}
