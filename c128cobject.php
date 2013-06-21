<?php
namespace PohlmanUSA\Barcode;
/*
Barcode Render Class for PHP using the GD graphics library 
Copyright (C) 2001  Karim Mribti
  -- Written on 2001-08-03 by Sam Michaels
       to add Code 128-C support.
       swampgas@swampgas.org
								
   Version  0.0.7a  2001-08-03  
								
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
  
  /** 
   * Render for Code 128-A
   * Code 128-A is a continuous, multilevel for double digit numeric data.
   */
  class C128CObject extends C128Object {
    /**
     * List of valid characters
     * 
     * @var array
     */
    protected $mChars;

    /**
     * Initializes the barcode object
     * 
     * @return void
     */
    public function __construct()
    {
      parent::__construct();
      $this->mChars = array(
            "00", "01", "02", "03", "04", "05", "06", "07", "08", "09",
            "10", "11", "12", "13", "14", "15", "16", "17", "18", "19",
            "20", "21", "22", "23", "24", "25", "26", "27", "28", "29",
            "30", "31", "32", "33", "34", "35", "36", "37", "38", "39",
            "40", "41", "42", "43", "44", "45", "46", "47", "48", "49",
            "50", "51", "52", "53", "54", "55", "56", "57", "58", "59",
            "60", "61", "62", "63", "64", "65", "66", "67", "68", "69",
            "70", "71", "72", "73", "74", "75", "76", "77", "78", "79",
            "80", "81", "82", "83", "84", "85", "86", "87", "88", "89",
            "90", "91", "92", "93", "94", "95", "96", "97", "98", "99",
          );
    }
    
    /**
     * Sets the value to be encoded
     * 
     * @param string
     * 
     * @return void
     * 
     * @throws InvalidArgumentException If the string contains characters that can't be encoded
     */
    public function setValue($value)
    {
      if(!is_numeric($value)){
        throw new \InvalidArgumentException('C128c barcodes only accept numeric values');
      }
      if(strlen($value)%2>0){
        throw new \InvalidArgumentException('C128c barcodes expect all elements '
                                          .'to be a 0 padded two digit number');
      }
      $this->value = $value;
    }
    
    /**
     * Gets the mapping ID between the supplied character and the character set map
     * 
     * Returns -1 if the supplied character is not a valid character
     * 
     * @param string $char
     * 
     * @return int
     */
    protected function getCharIndex ($char) {
      if(($i = array_search($char, $this->mChars)) ===false){
        return -1;
      }
      return $i;
    }
    
    /**
     * Calculates the full width of the barcode
     * 
     * @param int $xres The barcode resolution size
     * 
     * @return int
     */
    protected function getSize($xres) {
      $len = strlen($this->value);
      
      if ($len == 0)  {
        throw new \RuntimeException('No value set to encode');
      }
      $ret = $checkSize = 0;
      //skip two because the code is based on a set of 0 padded two digit numbers
      for ($i=0;$i<$len;$i+=2) {//calculates the width of the encoded string
        $id = $this->GetCharIndex($this->value[$i].$this->value[$i+1]);
        $cset = $this->mCharSet[$id];
        $ret += $this->getBarSize($xres, $cset[0]);
        $ret += $this->getBarSize($xres, $cset[1]);
        $ret += $this->getBarSize($xres, $cset[2]);
        $ret += $this->getBarSize($xres, $cset[3]);
        $ret += $this->getBarSize($xres, $cset[4]);
        $ret += $this->getBarSize($xres, $cset[5]);
      }
      /* length of Check character */
      $cset = $this->GetCheckCharValue();
      for ($i=0;$i<6;$i++) {
        $checkSize += $this->getBarSize($cset[$i], $xres);
      }
      $StartSize = 2*BCD_C128_BAR_2*$xres + 3*BCD_C128_BAR_1*$xres + BCD_C128_BAR_4*$xres;
      $StopSize  = 2*BCD_C128_BAR_2*$xres + 3*BCD_C128_BAR_1*$xres + 2*BCD_C128_BAR_3*$xres;
      return $StartSize + $ret + $checkSize + $StopSize;
    }
    
    /**
     * Calculates the checksum character
     * 
     * @return string
     */
    protected function getCheckCharValue()
    {
      $len = strlen($this->value);
      $sum = 105; // 'C' type;
      $m=0;//charater position counter
      for ($i=0;$i<$len;$i+=2) {
        $m++;
        $sum +=  $this->GetCharIndex($this->value[$i].$this->value[$i+1]) * $m;
      }
      $check  = $sum % 103;
      return $this->mCharSet[$check];
    }
    
    /**
     * Adds the start control character to the barcode
     * 
     * @param int $drawPos The starting x-axis position to start the character at
     * @param int $yPos Where on the y-axis to start the line
     * @param int $ySize How tall to make the line
     * @param int $xres The barcode resolution size
     * 
     * @return int The end of the start control character
     */
    function drawStart($DrawPos, $yPos, $ySize, $xres)
    {
      return $this->drawCharCode('211232',$DrawPos, $yPos, $ySize, $xres);
    }
    
    /**
     * Fills the image with the barcode and any text
     * 
     * @param int $xres The barcode linear resolution
     * 
     * @return boolean
     */
    public function drawObject ($xres=1)
    {
      $size = $this->getSize($xres);
      if($size==0){
        throw new \RuntimeException('Failed to calculate a valid size');
      }
      if ($this->mStyle & BCS_ALIGN_CENTER){
        $sPos = floor(($this->mWidth - $size ) / 2);
      }
      elseif ($this->mStyle & BCS_ALIGN_RIGHT){
        $sPos = $this->mWidth - $size;
      }
      else{
        $sPos = 0;
      }
      
      //calculate barcode height (subtract barcode margins and text height, if any from total height)
      $ysize = $this->mHeight - BCD_DEFAULT_MAR_Y1 - BCD_DEFAULT_MAR_Y2;
      if ($this->mStyle & BCS_DRAW_TEXT){
        $ysize-= $this->GetFontHeight($this->mFont);
      }
      
      //draw the text in the image
      if ($this->mStyle & BCS_DRAW_TEXT) {
        //previously calculated barcode height plus top margin plus separation between barcode and text
        $textY = $ysize + BCD_DEFAULT_MAR_Y1 + BCD_DEFAULT_TEXT_OFFSET;
        //previously calculated start pos + start control character size
        $charStart = $sPos+(2*BCD_C128_BAR_2*$xres + 3*BCD_C128_BAR_1*$xres + BCD_C128_BAR_4*$xres);
        
        $this->drawBarcodeText($this->value,$charStart,$textY,$this->mFont,$this->mStyle&BCS_STRETCH_TEXT,$size);
      }
      
      $pos = $this->drawStart($sPos, BCD_DEFAULT_MAR_Y1, $ysize, $xres);
      for($x=0;$x<strlen($this->value);$x+=2){
        $charCode = $this->mCharSet[$this->getCharIndex($this->value[$x].$this->value[$x+1])];
        $pos = $this->drawCharCode($charCode,$pos, BCD_DEFAULT_MAR_Y1, $ysize, $xres);
      }
      $pos = $this->drawCheckChar($pos, BCD_DEFAULT_MAR_Y1 , $ysize, $xres);
      $pos = $this->drawStop($pos, BCD_DEFAULT_MAR_Y1 , $ysize, $xres);
      return true;
    }
  }