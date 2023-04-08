<?php

$obj_law = new mf_law();
$obj_import = new mf_law_import();

echo "<div class='wrap'>
	<h2>".__("Import", 'lang_law')."</h2>"
	.$obj_import->do_display()
."</div>";