<?php
/**
 * Images manipulation
 */
class ImagesUtils {
	
	private $srcImage;
	private $source_width;
	private $source_height;
	private $source_type;
	private $destImage;
	private $dest_type;
	private $dest_width;
	private $dest_height;

	/**
	 * Construct the Image Utility class
	 * @param String $srcImage : path to the image to transform. Must be a gif, a jpeg or a png
	 * @return type
	 */
	public function __construct($srcImage) {
		list($this->source_width, $this->source_height, $this->source_type) = getimagesize($srcImage);
		
		switch ($this->source_type) {
		    case IMAGETYPE_GIF:
		        $this->srcImage = imagecreatefromgif($srcImage);
		        break;
		    case IMAGETYPE_JPEG:
		        $this->srcImage = imagecreatefromjpeg($srcImage);
		        break;
		    case IMAGETYPE_PNG:
		        $this->srcImage = imagecreatefrompng($srcImage);
		        $this->transformTransparencyToWhite();
		        $this->srcImage = $this->destImage;
		        break;
		}
		//By default the destination type is the same than the source type
		$this->dest_type = $this->source_type;
	}

    public function __destruct() {
        if (is_resource($this->srcImage)) {
            imagedestroy($this->srcImage);
        }
        if (is_resource($this->destImage)) {
            imagedestroy($this->destImage);
        }
    }

	public function display() {
		header('Content-type: image/png');
		imagepng($this->destImage);
	}

	public function save($destImagePath, $quality="100") {
		//Save Image
		switch ($this->dest_type) {
		    case IMAGETYPE_GIF:
		        imagegif($this->destImage, $destImagePath,$quality);
		        break;
		    case IMAGETYPE_JPEG:
		        imagejpeg($this->destImage, $destImagePath,$quality);
		        break;
		    case IMAGETYPE_PNG:
		    	$q=9/100;
				$quality*=$q;
		        imagepng($this->destImage, $destImagePath,$quality);
		        break;
		}
	}

	/**
	 * Resize the image a newWidth and a newHeight
	 * If the image is a png with transparency, the background will be filled with white color
	 * @param int $newwidth 
	 * @param int $newheight 
	 * @return this
	 */
	public function resizeImage($newwidth, $newheight) {

		$source_aspect_ratio = $this->source_width / $this->source_height;
		$desired_aspect_ratio = $newwidth / $newheight;

		if ($source_aspect_ratio > $desired_aspect_ratio) {
		    // Triggered when source image is wider
		    $temp_height = $newheight;
		    $temp_width = ( int ) ($newheight * $source_aspect_ratio);
		} else {
		    // Triggered otherwise (i.e. source image is similar or taller)
		    $temp_width = $newwidth;
		    $temp_height = ( int ) ($newwidth / $source_aspect_ratio);
		}

		/*
		 * Resize the image into a temporary GD image
		 */
		$temp_gdim = imagecreatetruecolor($temp_width, $temp_height);
		imagecopyresampled(
		    $temp_gdim,
		    $this->srcImage,
		    0, 0,
		    0, 0,
		    $temp_width, $temp_height,
		    $this->source_width, $this->source_height
		);

		/*
		 * Copy cropped region from temporary image into the desired GD image
		 */
		$x0 = ($temp_width - $newwidth) / 2;
		$y0 = ($temp_height - $newheight) / 2;
		$desired_gdim = imagecreatetruecolor($newwidth, $newheight);

		imagecopy(
		    $desired_gdim,
		    $temp_gdim,
		    0, 0,
		    $x0, $y0,
		    $newwidth, $newheight
		);

		$this->destImage = $desired_gdim;
		imagedestroy($temp_gdim);
		//imagedestroy($desired_gdim);
		
		return $this;
	}
	/**
	 * Resize the image a newWidth and a newHeight
	 * If the image is a png with transparency, the background will be filled with white color
	 * @param int $newwidth 
	 * @param int $newheight 
	 * @return this
	 */
	public function resizePropertionalyImage($newwidth, $newheight) {
		$width = $this->source_width;
	    $height = $this->source_height;
	
	    # taller
	    if ($height > $newheight) {
	        $width = ($newheight / $height) * $width;
	        $height = $newheight;
	        $resized=true;
	    }
	
	    # wider
	    if ($width > $newwidth) {
	        $height = ($newwidth / $width) * $height;
	        $width = $newwidth;
			$resized=true;
	    }

	    $temp_width = $width;
		$temp_height =$height;
		/*$source_aspect_ratio = $this->source_width / $this->source_height;
		$desired_aspect_ratio = $newwidth / $newheight;

		if ($source_aspect_ratio > $desired_aspect_ratio) {
		    // Triggered when source image is wider
		    $temp_height = $newheight;
		    $temp_width = ( int ) ($newheight * $source_aspect_ratio);
		} else {
		    // Triggered otherwise (i.e. source image is similar or taller)
		    $temp_width = $newwidth;
		    $temp_height = ( int ) ($newwidth / $source_aspect_ratio);
		}*/
		//echo $temp_width."///".$temp_height;
		/*
		 * Resize the image into a temporary GD image
		 */
		$temp_gdim = imagecreatetruecolor($temp_width, $temp_height);
		imagecopyresampled(
		    $temp_gdim,
		    $this->srcImage,
		    0, 0,
		    0, 0,
		    $temp_width, $temp_height,
		    $this->source_width, $this->source_height
		);
		$this->destImage = $temp_gdim;
		imagedestroy($this->srcImage);
		/*
		 * Copy cropped region from temporary image into the desired GD image
		 */
		/*$x0 = ($temp_width - $newwidth) / 2;
		$y0 = ($temp_height - $newheight) / 2;
		$desired_gdim = imagecreatetruecolor($newwidth, $newheight);

		imagecopy(
		    $desired_gdim,
		    $temp_gdim,
		    0, 0,
		    $x0, $y0,
		    $newwidth, $newheight
		);

		$this->destImage = $desired_gdim;
		imagedestroy($temp_gdim);
		//imagedestroy($desired_gdim);*/
		return $this;
	}
	public function createCircleImage($newwidth, $newheight) {
		//There will be transparency around the circle so dest Type is png
		$this->dest_type = IMAGETYPE_PNG;

		$this->resizeImage($newwidth, $newheight);
		$square = imagesx($this->destImage) < imagesy($this->destImage) ? imagesx($this->destImage) : imagesy($this->destImage);
		$width = $square;
		$height = $square;
		$this->circleCrop($width,$height);
		return $this;
	}

