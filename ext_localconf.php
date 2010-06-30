<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$GLOBALS["TYPO3_CONF_VARS"][TYPO3_MODE]['XCLASS']['ext/impexp/class.tx_impexp.php']=t3lib_extMgm::extPath("greenhouse")."class.ux_tx_impexp.php";
$GLOBALS["TYPO3_CONF_VARS"][TYPO3_MODE]['XCLASS']['ext/impexp/app/index.php'] = t3lib_extMgm::extPath("greenhouse")."class.ux_sc_mod_tools_log_index.php";

?>