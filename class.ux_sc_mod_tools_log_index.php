<?php

/**
 * Main script class for the Import / Export facility
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @package TYPO3
 * @subpackage tx_impexp
 */
class ux_SC_mod_tools_log_index extends SC_mod_tools_log_index {
	/**
	 * Create advanced options form
	 *
	 * @param	array		Form configurat data
	 * @param	array		Table row accumulation variable. This is filled with table rows.
	 * @return	void		Sets content in $this->content
	 */
	function makeAdvancedOptionsForm($inData, &$row)	{
		global $LANG;

			// Soft references
		$row[] = '
			<tr class="tableheader bgColor5">
				<td colspan="2">'.$LANG->getLL('makeadvanc_softReferences',1).'</td>
			</tr>';
		$row[] = '
				<tr class="bgColor4">
					<td><label for="checkExcludeHTMLfileResources"><strong>'.$LANG->getLL('makeadvanc_excludeHtmlCssFile',1).'</strong></label>'.t3lib_BEfunc::cshItem('xMOD_tx_impexp', 'htmlCssResources', $GLOBALS['BACK_PATH'],'').'</td>
					<td><input type="checkbox" name="tx_impexp[excludeHTMLfileResources]" id="checkExcludeHTMLfileResources" value="1"'.($inData['excludeHTMLfileResources'] ? ' checked="checked"' : '').' /></td>
				</tr>';


			// Extensions
		$row[] = '
			<tr class="tableheader bgColor5">
				<td colspan="2">'.$LANG->getLL('makeadvanc_extensionDependencies',1).'</td>
			</tr>';
		$row[] = '
				<tr class="bgColor4">
					<td><strong>'.$LANG->getLL('makeadvanc_selectExtensionsThatThe',1).'</strong>'.t3lib_BEfunc::cshItem('xMOD_tx_impexp', 'extensionDependencies', $GLOBALS['BACK_PATH'],'').'</td>
					<td>'.$this->extensionSelector('tx_impexp[extension_dep]',$inData['extension_dep']).'</td>
				</tr>';
                
        $row[] = '
				<tr class="bgColor4">
					<td><strong>Files to include</strong></td>
					<td><textarea name="tx_impexp[files]"'.$this->doc->formWidth(30,5).'>'.t3lib_div::formatForTextarea($inData['files']).'</textarea></td>
                </tr>';

			// Add buttons:
		$row[] = '
				<tr class="bgColor4">
					<td>&nbsp;</td>
					<td>
						<input type="submit" value="'.$LANG->getLL('makesavefo_update',1).'" />
						<input type="hidden" name="tx_impexp[download_export_name]" value="'.substr($nameSuggestion,0,30).'" />
					</td>
				</tr>';
	}
}