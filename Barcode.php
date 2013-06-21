<?php
namespace PohlmanUSA\Barcode;
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
 * Style Definitions
 */
/**
 * Generate a border
 */
define("BCS_BORDER"	    	,    1);
/**
 * Transparent background
 */
define("BCS_TRANSPARENT"    ,    2);
/**
 * Align barcode in the center of the generated image
 */
define("BCS_ALIGN_CENTER"   ,    4);
/**
 * Align barcode at the left of the generated image
 */
define("BCS_ALIGN_LEFT"     ,    8);
/**
 * Align barcode at the left of the generated image
 */
define("BCS_ALIGN_RIGHT"    ,   16);
/**
 * Create image as a JPEG
 */
define("BCS_IMAGE_JPEG"     ,   32);
/**
 * Create image as a PNG
 */
define("BCS_IMAGE_PNG"      ,   64);
/**
 * Draw the human readable text
 */
define("BCS_DRAW_TEXT"      ,  128);
/**
 * Stretch the text the width of the barcode
 */
define("BCS_STRETCH_TEXT"   ,  256);

/**
 * Default values 
 */
/**
 * Margin on the top of the barcode in pixels
 */
define("BCD_DEFAULT_MAR_Y1"          ,  10);
/**
 * Margin on the bottom of the barcode in pixels
 */
define("BCD_DEFAULT_MAR_Y2"          ,  10);
/**
 * How much to offset the barcode from the human readable text in pixels
 */
define("BCD_DEFAULT_TEXT_OFFSET"     ,   2);

/**
 * A parent class for all barcodes that defines basic ways of interacting with barcodes
 */
abstract class BarcodeObject {
  /**
   * The barcode width in pixels
   * 
   * @var int
   */  
  protected $mWidth = 460;
  /**
   * The barcode height in pixels
   * 
   * @var int
   */
  protected $mHeight = 120;
  /**
   * The set barcode styles
   * 
   * Defaults to BCS_BORDER | BCS_ALIGN_CENTER | BCS_IMAGE_PNG | BCS_DRAW_TEXT
   * 
   * @var int
   */
  protected $mStyle = 197;
  /**
   * The barcode background color
   * 
   * A color identifier created with imagecolorallocate()
   * 
   * @var int
   */
  protected $mBgcolor=null;
  /**
   * The barcode foreground color
   * 
   * A color identifier created with imagecolorallocate()
   * 
   * @var int
   */
  protected $mBrush=null;
  /**
   * The image object being worked on
   * 
   * @var Resource
   */
	protected $mImg=null;
  /**
   * The font for the human readable text
   * 
   * Can be 1, 2, 3, 4, 5 for built-in fonts in latin2 encoding (where higher numbers 
   * corresponding to larger fonts) or any of your own font identifiers 
   * registered with imageloadfont(). 
   * 
   * @var int
   */
	protected $mFont=5;
  /**
   * The value to be encoded in the barcode
   * 
   * @var string
   */
  protected $value = '';
	
  /**
   * Initializes the barcode object
   * 
   * @return void
   */
	public function __construct()  
	{
	}
  
  /**
   * Cleans up
   * 
   * @return void
   */
  public function __destruct()
  {
    if(!is_null($this->mImg)){
      $this->destroyObject();
    }
  }
  
  /**
   * Sets up the image
   * 
   * @return void
   */
  protected function _prepareImage()
  {
    $this->mImg = ImageCreate($this->mWidth, $this->mHeight);
    if (!($this->mStyle & BCS_TRANSPARENT) && !is_null($this->mBgcolor)) {
      imagefill($this->mImg, $this->mWidth, $this->mHeight, $this->mBgcolor); 
    }
  }
  
  /**
   * Parses a hex number into its constituent RGB color values
   * 
   * Returns an array formatted:
   * [0]=>red value
   * [1]=>green value
   * [2]=>blue value
   * 
   * @param int $colorHex
   * 
   * @return array[int]int
   */
  private function _colorParse($colorHex)
  {
    $red = ($colorHex & 0xFF0000) >> 16;
    $green = ($colorHex & 0x00FF00) >> 8 ;
    $blue = $colorHex & 0x0000FF;
    return array($red,$green,$blue);
  }
  
  /**
   * Sets the background color for the image
   * 
   * @param int $colorHex
   * 
   * @return void
   */
  public function setBackgroundColor($colorHex)
  {
    list($red,$green,$blue) = $this->_colorParse($colorHex);
    //the image needs to be started in order to generate the color identifier
    if(is_null($this->mImg)){
      $this->_prepareImage();
    }
    $this->mBgcolor = imagecolorallocate($this->mImg,$red, $green, $blue);
    imagefill($this->mImg, $this->mWidth, $this->mHeight, $this->mBgcolor);
  }
	
  /**
   * Sets the foreground color for the image
   * 
   * @param int $colorHex
   * 
   * @return void
   */
  public function setForegroundColor($colorHex)
  {
    list($red,$green,$blue) = $this->_colorParse($colorHex);
    //the image needs to be started in order to generate the color identifier
    if(is_null($this->mImg)){
      $this->_prepareImage();
    }
    $this->mBrush = imagecolorallocate($this->mImg,$red, $green, $blue);
  }
  
  public function getForeground()
  {
    if(is_null($this->mBrush)){
      $this->setForegroundColor(0x000000);
    }
    return $this->mBrush;
  }
  
