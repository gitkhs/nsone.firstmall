<?php
 
/*
* File: SimpleImage.php
* Author: Simon Jarvis
* Copyright: 2006 Simon Jarvis
* Date: 08/11/06
* Link: http://www.white-hat-web-design.co.uk/articles/php-image-resizing.php
*
* This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 2
* of the License, or (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details:
* http://www.gnu.org/licenses/gpl.html
*
*/
define("TL", 1);		// TOP LEFT
define("TM", 2);		// TOP MIDDLE
define("TR", 4);		// TOP RIGHT
define("ML", 8); 		// MIDDLE LEFT
define("MM", 16); 		// MIDDLE MIDDLE
define("MR", 32);		// MIDDLE RIGHT
define("BL", 64);		// BOTTOM LEFT
define("BM", 128);		// BOTTOM MIDDLE
define("BR", 256);		// BOTTOM RIGHT

define("SCALE_NUM", 1);
// define("SCALE_NUM", 0.04);

class watermarklib {
 
var $image;
	var $image_type;
	
	var $watermarkImage	= null;
	var $watermarkImage_type;
	function load($filename, $type=0) { 
		$image_info = getimagesize($filename);
		if( $type == 0 ) {
			$this->image_type = $image_info[2];
			if( $this->image_type == IMAGETYPE_JPEG ) {
				$this->image = imagecreatefromjpeg($filename);
			} elseif( $this->image_type == IMAGETYPE_GIF ) {
				$old_r = 0;
				$old_g = 0;
				$old_b = 0;
				
				$gif = imagecreatefromgif($filename);
				
				imagecolortransparent ($gif, imagecolorallocate ($gif, $old_r, $old_g, $old_b));
    
				$w = imagesx ($gif);
				$h = imagesy ($gif);
    
				$this->image = imagecreatetruecolor ($w, $h);    
				imagefill ($this->image , 0, 0, imagecolorallocate ($this->image , $old_r, $old_g, $old_b));    
				imagecopymerge ($this->image , $gif, 0, 0, 0, 0, $w, $h, 100);
					
				//return $resImage;
			} elseif( $this->image_type == IMAGETYPE_PNG ) {
				$this->image = imagecreatefrompng($filename);
			}
			//imagealphablending($this->image,true);
		}else {
			$this->watermarkImage_type	= $image_info[2];
			if( $this->watermarkImage_type == IMAGETYPE_JPEG ) {
				$this->watermarkImage		= imagecreatefromjpeg($filename);
			}else if( $this->watermarkImage_type == IMAGETYPE_GIF ) {
				$this->watermarkImage		= imagecreatefromgif($filename);
			}else if( $this->watermarkImage_type == IMAGETYPE_PNG ) {
				$this->watermarkImage		= imagecreatefrompng($filename);
			}			
			//imagealphablending($this->watermarkImage,true);
		}
   }
   
   function changeGifToPng()
   {
   
   }
   
   
   function save($filename, $image_type=IMAGETYPE_JPEG, $compression=95, $permissions=null) {
		imagesavealpha($this->image, true); 
	  if( $image_type == IMAGETYPE_JPEG ) {
		 imagejpeg($this->image,$filename,$compression);
	  } elseif( $image_type == IMAGETYPE_GIF ) {
 
		 imagegif($this->image,$filename);
	  } elseif( $image_type == IMAGETYPE_PNG ) {
 
		 imagepng($this->image,$filename);
	  }
	  if( $permissions != null) {
 
		 chmod($filename,$permissions);
	  }
   }
   function output($image_type=IMAGETYPE_JPEG) {
 
	  if( $image_type == IMAGETYPE_JPEG ) {
		 imagejpeg($this->image);
	  } elseif( $image_type == IMAGETYPE_GIF ) {
 
		 imagegif($this->image);
	  } elseif( $image_type == IMAGETYPE_PNG ) {
 
		 imagepng($this->image);
	  }
   }
   function getWidth() {
 
	  return imagesx($this->image);
   }
   function getHeight() {
 
	  return imagesy($this->image);
   }
	function getWaterMarkWidth() {
		if( $this->watermarkImage != null ) {
			return imagesx($this->watermarkImage);
		}else {
			return -1;
		}		
	}
	function getWaterMarkHeight() {
		if( $this->watermarkImage != null ) {
			return imagesy($this->watermarkImage);
		}else {
			return -1;
		}
	}
   function resizeToHeight($height) {
 
	  $ratio = $height / $this->getHeight();
	  $width = $this->getWidth() * $ratio;
	  $this->resize($width,$height);
   }
 
