<?php
namespace pohlmanusa\barcode;
/*
Barcode Render Class for PHP using the GD graphics library 
Copyright (C) 2001  Karim Mribti
                
   Version  0.0.7a  2001-04-01  
                
This library is free software; you can redistribute it and/or
modify it under the terms of the GNU Lesser General Public
License as published by the Free Software Foundation; either
version 2.1 of the License, or (at your option) any later version.
                                  
This library is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
Lesser General Public License for more details.
                         
You should have received a copy of the GNU Lesser General Public
License along with this library; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
                                     
Copy of GNU Lesser General Public License at: http://www.gnu.org/copyleft/lesser.txt
                           
Source code home page: http://www.mribti.com/barcode/
Contact author at: barcode@mribti.com
*/

/* For Code 128 */
define("BCD_C128_BAR_1", 1);
define("BCD_C128_BAR_2", 2);
define("BCD_C128_BAR_3", 3);
define("BCD_C128_BAR_4", 4);

      
/**
 * A parent class for all C128 family barcodes
 * 
 */
abstract class C128Object extends Barcode {
  
  /**
   * The mapping of character to barcode encoding
   * 
   * @var array
   */
  protected $mCharSet = array(
                      "212222",   /*   00 */ "222122",   /*   01 */
                      "222221",   /*   02 */ "121223",   /*   03 */
                      "121322",   /*   04 */ "131222",   /*   05 */
                      "122213",   /*   06 */ "122312",   /*   07 */
                      "132212",   /*   08 */ "221213",   /*   09 */
                      "221312",   /*   10 */ "231212",   /*   11 */
                      "112232",   /*   12 */ "122132",   /*   13 */
                      "122231",   /*   14 */ "113222",   /*   15 */
                      "123122",   /*   16 */ "123221",   /*   17 */
                      "223211",   /*   18 */ "221132",   /*   19 */
                      "221231",   /*   20 */ "213212",   /*   21 */
                      "223112",   /*   22 */ "312131",   /*   23 */
                      "311222",   /*   24 */ "321122",   /*   25 */
                      "321221",   /*   26 */ "312212",   /*   27 */
                      "322112",   /*   28 */ "322211",   /*   29 */
                      "212123",   /*   30 */ "212321",   /*   31 */
                      "232121",   /*   32 */ "111323",   /*   33 */
                      "131123",   /*   34 */ "131321",   /*   35 */
                      "112313",   /*   36 */ "132113",   /*   37 */
                      "132311",   /*   38 */ "211313",   /*   39 */
                      "231113",   /*   40 */ "231311",   /*   41 */
                      "112133",   /*   42 */ "112331",   /*   43 */
                      "132131",   /*   44 */ "113123",   /*   45 */
                      "113321",   /*   46 */ "133121",   /*   47 */
                      "313121",   /*   48 */ "211331",   /*   49 */
                      "231131",   /*   50 */ "213113",   /*   51 */
                      "213311",   /*   52 */ "213131",   /*   53 */
                      "311123",   /*   54 */ "311321",   /*   55 */
                      "331121",   /*   56 */ "312113",   /*   57 */
                      "312311",   /*   58 */ "332111",   /*   59 */
                      "314111",   /*   60 */ "221411",   /*   61 */
                      "431111",   /*   62 */ "111224",   /*   63 */
                      "111422",   /*   64 */ "121124",   /*   65 */
                      "121421",   /*   66 */ "141122",   /*   67 */
                      "141221",   /*   68 */ "112214",   /*   69 */
                      "112412",   /*   70 */ "122114",   /*   71 */
                      "122411",   /*   72 */ "142112",   /*   73 */
                      "142211",   /*   74 */ "241211",   /*   75 */
                      "221114",   /*   76 */ "413111",   /*   77 */
                      "241112",   /*   78 */ "134111",   /*   79 */
                      "111242",   /*   80 */ "121142",   /*   81 */
                      "121241",   /*   82 */ "114212",   /*   83 */
                      "124112",   /*   84 */ "124211",   /*   85 */
                      "411212",   /*   86 */ "421112",   /*   87 */
                      "421211",   /*   88 */ "212141",   /*   89 */
                      "214121",   /*   90 */ "412121",   /*   91 */
                      "111143",   /*   92 */ "111341",   /*   93 */
                      "131141",   /*   94 */ "114113",   /*   95 */
                      "114311",   /*   96 */ "411113",   /*   97 */
                      "411311",   /*   98 */ "113141",   /*   99 */
                      "114131",   /*  100 */ "311141",   /*  101 */ "411131"    /*  102 */
                      );
  
  
  /**
   * Calculates the pixel width of the bar
   * 
   * @param int $xres the resolution of the barcode
   * @param int $char The bar code to get the width of
   * 
   * @return int
   */
  protected function getBarSize ($xres, $char) {
    switch ($char){
      case '1':
        $cVal = BCD_C128_BAR_1;
        break;
      case '2':
        $cVal = BCD_C128_BAR_2;
        break;
      case '3':
        $cVal = BCD_C128_BAR_3;
        break;
      case '4':
        $cVal = BCD_C128_BAR_4;
        break;
      default:
        $cVal = 0;
    }
    return  $cVal * $xres;
  }
  
