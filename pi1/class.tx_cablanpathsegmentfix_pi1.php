<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2012 Martin-Pierre Frenette <typo3@cablan.net>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
require_once(PATH_tslib.'class.tslib_pibase.php');


/**
 * Plugin 'real url pathsegment fix' for the 'cablan_path_segment_fix' extension.
 *
 * This extension repairs the path segements for many languages which TYPO3 is unable
 * to properly process.
 *
 * This was discovered only in 2012 when a client began using realurl with non latin language!
 *
 *
 * @author	Martin-Pierre Frenette <typo3@cablan.net>
 * @package	TYPO3
 * @subpackage	tx_cablanpathsegmentfix
 */
class tx_cablanpathsegmentfix_pi1 extends tslib_pibase {
	var $prefixId      = 'tx_cablanpathsegmentfix_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_cablanpathsegmentfix_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'cablan_path_segment_fix';	// The extension key.
	var $pi_checkCHash = true;
	


	function getRecord($table, $uid, $enableFields = 1){
        $where = ' uid=' . intval($uid);
        if ($enableFields) {
            $where .= $this->cObj->enableFields($table);
        }

        $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', $table, $where);
        return @$GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);
    }
		
	/**
	 * The main method of the PlugIn. It scan the path segment for paths
	 * which are empty because they failed to generate!
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content, $conf)	{

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('sys_language.uid,sys_language.flag, static_languages.lg_iso_2', 'sys_language,static_languages', 'static_lang_isocode=static_languages.uid');

		$languages_to_fix = $this->GetNonLatinLanguageList();

		while ( $row=  $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){

			if (in_array($row['lg_iso_2'], $languages_to_fix) ){
				$langs[] =$row['uid'];
			}
		}

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*',  'pages_language_overlay', 
			'length(tx_realurl_pathsegment) =0 AND sys_language_uid IN ('.implode(',',$langs). ')');

		while ( $row=  $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
			$page = $this->getRecord('pages',$row['pid'] );
			$values = array();
			if ( strlen($page['tx_realurl_pathsegment']) > 0){
				$values['tx_realurl_pathsegment'] = $page['tx_realurl_pathsegment'];
			}
			else{
				$values['tx_realurl_pathsegment'] = str_replace(' ', '-', $page['title']);	
			}

			$GLOBALS['TYPO3_DB']->exec_UPDATEquery(  'pages_language_overlay', 
				'uid='. $row['uid'], $values);			
		}
	}

	function GetNonLatinLanguageList(){

		return array( 
			'ZH', // chinese
			'AR', // arabic,
			'MY', // burmese
			'HE', // Hebrew
			'HI', // Hindi
			'JA', // Japanese
			'KO', // Korean
			'FA', // Persian
			'PA', // Punjabi
			'RU', // Russian
			'TR', // turkish
			'VI', // vietnamese

			);
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cablan_path_segment_fix/pi1/class.tx_cablanpathsegmentfix_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cablan_path_segment_fix/pi1/class.tx_cablanpathsegmentfix_pi1.php']);
}

?>