<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2009 Peter Schuster <typo3@peschuster.de>
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
/**
 * class.tx_profileimgmap_main.php
 *
 * $Id$
 *
 * @author Peter Schuster <typo3@peschuster.de>
 */

/**
 * Generates an image with small user profile images
 * Canvas size and size of the single user images is
 * configurable by TypoScript
 *
 * @author Peter Schuster <typo3@peschuster.de>
 * @package TYPO3
 * @subpackage tx_profileimgmap
 */
class tx_profileimgmap_main {

	/**
	 * Main method of the class
	 * generates image
	 *
	 * @param	string		$content
	 * @param	array		$conf: configuration array
	 * @return	string		HTML output
	 */
	function main($content, $conf) {

		$height = (intVal($conf['height']) > 0 ? intVal($conf['height']) : 19);
		$width = (intVal($conf['width']) > 0 ? intVal($conf['width']) : 19);
		$imageWidth = (intVal($conf['singleWidth']) > 0 ? intVal($conf['singleWidth']) : 437);
		$imageHeight = (intVal($conf['singleHeight']) > 0 ? intVal($conf['singleHeight']) : 76);
		$spacing = (isset($conf['spacing']) ? intVal($conf['spacing']) : 1);
		$dbField = (isset($conf['dbField']) ? trim($conf['dbField']) : 'image');
		$imagePath = (isset($conf['imagePath']) ? trim($conf['imagePath']) : 'uploads/tx_srfeuserregister/');
		$bgColor = (isset($conf['bgColor']) ? trim($conf['bgColor']) : '#FFFFFF');

		$imagesPerColumn = floor($height / ($imageHeight+$spacing));
		$imagesPerRow = floor($width / ($imageWidth+$spacing));
		$maxNumber = ($imagesPerColumn * $imagesPerRow);

		$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows($dbField, 'fe_users', $dbField . '<>\'\'', '', 'RAND()', '0,' . ($maxNumber-10));

		if (empty($rows)) return '';

		$imgConf = array();
		$imgConf['file'] = 'GIFBUILDER';
		$imgConf['file.']['XY'] = $width . ',' . $height;
		$imgConf['file.']['format'] = 'jpg';
		$imgConf['file.']['quality'] = '80';
		$imgConf['file.']['backColor'] = $bgColor;




		for ($i = (count($rows)-1); $i < $maxNumber; $i++) {
			$rows[$i]['image'] = 'false';
		}

		shuffle($rows);

		$colNumber = 0;
		for ($i = 0; $i < $maxNumber; $i++) {
			if ($rows[$i]['image'] == 'false') continue;

			$colNumber = floor($i / $imagesPerColumn);
			$x = ($colNumber * ($imageWidth+$spacing)) + $spacing;
			$y = (($i - (floor($i / $imagesPerColumn) * $imagesPerColumn)) * ($imageHeight + $spacing)) + $spacing;
			$imgConf['file.'][$i+1] = 'IMAGE';
			$imgConf['file.'][($i+1).'.'] = self::addImage('uploads/tx_srfeuserregister/'.$rows[$i]['image'], $imageWidth, $imageHeight, $x, $y);
		}

		$cObj = t3lib_div::makeInstance('tslib_cObj');
		$myImage = $cObj->cObjGetSingle('IMAGE',$imgConf);
		return $myImage;
	}

	/**
	 * Generates configuration array for single image
	 *
	 * @param	string		$filename: filename with path
	 * @param	integer		$width: single image width
	 * @param	integer		$height: single image height
	 * @param	ineteger	$x: x position from top left of canvas to top left of image
	 * @param	integer		$y: y position from top left of canvas to top left of image
	 * @return	array		configuration array
	 */
	function addImage($filename, $width, $height, $x, $y) {
		$imgConf = array();
		if (@file_exists(t3lib_div::getFileAbsFileName($filename))) {

			$imgConf['file'] = $filename;
			$imgConf['file.']['width'] = $width . 'c';
			$imgConf['file.']['height'] = $height . 'c';
			$imgConf['offset'] = $x . ',' . $y;
		}
		return $imgConf;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/profileimgmap/class.tx_profileimgmap_main.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/profileimgmap/class.tx_profileimgmap_main.php']);
}

?>