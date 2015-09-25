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
		        break;
		}
	}

    public function __destruct() {
        if (is_resource($this->srcImage)) {
            imagedestroy($this->srcImage);
        }
        if (is_resource($this->destImage)) {
            imagedestroy($this->destImage);
        }
    }

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

	public function display() {
		header('Content-type: image/jpeg');
		imagejpeg($this->destImage);
	}

	public function save($destImagePath) {
		//Save Image
		imagejpeg($this->destImage, $destImagePath);
	}

	public function createCircleImage($newwidth, $newheight) {
		$this->resizeImage($newwidth, $newheight);
		$square = imagesx($this->destImage) < imagesy($this->destImage) ? imagesx($this->destImage) : imagesy($this->destImage);
		$width = $square;
		$height = $square;
		$this->circleCrop($width,$height);
	}

	public function circleCrop($newwidth, $newheight) {
        //$this->reset();
       	$mask = imagecreatetruecolor($newwidth, $newheight);
       	imageantialias($mask, true);
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
		//Create a circle image
		$this->createCircleImage(152, 152);

		$source = $this->destImage;
		imageantialias($source, true);
		$destination = imagecreatefrompng($srcEmptyMarker);
		imagealphablending($destination,false);
		imagesavealpha($destination, true);
		imageantialias($destination, true);
		
		// On charge d'abord les images
		// Les fonctions imagesx et imagesy renvoient la largeur et la hauteur d'une image
		$largeur_source = imagesx($source);
		$hauteur_source = imagesy($source);
		$largeur_destination = imagesx($destination);
		$hauteur_destination = imagesy($destination);
 
		// On veut placer le logo au centre du marker
		$destination_x = 22;
		$destination_y = 23;
 
		// On met le logo (source) dans l'image de destination (le marker)
		imagecopymerge($destination, $source, $destination_x, $destination_y, 0, 0, $largeur_source, $hauteur_source, 60);
 
		header("Content-type: image/png");
		// On affiche l'image de destination qui a été fusionnée avec le logo
		imagepng($destination);

	}

}