<?php
class ImageConverter extends Converter {
	use ObjectHelper;
	
}
class TIFFConverter extends ImageConverter {
	function convertToJPG($file) {
		$image = new Imagick($file);
		$image->setImageFormat('jpg');
		
		$image->writeImage($file . '.jpg');
		
	}
}
?>
