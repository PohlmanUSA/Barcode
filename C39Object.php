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
  
/* For the C39 Only */
define("BCD_C39_NARROW_BAR"      ,   1);
define("BCD_C39_WIDE_BAR"        ,   2);

  /** 
   * Render for Code 39
   * Code 39 is an alphanumeric bar code that can encode decimal number, 
   * upper case alphabet and some special symbols.
   */
  class C39Object extends BarcodeObject {
    /**
     * Whether to draw the checksum character
     * 
     * @var boolean
     */
    public $drawCheckChar=false;
    /**
     * List of valid characters
     * 
     * @var string
     */
    protected $mChars;
    
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
      parent::__construct();
      $this->mChars   = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ-. $/+%';
      $this->mCharSet = array (
                "000110100"/* 0  */, "100100001"/* 1  */,
                "001100001"/* 2  */, "101100000"/* 3  */,
                "000110001"/* 4  */, "100110000"/* 5  */,
                "001110000"/* 6  */, "000100101"/* 7  */,
                "100100100"/* 8  */, "001100100"/* 9  */,
                "100001001"/* A  */, "001001001"/* B  */,
                "101001000"/* C  */, "000011001"/* D  */,
                "100011000"/* E  */, "001011000"/* F  */,
                "000001101"/* G  */, "100001100"/* H  */,
                "001001100"/* I  */, "000011100"/* J  */,
                "100000011"/* K  */, "001000011"/* L  */,
                "101000010"/* M  */, "000010011"/* N  */,
                "100010010"/* O  */, "001010010"/* P  */,
                "000000111"/* Q  */, "100000110"/* R  */,
                "001000110"/* S  */, "000010110"/* T  */,
                "110000001"/* U  */, "011000001"/* V  */,
                "111000000"/* W  */, "010010001"/* X  */,
                "110010000"/* Y  */, "011010000"/* Z  */,
                "010000101"/* -  */, "110000100"/* .  */,
                "011000100"/*    */, "010101000"/* $  */, 
                "010100010"/* /  */, "010001010"/* +  */, 
                "000101010"/* %  */
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
      for($x=0;$x<strlen($value);$x++){
        if($this->getCharIndex($value[$x])==-1){
          throw new \InvalidArgumentException('String contains invalid characters');
        }
      }
      //wrap the value in asterisks for start/stop characters
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
    protected function getSize($xres)
    {
      $len = strlen($this->value);
      if ($len == 0)  {
        throw new \RuntimeException('No value set to encode');
      }
      //All characters are the same width
      $charSize  = BCD_C39_NARROW_BAR * $xres * 6 + BCD_C39_WIDE_BAR * $xres * 3;
      return ($charSize * $len) + (BCD_C39_NARROW_BAR * $xres * ($len))+$charSize;
    }
    
    /**
     * Generates the Checksum character
     * 
     * @return string
     */
    protected function getControlChar()
    {
      $len = strlen($this->value);
      $sum = 0;
      for($x=0;$x<$len;$x++){
        $sum+= $this->GetCharIndex($this->value[$x]);
      }
      $checkIndex = $sum%43;
      return $this->mCharSet[$checkIndex];
    }
    
    /**
     * Draws a barcode character in the image
     * 
     * @param string $code One of the mCharSet codes
     * @param int $drawPos The x-axis start of the character
     * @param int $yMargin The y-axis start of the barcode
     * @param int $xres The linear resolution of the barcode
     * @param int $ysize The height of the barcode
     * 
     * @return int The end position of the character
     */
    protected function drawBarcodeChar($code,$drawPos,$yMargin,$xres,$ysize)
    {
      $narrow = BCD_C39_NARROW_BAR * $xres;
      $wide   = BCD_C39_WIDE_BAR * $xres;
      for($p=0;$p<9;$p++){//'010010100'
        $width = $code[$p]=='0'?$narrow:$wide;
        if($p%2==0){//lines
          $this->drawSingleBar($drawPos, $yMargin, $width, $ysize);
        }
        $drawPos+=$width;
      }//encoding loop
      return $drawPos+$narrow;//add a space at the end
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
      $len = strlen($this->value);
      
      $narrow = BCD_C39_NARROW_BAR * $xres;
      $wide   = BCD_C39_WIDE_BAR * $xres;
      
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
      $sPos = (int)$sPos; 
      //calculate barcode height (subtract barcode margins and text height, if any from total height)
      $ysize = $this->mHeight - BCD_DEFAULT_MAR_Y1 - BCD_DEFAULT_MAR_Y2;
      if ($this->mStyle & BCS_DRAW_TEXT){
        $ysize-= $this->getFontHeight($this->mFont);
      }
      //Draw the text
			if ($this->mStyle & BCS_DRAW_TEXT) {
			  //Previously calculated barcode start position plus the leading control character 
			  $textStart = $sPos+((BCD_C39_NARROW_BAR * $xres*6)+(BCD_C39_WIDE_BAR * $xres*3));
        $textY = $ysize + BCD_DEFAULT_MAR_Y1 + BCD_DEFAULT_TEXT_OFFSET;
			  if ($this->mStyle & BCS_STRETCH_TEXT) {
			    for ($i=0;$i<$len;$i++) {
			      //text start plus the stretch character width per printed character
			      $textPos = $textStart+($size/$len)*$i;
			      $this->DrawChar($this->mFont, $textPos,$textY, $this->value[$i]);
          }
        } 
        else {
          $text_width = $this->GetFontWidth($this->mFont) * strlen($this->value);
          $this->DrawText($this->mFont, $textStart+(($size-$text_width)/2),$textY, $this->value);
        }
      }
      //Draw the barcode
      $drawPos = $this->drawBarcodeChar('010010100', $sPos, BCD_DEFAULT_MAR_Y1, $xres, $ysize);//start *
      for($x=0;$x<$len;$x++){
        $code  = $this->mCharSet[$this->GetCharIndex($this->value[$x])];
        $drawPos = $this->drawBarcodeChar($code, $drawPos, BCD_DEFAULT_MAR_Y1, $xres, $ysize);
      }
      if($this->drawCheckChar){
        $drawPos = $this->drawBarcodeChar($this->getControlChar(), 
                                            $drawPos,BCD_DEFAULT_MAR_Y1,$xres,$ysize);
      }
      $drawPos = $this->drawBarcodeChar('010010100', $drawPos, BCD_DEFAULT_MAR_Y1, $xres, $ysize);//end *
      return true;
    }
  }
?>