   function resizeToWidth($width) {
	  $ratio = $width / $this->getWidth();
	  $height = $this->getheight() * $ratio;
	  $this->resize($width,$height);
   }
 
   function scale($scale) {
	  $width = $this->getWidth() * $scale/100;
	  $height = $this->getheight() * $scale/100;
	  $this->resize($width,$height);
   }
 
   function resize($width,$height) {
	  $new_image = imagecreatetruecolor($width, $height);
	  imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
	  $this->image = $new_image;
   }      
   
   function scaleWaterMark($scale) {
	  $width = $this->getWidth() * $scale/100;
	  $height = $this->getheight() * $scale/100;
	  $this->resize($width,$height);
   }
	var $isResizeWarterMark = false;
	function resizeWaterMark($width,$height) {
		$new_image = imagecreatetruecolor($width, $height);
		
		imagealphablending($new_image, false);
		$col=imagecolorallocatealpha($new_image,255,255,255,127);
		imagefilledrectangle($new_image,0,0,$width, $height,$col);		
		imagealphablending($new_image,true);    
		//imagesavealpha($new_image, true);
		
		imagecopyresampled($new_image, $this->watermarkImage, 0, 0, 0, 0, $width, $height, $this->getWaterMarkWidth(), $this->getWaterMarkHeight());
		$this->watermarkImage = $new_image;
		
		$this->isResizeWarterMark = true;
	}      
	function addWaterMark($watermarkPath, $position = BR, $offset = 0)
	{
		if( $this->watermarkImage == null ) {
			$this->load($watermarkPath, 1);
		}
		
		$image_width		= $this->getWidth();
		$image_height		= $this->getHeight();
		
		$watermark_width	= $this->getWaterMarkWidth();
		$watermark_height	= $this->getWaterMarkHeight();
		
		if( $this->isResizeWarterMark == false && SCALE_NUM != 1) {
			$temp_height = $image_height * SCALE_NUM;
					
			$percent 	= $temp_height / $watermark_height;
			$watermark_width = $watermark_width * $percent;
			$watermark_height  = $temp_height;
			$this->resizeWaterMark($watermark_width, $watermark_height);
		
		}
		
		
		$dest_x		= 0;
		$dest_y		= 0;
		if( $position & TL ) {
			$dest_x		= $offset;
			$dest_y		= $offset;
			
			imagecopyresampled($this->image, $this->watermarkImage,$dest_x,$dest_y,0,0,$watermark_width,$watermark_height,$watermark_width,$watermark_height); 
		}
		
		//top middle 
		if( $position & TM ) {
			$dest_x = ( ( $image_width - $watermark_width ) / 2 ); 
			$dest_y = $offset; 
			
			imagecopyresampled($this->image, $this->watermarkImage,$dest_x,$dest_y,0,0,$watermark_width,$watermark_height,$watermark_width,$watermark_height); 
		}
		
		//top right 
		if( $position & TR ) 
		{ 
			$dest_x = ($image_width - $watermark_width) - $offset; 
			$dest_y = $offset; 
			
			imagecopyresampled($this->image, $this->watermarkImage,$dest_x,$dest_y,0,0,$watermark_width,$watermark_height,$watermark_width,$watermark_height); 
		} 
		
		//middle left 
		if( $position & ML ) 
		{
			$dest_x = $offset; 
			$dest_y = (( $image_height / 2 ) - ( $watermark_height / 2 )); 
			
			imagecopyresampled($this->image, $this->watermarkImage,$dest_x,$dest_y,0,0,$watermark_width,$watermark_height,$watermark_width,$watermark_height); 
		}
		
		// middle middle
		if( $position & MM ) 
		{ 
			$dest_x = (($image_width / 2) - ( $watermark_width / 2 )); 
			$dest_y = (($image_height / 2 ) - ( $watermark_height / 2)); 
		   
			imagecopyresampled($this->image, $this->watermarkImage,$dest_x,$dest_y,0,0,$watermark_width,$watermark_height,$watermark_width,$watermark_height); 
		} 
		
		//middle right 
		if( $position & MR ) 
		{ 
			$dest_x = ($image_width - $watermark_width) - $offset; 
			$dest_y = (( $image_height / 2 ) - ( $watermark_height / 2 )); 
			
			imagecopyresampled($this->image, $this->watermarkImage,$dest_x,$dest_y,0,0,$watermark_width,$watermark_height,$watermark_width,$watermark_height); 
		} 
		
		//bottom left    
		if( $position & BL ) 
		{ 
			$dest_x = $offset; 
			$dest_y = ($image_height - $watermark_height) - $offset; 
			
			imagecopyresampled($this->image, $this->watermarkImage,$dest_x,$dest_y,0,0,$watermark_width,$watermark_height,$watermark_width,$watermark_height); 
		}
		
		//bottom middle    
		if( $position & BM ) 
		{ 
			$dest_x = (( $image_width - $watermark_width ) / 2 ); 
			$dest_y = ($image_height - $watermark_height) - $offset; 
			
			imagecopyresampled($this->image, $this->watermarkImage,$dest_x,$dest_y,0,0,$watermark_width,$watermark_height,$watermark_width,$watermark_height); 
		} 
		
		//bottom right 
		if( $position & BR ) 
		{ 
			$dest_x = ($image_width - $watermark_width) - $offset; 
			$dest_y = ($image_height - $watermark_height) -$offset; 
			
			imagecopyresampled($this->image, $this->watermarkImage,$dest_x,$dest_y,0,0,$watermark_width,$watermark_height,$watermark_width,$watermark_height); 
		} 
	}
	
