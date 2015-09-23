<?php
/**
 * Images manipulation
 */
class ImagesUtils {

	public static function resizeImage($srcImage, $newwidth, $newheight, $destImage = null) {
		$filename = $srcImage;
		list($source_width, $source_height, $source_type) = getimagesize($srcImage);

		switch ($source_type) {
		    case IMAGETYPE_GIF:
		        $source_gdim = imagecreatefromgif($srcImage);
		        break;
		    case IMAGETYPE_JPEG:
		        $source_gdim = imagecreatefromjpeg($srcImage);
		        break;
		    case IMAGETYPE_PNG:
		        $source_gdim = imagecreatefrompng($srcImage);
		        break;
		}

		$source_aspect_ratio = $source_width / $source_height;
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
		    $source_gdim,
		    0, 0,
		    0, 0,
		    $temp_width, $temp_height,
		    $source_width, $source_height
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

		if (isset($destImage)) {
			//Save Image
			imagejpeg($desired_gdim, $destImage);
		} else {
			// Output and free memory
			header('Content-type: image/jpeg');
			imagejpeg($desired_gdim);
		}
		
		imagedestroy($temp_gdim);
		imagedestroy($desired_gdim);
		
	}

	public static function createCircleImage($srcImage, $destImage) {
		$dest = Yii::app()->params['uploadDir']."communecter/image_2_tmp.jpg";
		@unlink($dest);

		self::resizeImage($srcImage, 152, 152, $dest);
		$image = imagecreatefromjpeg($dest);		
		$square = imagesx($image) < imagesy($image) ? imagesx($image) : imagesy($image);
		$width = $square;
		$height = $square;
		$crop = new CircleCrop($image,$width,$height);
		$crop->crop()->save($destImage);
	}

	public static function createMarkerFromImage($profilImage, $srcEmptyMarker, $destImage) {
		//Temporary file. TODO manage random
		$destCircleImage = Yii::app()->params['uploadDir']."communecter/circleImage.png";
		@unlink($destCircleImage);

		//Get a circle image
		self::createCircleImage($profilImage, $destCircleImage);
		$source = imagecreatefrompng($destCircleImage);
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