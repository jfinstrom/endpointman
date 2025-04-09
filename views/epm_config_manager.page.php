<?php
	if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }
	
	echo load_view(__DIR__.'/epm_config/manager.barra.top.php', []);
	echo load_view(__DIR__.'/epm_config/manager.list.hide.php', []);
	echo load_view(__DIR__.'/epm_config/manager.list.all.php', []);
?>