	function addPatternWaterMark($watermarkPath, $offset = 0)
	{
		if( $this->watermarkImage == null ) {
			$this->load($watermarkPath, 1);
		}
		
		
		$image_width		= $this->getWidth();
		$image_height		= $this->getHeight();
		$watermark_width	= $this->getWaterMarkWidth();
		$watermark_height	= $this->getWaterMarkHeight();
		
		/*if( $this->isResizeWarterMark == false && SCALE_NUM != 1) {
			$temp_height = $image_height * SCALE_NUM;
					
			$percent 	= $temp_height / $watermark_height;
			$watermark_width = $watermark_width * $percent;
			$watermark_height  = $temp_height;
			$this->resizeWaterMark($watermark_width, $watermark_height);
		
		}*/
		
		$new_width 			= $image_width + $image_height;// + image_height;//$image_width/2;
		$new_height 		= $image_height + $image_width;//$image_height + $image_height/2;
		// 워터 마크 이미지 생성..
		/*if( $new_width > $new_height ) {
			$new_height = $new_width;
		}else {
			$new_width = $new_height;
		}*/		
		$largeWaterMark = imagecreatetruecolor($new_width, $new_height); 
		
		// make $base_image transparent
		imagealphablending($largeWaterMark, false);
		$col=imagecolorallocatealpha($largeWaterMark,255,255,255,127);
		imagefilledrectangle($largeWaterMark,0,0,$new_width, $new_height,$col);		
		imagealphablending($largeWaterMark,true);    
		imagesavealpha($largeWaterMark, true);
		
		$offset_x = $watermark_width * 0.3;
		$offset_y = $watermark_height * 5;
		
		// drawing center
		
		$center_y = ($new_height)/2 - $watermark_height/2;// * 2;//$offset;	
		$chk = true;
		$x = 0;
		$y = $center_y;	
		
		$x = 0;
		$chk = true;
		while($chk) {
			imagecopyresampled($largeWaterMark, $this->watermarkImage, $x, $y, 0, 0,$watermark_width,$watermark_height,$watermark_width,$watermark_height); 			
			$x = $x + $watermark_width + $offset_x;
			
			if( $x > $new_width ) {
				$chk = false;
			}
		}
		
		// center -> top		
		for( $y = ($center_y - $watermark_height * 10); $y > 0; $y -= $watermark_height * 10 ) {
			$x = 0;
			$chk = true;
			while($chk) {
				imagecopyresampled($largeWaterMark, $this->watermarkImage, $x, $y, 0, 0,$watermark_width,$watermark_height,$watermark_width,$watermark_height); 			
				$x = $x + $watermark_width + $offset_x;
				
				if( $x > $new_width ) {
					$chk = false;
				}
			}
		}
		
		// center -> bottom
		for( $y = ($center_y + $watermark_height * 10); $y < ($new_height); $y += $watermark_height * 10 ) {
			$x = 0;
			$chk = true;
			while($chk) {
				imagecopyresampled($largeWaterMark, $this->watermarkImage, $x, $y, 0, 0,$watermark_width,$watermark_height,$watermark_width,$watermark_height); 			
				$x = $x + $watermark_width + $offset_x;
				
				if( $x > $new_width ) {
					$chk = false;
				}
			}
		}
		// center -> bottom
		/*for( $y = $watermark_height * 4; $y < ($image_height + $image_width); $y += $watermark_height * 10 ) {
			$x = 0;
			$chk = true;
			while($chk) {
				imagecopyresampled($largeWaterMark, $this->watermarkImage, $x, $y, 0, 0,$watermark_width,$watermark_height,$watermark_width,$watermark_height); 			
				$x = $x + $watermark_width + $offset_x;
				
				if( $x > $new_width ) {
					$chk = false;
				}
			}
		}*/
		
		
		// top
		/*$chk = true;		
		$x = 0;
		$y = $offset;		
		while($chk) {
			imagecopyresampled($largeWaterMark, $this->watermarkImage, $x, $y, 0, 0,$watermark_width,$watermark_height,$watermark_width,$watermark_height); 			
			$x = $x + $watermark_width + $offset_x;
			
			if( $x > $new_width ) {
				$chk = false;
			}
		}
		
		// center
		$chk = true;		
		$x = 0;
		$y = $new_height/2 - $watermark_height/2;		
		while($chk) {
			imagecopyresampled($largeWaterMark, $this->watermarkImage, $x, $y, 0, 0,$watermark_width,$watermark_height,$watermark_width,$watermark_height); 			
			$x = $x + $watermark_width + $offset_x;
			
			if( $x > $new_width ) {
				$chk = false;
			}
		}
		
		// bottom
		$chk = true;		
		$x = 0;
		$y = $new_height - $watermark_height - $offset;		
		while($chk) {
			imagecopyresampled($largeWaterMark, $this->watermarkImage, $x, $y, 0, 0,$watermark_width,$watermark_height,$watermark_width,$watermark_height); 			
			$x = $x + $watermark_width + $offset_x;
			
			if( $x > $new_width ) {
				$chk = false;
			}
		}*/
		
		
		// 회전		
		$rotate = 60;//rad2deg( atan2( imagesy($this->image), imagesx($this->image) ) );	
		
		// 회전 후 배경이 투명을 유지 하기 위해서..
		$transColor = imagecolorallocatealpha($largeWaterMark, 255, 255, 255, 127); 
		$largeWaterMark = imagerotate($largeWaterMark, $rotate, $transColor); 		
		
		$w = imagesx($largeWaterMark);
		$h = imagesy($largeWaterMark);		
		
		
		$dest_x 	= $w/2 - imagesx($this->image)/2;		
		$dest_y 	= $h/2 - imagesy($this->image)/2;		
		
		
		//imagecopyresized ( resource $dst_image , resource $src_image , 
		//int $dst_x , int $dst_y , int $src_x , int $src_y , int $dst_w , int $dst_h , int $src_w , int $src_h )
		//imagecopyresampled($this->image, $largeWaterMark,0,0, $dest_x,$dest_y,$image_width,$image_height,$image_width,$image_height);
		imagecopy($this->image, $largeWaterMark,0,0, $dest_x,$dest_y,$image_width,$image_height);//
		ImageDestroy($largeWaterMark);
	
	}
   
 
}
?>