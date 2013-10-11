<?php
$extensionPath = t3lib_extMgm::extPath('easyvote_importer');
return array(
	'PHPExcel' => $extensionPath . 'Classes/Utility/PHPExcel.php',
	'PHPExcel_IOFactory' => $extensionPath . 'Classes/Utility/PHPExcel/IOFactory.php',
);
?>