<?php
class MediaHelper extends AppHelper{

	public $helpers = array('Html','Form');
	public $javascript = false;
	public $explorer = false;

	/**
	 * Generate an image with a specific size
	 * @param  string 	$image   Path of the image (from the webroot directory)
	 * @param  int 		$width
	 * @param  int 		$height
	 * @param  array  	$options Options (same that HtmlHelper::image)
	 * @return string 	<img> tag
	 */
	public function image($image, $width, $height, $options = array()){
		$options['width'] = $width;
		$options['height'] = $height;
		return $this->Html->image($this->resizedUrl($image, $width, $height), $options);
	}

	public function resizedUrl($image, $width, $height){

		if($width == null OR $height == null)
		{
			//on récupère l'extensipn
			$ext=pathinfo($image,PATHINFO_EXTENSION);debug($ext);
			if($ext == 'jpeg' OR $ext == 'jpg') $img = ImageCreateFromJpeg(WWW_ROOT.$image);
			if($ext == 'png') $img = imagecreatefrompng(WWW_ROOT.$image);
			$Ini_Height = ImageSY($img);
			$Ini_Width = ImageSX($img);

			if($width == null)
			{
				$width = round(($Ini_Width*$height)/$Ini_Height, 2);
			}

			if($height == null)
			{
				$height = round(($width*$Ini_Height)/$Ini_Width, 2);
			}
		}

		$this->pluginDir = dirname(dirname(dirname(__FILE__)));
		$image = trim($image, '/');
		$pathinfo = pathinfo($image);
		$dest = sprintf(str_replace(".{$pathinfo['extension']}", '_%sx%s.jpg', $image), $width, $height);
		$image_file = WWW_ROOT . $image;
		$dest_file = WWW_ROOT . $dest;

		// On a déjà le fichier redimensionné ?
		if (!file_exists($dest_file)) {
			require_once APP . 'Plugin' . DS . 'Media' . DS . 'Vendor' . DS . 'imagine.phar';
			$imagine = new Imagine\Gd\Imagine();
			try{
				$imagine->open($image_file)->thumbnail(new Imagine\Image\Box($width, $height), Imagine\Image\ImageInterface::THUMBNAIL_OUTBOUND)->save($dest_file, array('quality' => 90));
			} catch (Imagine\Exception\Exception $e) {
				$alternates = glob(str_replace(".{$pathinfo['extension']}",".*", $image_file));
				if(empty($alternates)){
					return '/img/error.jpg';
				}else{
					try{
						$imagine->open($alternates[0])->thumbnail(new Imagine\Image\Box($width, $height), Imagine\Image\ImageInterface::THUMBNAIL_OUTBOUND)->save($dest_file, array('quality' => 90));
					} catch (Imagine\Exception\Exception $e) {
						return '/img/error.jpg';
					}
				}
			}
		}
		return '/' . $dest;
	}

	public function tinymce($field, $options = array()){
		$this->Html->script('/media/js/tinymce/tiny_mce.js',array('inline'=>false));
		return $this->textarea($field, 'tinymce', $options);
	}

	public function ckeditor($field, $options = array()) {
		$model = $this->Form->_models; $model = key($model);
		$this->Html->script('/media/js/ckeditor/ckeditor.js',array('inline'=>false));
		return $this->textarea($field, 'ckeditor', $options);
	}

	public function redactor($field, $options = array()) {
		$model = $this->Form->_models; $model = key($model);
		$this->Html->script('/media/js/redactor/redactor.min.js',array('inline'=>false));
		$this->Html->css('/Media/js/redactor/redactor.css', null, array('inline'=>false));
		return $this->textarea($field, 'redactor', $options);
	}

	public function textarea($field, $editor = false, $options = array()){
		$options = array_merge(array('label'=>false,'style'=>'width:100%;height:500px','row' => 160, 'type' => 'textarea', 'class' => "wysiwyg $editor"), $options);
		$html = $this->Form->input($field, $options);
		$models = $this->Form->_models;
		$model = key($models);
        if(isset($this->request->data[$model]['id']) && !$this->explorer){
			$html .= '<input type="hidden" id="explorer" value="' . $this->Html->url('/media/medias/index/'.$model.'/'.$this->request->data[$model]['id']) . '">';
			$this->explorer = true;
    	}
    	return $html;
	}

	public function iframe($ref,$ref_id){
		return '<iframe src="' . $this->Html->url("/media/medias/index/$ref/$ref_id") . '" style="width:100%;" id="medias-' . $ref . '-' . $ref_id . '"></iframe>';
	}
}