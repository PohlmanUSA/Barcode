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
  
/* For the I25 Only */
define("BCD_I25_NARROW_BAR" , 1);
define("BCD_I25_WIDE_BAR"   , 2);

  /** 
   * Render for Interleaved 2 of 5
   * Interleaved 2 of 5 is a numeric only bar code with a optional check number.
   */
  class  I25Object extends Barcode {
    /**
     * The mapping of character to barcode encoding
     * 
     * @var array
     */
    protected $mCharSet;
    
    /**
     * Initializes the barcode object
     * 
     * @return void
     */
    public function __construct()
    {
      $this->mCharSet = array(
                        "00110"/* 0 */, "10001"/* 1 */,
                        "01001"/* 2 */, "11000"/* 3 */,
                        "00101"/* 4 */, "10100"/* 5 */,
                        "01100"/* 6 */, "00011"/* 7 */,
                        "10010"/* 8 */, "01010"/* 9 */
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
        throw new InvalidArgumentException('Interleaved 2 of 5 barcodes only accept numeric values');
      }
      if(strlen($value)%2>0){
        throw new InvalidArgumentException('Interleaved 2 of 5 barcodes must be an even length');
      }
      $this->value = $value;
    }
    
    /**
     * Calculates the full width of the barcode
     * 
     * @param int $xres The barcode resolution size
     * 
     * @return int
     */
    protected function getSize($xres)
    {
      $len = strlen($this->value);
      if ($len == 0)  {
        throw new \RuntimeException('No value set to encode');
      }
      
      $startSize = BCD_I25_NARROW_BAR * 4  * $xres;
      $stopSize  = BCD_I25_WIDE_BAR * $xres + 2 * BCD_I25_NARROW_BAR * $xres;
      $size = BCD_I25_NARROW_BAR * 4  * $xres;//start control character
      for($x=0;$x<$len;$x+=2) {
        $code1 = $this->mCharSet[$this->value[$x]];
        $code2 = $this->mCharSet[$this->value[$x+1]];
        for ($i=0;$i<5;$i++) {
          $width1 = (($code1[$i]==0)?BCD_I25_NARROW_BAR:BCD_I25_WIDE_BAR) * $xres;
          $width2 = (($code2[$i]==0)?BCD_I25_NARROW_BAR:BCD_I25_WIDE_BAR) * $xres;
          $size += ($width1 + $width2);
        }
      }
      $size+= BCD_I25_WIDE_BAR * $xres + 2 * BCD_I25_NARROW_BAR * $xres;//end control character
      return $size;
    }
    
    /**
     * Adds the start control character to the barcode
     * 
     * The start code is 0000
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
      $this->drawSingleBar($DrawPos, $yPos, BCD_I25_NARROW_BAR  * $xres , $ySize);//0
      $DrawPos += BCD_I25_NARROW_BAR  * $xres;
      $DrawPos += BCD_I25_NARROW_BAR  * $xres;//0
      $this->DrawSingleBar($DrawPos, $yPos, BCD_I25_NARROW_BAR  * $xres , $ySize);//0
      $DrawPos += BCD_I25_NARROW_BAR  * $xres;
  	  $DrawPos += BCD_I25_NARROW_BAR  * $xres;//0
  	  return $DrawPos;
    }
    
    /**
     * Builds the stop control character
     * 
     * Stop code is "100"
     * 
     * @param int $drawPos x-index of where to start the character
     * @param int $yPos y-index of where to start the character
     * @param int $ySize Height of the barcode
     * @param int $xres Resolution of the barcode
     * 
     * @return int x-index of where the character ended
     */
    function drawStop($DrawPos, $yPos, $ySize, $xres)
    {
      $this->DrawSingleBar($DrawPos, $yPos, BCD_I25_WIDE_BAR * $xres , $ySize);//1
      $DrawPos += BCD_I25_WIDE_BAR  * $xres;
      $DrawPos += BCD_I25_NARROW_BAR  * $xres;//0
      $this->DrawSingleBar($DrawPos, $yPos, BCD_I25_NARROW_BAR  * $xres , $ySize);//0
      $DrawPos += BCD_I25_NARROW_BAR  * $xres;
      return $DrawPos;
    }
    
    /**
     * Fills the image with the barcode and any text
     * 
     * @param int $xres The barcode linear resolution
     * 
     * @return boolean
     */
    function drawObject ($xres=1)
    {
      $len = strlen($this->value);
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
      //Draw the text
      if ($this->mStyle & BCS_DRAW_TEXT) {
        //Previously calculated barcode start position plus the leading control character 
        $textStart = $sPos+BCD_I25_NARROW_BAR*4*$xres;
        $textY = $ysize + BCD_DEFAULT_MAR_Y1 + BCD_DEFAULT_TEXT_OFFSET;
        if ($this->mStyle & BCS_STRETCH_TEXT) {
          for ($i=0;$i<$len;$i++) {
            //text start plus the stretch character width per printed character
            $textPos = $textStart+($size/$len)*$i;
            $this->drawChar($this->mFont, $textPos,$textY, $this->value[$i]);
          }
        } 
        else {
          $text_width = $this->GetFontWidth($this->mFont) * strlen($this->value);
          $this->DrawText($this->mFont, $textStart+(($size-$text_width)/2),$textY, $this->value);
        }
      }
      //Draw the barcode
      $sPos = $this->drawStart($sPos, BCD_DEFAULT_MAR_Y1, $ysize, $xres); 
      for($x=0;$x<$len;$x+=2){
        $code1 = $this->mCharSet[$this->value[$x]];
        $code2 = $this->mCharSet[$this->value[$x+1]];
        for ($i=0;$i<5;$i++) {
          $type1 = (($code1[$i]==0)?BCD_I25_NARROW_BAR:BCD_I25_WIDE_BAR) * $xres;
          $type2 = (($code2[$i]==0)?BCD_I25_NARROW_BAR:BCD_I25_WIDE_BAR) * $xres;
          $this->drawSingleBar($sPos, BCD_DEFAULT_MAR_Y1, $type1 , $ysize);
          $sPos += ($type1 + $type2);
        }
      }
      $sPos =  $this->drawStop($sPos, BCD_DEFAULT_MAR_Y1, $ysize, $xres);
      return true;
    }
  }