<?php
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
  
  /** 
   * Render for Code 128-A
   * Code 128-A is a continuous, multilevel and include all upper case alphanumeric characters 
   * and ASCII control characters .
   */
  class C128AObject extends C128Object {
    /**
     * List of valid characters
     * 
     * @var string
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
      $this->mChars   = ' !"#$%&\'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\\]^_';
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
      for($x=0;$x<strlen($value);$x++){
        if($this->getCharIndex($value[$x])==-1){
          throw new InvalidArgumentException('String contains invalid characters');
        }
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
      if(($i = strpos($this->mChars, $char)) ===false){
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
        throw new RuntimeException('No value set to encode');
      }
      $ret = $checkSize = 0;
      for ($i=0;$i<$len;$i++) {//calculates the width of the encoded string
        $id = $this->GetCharIndex($this->value[$i]);
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
      $sum = 103; // 'A' type;
      for ($i=0;$i<$len;$i++) {
        $sum +=  $this->GetCharIndex($this->value[$i]) * ($i+1);
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
    protected function drawStart($DrawPos, $yPos, $ySize, $xres)
    {
      return $this->drawCharCode('211412',$DrawPos, $yPos, $ySize, $xres);
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
        throw new RuntimeException('Failed to calculate a valid size');
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
		    $ysize-= $this->getFontHeight($this->mFont);
      }
			
      //draw the text in the image
      if ($this->mStyle & BCS_DRAW_TEXT) {
        //previously calculated barcode height plus top margin plus separation between barcode and text
        $textY = $ysize + BCD_DEFAULT_MAR_Y1 + BCD_DEFAULT_TEXT_OFFSET;
        //previously calculated start pos + start control character size
        $charStart = $sPos+(2*BCD_C128_BAR_2*$xres + 3*BCD_C128_BAR_1*$xres + BCD_C128_BAR_4*$xres);
        
        $this->drawBarcodeText($this->value,$charStart,$textY,$this->mFont,$this->mStyle&BCS_STRETCH_TEXT,$size);
      }
      //draw the barcode itself
      $pos = $this->drawBarcode($this->value,$sPos,BCD_DEFAULT_MAR_Y1,$ysize,$xres);
      return true;
    }
    
  }