  /**
   * Builds the stop control character
   * 
   * @param int $drawPos x-index of where to start the character
   * @param int $yPos y-index of where to start the character
   * @param int $ySize Height of the barcode
   * @param int $xres Resolution of the barcode
   * 
   * @return int x-index of where the character ended
   */
  protected function drawStop($drawPos, $yPos, $ySize, $xres)
  {
    return $this->drawCharCode('2331112',$drawPos, $yPos, $ySize, $xres);
  }
  
  /**
   * Adds the check character to the barcode
   * 
   * @param int $drawPos x-index of where to start the character
   * @param int $yPos y-index of where to start the character
   * @param int $ySize Height of the barcode
   * @param int $xres The resolution of the barcode
   * 
   * @return int The x-index position of the end of the character
   */
  protected function drawCheckChar($drawPos, $yPos, $ySize, $xres)
  {
    return $this->drawCharCode($this->getCheckCharValue(),$drawPos, $yPos, $ySize, $xres);
  }
  
  /**
   * Calculates the checksum character
   * 
   * @return string
   */
  abstract protected function getCheckCharValue();
  
  /**
   * Adds a character code to the barcode
   * 
   * @param string $charCode A six digit code from mCharSet
   * @param int $drawPos x-index of where to start the character
   * @param int $yPos y-index of where to start the character
   * @param int $ySize Height of the barcode
   * @param int $xres The barcode linear resolution
   * 
   * @return int The x-index position of the end of the character
   */
  protected function drawCharCode($charCode, $drawPos,$yPos,$ySize,$xres)
  {
    
    for($x=0;$x<strlen($charCode);$x++){
      if($x%2==0){//causes the odd number positions to be bars, even to be spaces
        $this->drawSingleBar($drawPos, BCD_DEFAULT_MAR_Y1, $this->getBarSize($charCode[$x], $xres) , $ySize);
      }
      $drawPos+=$this->getBarSize($charCode[$x], $xres);
    }
    return $drawPos;
  }
  
  /**
   * Draws the barcode itself
   * 
   * @param string $text The text to convert
   * @param int $start The beginning x-axis position of the barcode
   * @param int $topMargin The beginning y-axis position of the barcode
   * @param int $height The height of the barcode
   * @param int $xres The barcode linear resolution
   * 
   * @return int the position of the end of the barcode
   */
  protected function drawBarcode($text,$start,$topMargin,$height,$xres)
  {
    $pos = $this->drawStart($start, $topMargin , $height, $xres);
    for($x=0;$x<strlen($text);$x++){
      $charCode = $this->mCharSet[$this->getCharIndex($text[$x])];
      $pos = $this->drawCharCode($charCode,$pos, $topMargin, $height, $xres);
    }
    $pos = $this->drawCheckChar($pos, $topMargin , $height, $xres);
    $pos = $this->drawStop($pos, $topMargin , $height, $xres);
    return $pos;
  }
  
  /**
   * Draws the human readable text that should accompany the barcode
   * 
   * @param string $text The text to display
   * @param int $start The x-axis start position for the text
   * @param int $textY The y-axis start position for the text
   * @param int $font An imageloadfont() font or a built in font
   * @param boolean $stretch Whether to stretch the text across the barcode
   * @param int $size The width of the barcode
   * 
   * @return void
   */
  protected function drawBarcodeText($text,$start,$textY, $font,$stretch,$size)
  {
    if ($stretch) {
      $len = strlen($text);
      for ($i=0;$i<$len;$i++) {
        //add the stretch width to the previous start position (0 if first character)
        $charPos = $start+($size/$len)*$i;
        $this->drawChar($font, $charPos,$textY, $text[$i]);
      }
    } 
    else {
      $text_width = $this->getFontWidth($font) * strlen($text);
      //barcode width minus the text width divided by two (to center)
      //this moves the text so it's centered with the barcode
      $lmargin = ($size-$text_width)/2;
      //barcode start position plus left margin
      $charPos = $start+$lmargin;
      $this->drawText($font, $charPos,$textY, $text);
    }
  }
   
}