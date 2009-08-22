<?php
/**
 * Image manipulation class using {@link  http://imagemagick.org ImageMagick}.
 *
 * @package    Image
 * @author     Kohana Team
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Image_ImageMagick extends Image
{
    //path to ImageMagick binaries
    protected static $_imagemagick = NULL;
    
    /**
     * Check if ImageMagick are installed
     *
     * @return boolean true if installed
     */
    public static function check()
    {
        return Image_ImageMagick::$_checked = TRUE;
    }

    public function __construct($file)
    {
        //load ImageMagick path from config
        $config = Kohana::Config('imagemagick');
        var_dump($config);
    }


    protected function _do_resize($width, $height) {}

    protected function _do_crop($width, $height, $offset_x, $offset_y){}
    protected function _do_rotate($degrees){}
    protected function _do_flip($direction){}
    protected function _do_sharpen($amount){}
    protected function _do_reflection($height, $opacity, $fade_in){}
    protected function _do_watermark(Image $image, $offset_x, $offset_y, $opacity){}
    protected function _do_background($r, $g, $b, $opacity){}
    protected function _do_save($file, $quality){}
    protected function _do_render($type, $quality){}
}

?>
