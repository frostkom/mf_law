<?php

$obj_law = new mf_law();
$obj_export = new mf_law_export();

echo "<div class='wrap'>
	<h2>".__("Export", 'lang_law')."</h2>"
	.$obj_export->get_form()
."</div>";