  /**
   * Sets the value to be encoded
   * 
   * @param string $value
   * 
   * @return void
   */
  abstract public function setValue($value);
  
  /**
   * Fills the image with the barcode and any text
   * 
   * @param int $xres The barcode linear resolution
   * 
   * @return boolean
   */
  abstract public function drawObject ($xres=1);
	
  /**
   * Draws the outer border
   * 
   * @return void
   */
	protected function drawBorder () 
	{
	  imagerectangle($this->mImg, 0, 0, $this->mWidth-1, $this->mHeight-1, $this->getForeground());
	}
	
  /**
   * Draws a character on the image
   * 
   * @param int $font Can be 1, 2, 3, 4, 5 or font identifier from imageloadfont()
   * @param int $xPos Where on the x-axis to place the character
   * @param int $yPos Where on the y-axis to place the character
   * @param string $char The character to add to the image
   * 
   * @return void
   */
	protected function drawChar($font, $xPos, $yPos, $char) 
	{
    $this->drawText($font,$xPos,$yPos,$char);
	}
	
  /**
   * Draws a string of text on the image
   * 
   * @param int $font Can be 1, 2, 3, 4, 5 or font identifier from imageloadfont()
   * @param int $xPos Where on the x-axis to place the character
   * @param int $yPos Where on the y-axis to place the character
   * @param string $text The text to add to the image
   * 
   * @return void
   */
	protected function drawText($font, $xPos, $yPos, $text) 
	{
	  imagestring($this->mImg,$font,$xPos,$yPos,$text,$this->getForeground());
	}   
	
  /**
   * Draws a barcode line
   * 
   * @param int $xPos Where on the x-axis to start the line
   * @param int $yPos Where on the y-axis to start the line
   * @param int $xSize How wide to make the line
   * @param int $ySize How tall to make the line
   * 
   * @return boolean
   */
	protected function drawSingleBar($xPos, $yPos, $xSize, $ySize) 
	{
	  //verify that the line will be drawn within the bounds of the image
	  if ($xPos>=0 && ($xPos+$xSize)<=$this->mWidth &&
	      $yPos>=0 && ($yPos+$ySize)<=$this->mHeight) {
      for ($i=0;$i<$xSize;$i++) {
        imageline($this->mImg, $xPos+$i, $yPos, $xPos+$i, $yPos+$ySize, $this->getForeground());
      }
      return true;
    }
    return false;
  }					  
	
  /**
   * Calculates the height of the supplied font
   * 
   * @param int $font Can be 1, 2, 3, 4, 5 or font identifier from imageloadfont()
   * 
   * @return int
   */
	protected function getFontHeight($font) 
	{
	  return imagefontheight($font);
	}							   
								   
  /**
   * Calculates the width of the supplied font
   * 
   * @param int $font Can be 1, 2, 3, 4, 5 or font identifier from imageloadfont()
   * 
   * @return int
   */
	protected function getFontWidth($font)  
	{
	 return imagefontwidth($font);
	}					  
						  
  /**
   * Sets the text font
   * 
   * @param int $font Can be 1, 2, 3, 4, 5 or font identifier from imageloadfont()
   * 
   * @return int
   */
	public function setFont($font) 
	{
	 $this->mFont = $font;
	}					  
	
  /**
   * Sets the barcode styles
   * 
   * @param int $style Any combination of the style constants
   * 
   * @return void
   */
  public function setStyle ($Style) 
  {
    $this->mStyle = $Style;
	}
  
  /**
   * Generates and returns the image resource so that it can be used in other applications
   * 
   * @return Resource
   */
  public function getRawImage()
  {
    if(is_null($this->mImg)){
      $this->_prepareImage();
      $this->drawObject();
    }
    return $this->mImg;
  }
  
  /**
   * Generates the image
   * 
   * Returns the data raw image data as a string or saves the image to a file
   * 
   * @param string $filepath If null, the function will return the data directly
   * 
   * @return void|string
   */
	public function flushObject ($filepath = null) {
	  if(is_null($this->mImg)){
	    $this->_prepareImage();
      $this->drawObject();
    }
	  if (($this->mStyle & BCS_BORDER)) {
	    $this->drawBorder();
    }
    $imgGenerator = '';
    if ($this->mStyle & BCS_IMAGE_PNG) {
      $imgGenerator = 'imagepng';
    }
    else if ($this->mStyle & BCS_IMAGE_JPEG) {
      $imgGenerator = 'imagejpeg';
    }
    
    if(is_null($filepath)){
      ob_start();
      $imgGenerator($this->mImg);
      $img = ob_get_contents();
      ob_end_clean();
      return $img;
    }
    else{
      if(file_exists($filepath) || !is_writable(dirname($filepath))){
        throw new RuntimeException('Cannot write to this image file. '.$filepath);
      }
      $imgGenerator($this->mImg,$filepath);
    }
  }
  
  public function setHeight($height)
  {
    if(!is_numeric($height) || $height<=0){
      throw new \InvalidArgumentException('Height must be a positive integer');
    }
    $this->mHeight = $height;
  }
  
  public function setWidth($width)
  {
    if(!is_numeric($width) || $width<=0){
      throw new \InvalidArgumentException('Width must be a positive integer');
    }
    $this->mWidth = $width;
  }
  
  /**
   * Cleans up the image 
   * 
   * @return void
   */
  function destroyObject () {
    imagedestroy($this->mImg);
    $this->mImg = null;
  }

}