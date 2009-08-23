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
    protected static $_imagemagick;

    //temporary image data
    protected $_image;
    
    /**
     * Check if ImageMagick are installed
     *
     * @return boolean true if installed
     */
    public static function check()
    {
        exec(Image_ImageMagick::$_imagemagick . '/convert', $response, $status);

        if($status)
        {
            throw new Kohana_Exception('ImageMagick is not installed in :path, check your configuration', array(':path'=>Image_ImageMagick::$_imagemagick));
        }

        return Image_ImageMagick::$_checked = TRUE;
    }

    /**
     * Class constructor
     *
     * @param string $file path to file to load
     */
    public function __construct($file)
    {
        //load ImageMagick path from config
        Image_ImageMagick::$_imagemagick = Kohana::Config('imagemagick')->path;

        if(!is_dir(Image_ImageMagick::$_imagemagick))
        {
            throw new Kohana_Exception('ImageMagick path is not a valid directory, check your configuration');
        }

        if ( ! Image_ImageMagick::$_checked)
        {
            // Run the install check
             Image_ImageMagick::check();
        }

        parent::__construct($file);
    }


    protected function _do_resize($width, $height) {}
    protected function _do_crop($width, $height, $offset_x, $offset_y){}
    protected function _do_rotate($degrees){}
    protected function _do_flip($direction){}
    protected function _do_sharpen($amount){}
    protected function _do_reflection($height, $opacity, $fade_in){}
    protected function _do_watermark(Image $image, $offset_x, $offset_y, $opacity){}
    protected function _do_background($r, $g, $b, $opacity){}
    
    /**
     * Save the image to a file
     * 
     * @param string $file path or filename of new image file
     * @param integer $quality quality level of new image
     */
    protected function _do_save($file, $quality)
    {
        //if tmp image file not exist, use original
        $filein = (!is_object($this->_image)) ? $this->file : $this->_image->file;

        $command = Image_ImageMagick::$_imagemagick.'/convert "'.$filein.'"';

        $command .= (isset($quality)) ? ' -quality '.$quality : '';

        $command .= ' "'.$file.'"';

        exec($command, $response, $status);

        if(!$status)
        {
            return TRUE;
        }

        return FALSE;
    }
    
    /**
     * Return RAW (bytes) image
     * 
     * @param string $type any image type support by ImageMagick {@link http://imagemagick.org/script/formats.php IM Formats}
     * @param integer $quality quality of image
     * @return byte raw image
     */
    protected function _do_render($type, $quality)
    {
        $tmpfile = tempnam(sys_get_temp_dir(), '');

        //if tmp image file not exist, use original
        $filein = (!is_object($this->_image)) ? $this->file : $this->_image->file;

        $command = Image_ImageMagick::$_imagemagick.'/convert "'.$filein.'"';
        $command .= (isset($quality)) ? ' -quality '.$quality : '';
        $command .= ' "'.strtoupper($type).':'.$tmpfile.'"';

        exec($command, $response, $status);

        if(!$status)
        {
            // Capture the output
            ob_start();

            readfile($tmpfile);

            return ob_get_clean();
        }

        return FALSE;
    }

    /**
     * Get and return file info
     * 
     * @param string $file path to file
     * @return object file info 
     */
    private function _get_info($file)
    {
        try
        {
            // Get the real path to the file
            $file = realpath($file);

            // Get the image information
            $info = getimagesize($file);
        }
        catch (Exception $e)
        {
            // Ignore all errors while reading the image
        }

        if (empty($file) OR empty($info))
        {
            throw new Kohana_Exception('Not an image or invalid image: :file',
                    array(':file' => Kohana::debug_path($file)));
        }

        $return = new stdClass();

        $return->file   = $file;
        $return->width  = $info[0];
        $return->height = $info[1];
        $return->type   = $info[2];
        $return->mime   = image_type_to_mime_type($return->type);

        return $return;
    }
}

?>