	private function circleCrop($newwidth, $newheight) {
        //$this->reset();
       	$mask = imagecreatetruecolor($newwidth, $newheight);
       	imagealphablending($mask,false);
		
        $maskTransparent = imagecolorallocate($mask, 255, 0, 255);
        imagecolortransparent($mask, $maskTransparent);
        imagefilledellipse($mask, $newwidth / 2, $newheight / 2, $newwidth, $newheight, $maskTransparent);
        
        imagecopymerge($this->destImage, $mask, 0, 0, 0, 0, $newwidth, $newheight, 100);
        $dstTransparent = imagecolorallocate($this->destImage, 255, 0, 255);
        imagefill($this->destImage, 0, 0, $dstTransparent);
        imagefill($this->destImage, $newwidth - 1, 0, $dstTransparent);
        imagefill($this->destImage, 0, $newheight - 1, $dstTransparent);
        imagefill($this->destImage, $newwidth - 1, $newheight - 1, $dstTransparent);
        imagecolortransparent($this->destImage, $dstTransparent);
        return $this;
    }

	public function createMarkerFromImage($srcEmptyMarker) {
		//There will be transparency around the circle so dest Type is png
		$this->dest_type = IMAGETYPE_PNG;

		//Create a circle image
		$this->createCircleImage(40, 40);

		$source = $this->destImage;
		
		$destination = imagecreatefrompng($srcEmptyMarker);
		imagealphablending($destination,false);
		imagesavealpha($destination, true);
		
		// On charge d'abord les images
		// Les fonctions imagesx et imagesy renvoient la largeur et la hauteur d'une image
		$largeur_source = imagesx($source);
		$hauteur_source = imagesy($source);
		$largeur_destination = imagesx($destination);
		$hauteur_destination = imagesy($destination);
 
		// On veut placer le logo au centre du marker
		$destination_x = 6;
		$destination_y = 6;
 
		// On met le logo (source) dans l'image de destination (le marker)
		imagecopymerge($destination, $source, $destination_x, $destination_y, 0, 0, $largeur_source, $hauteur_source, 100);
 
 		$this->destImage = $destination;
 		
		return $this;
	}

	public function transformTransparencyToWhite() {
		$this->destImage = imagecreatetruecolor($this->source_width, $this->source_height);
		$white = imagecolorallocate($this->destImage,  255, 255, 255);
		imagefilledrectangle($this->destImage, 0, 0, $this->source_width, $this->source_height, $white);
		imagecopy($this->destImage, $this->srcImage, 0, 0, 0, 0, $this->source_width, $this->source_height);
	}

	private function _clone_img_resource($img) {

	  //Get width from image.
	  $w = imagesx($img);
	  //Get height from image.
	  $h = imagesy($img);
	  //Get the transparent color from a 256 palette image.
	  $trans = imagecolortransparent($img);

	  //If this is a true color image...
	  if (imageistruecolor($img)) {

	    $clone = imagecreatetruecolor($w, $h);
	    imagealphablending($clone, false);
	    imagesavealpha($clone, true);
	  }
	  //If this is a 256 color palette image...
	  else {

	    $clone = imagecreate($w, $h);

	    //If the image has transparency...
	    if($trans >= 0) {

	      $rgb = imagecolorsforindex($img, $trans);

	      imagesavealpha($clone, true);
	      $trans_index = imagecolorallocatealpha($clone, $rgb['red'], $rgb['green'], $rgb['blue'], $rgb['alpha']);
	      imagefill($clone, 0, 0, $trans_index);
	    }
	  }

	  //Create the Clone!!
	  imagecopy($clone, $img, 0, 0, 0, 0, $w, $h);

	  return $clone;
	}



}