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
    // Path to ImageMagick binaries
    protected static $_imagemagick;

    // Temporary image file
    protected $filetmp;
    
    /**
     * Check if ImageMagick are installed
     *
     * @return boolean true if installed
     */
    public static function check()
    {
        exec(Image_ImageMagick::get_command('convert'), $response, $status);

        if ($status)
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
        // Load ImageMagick path from config
        Image_ImageMagick::$_imagemagick = Kohana::Config('imagemagick')->path;

        if (! is_dir(Image_ImageMagick::$_imagemagick) )
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

    /**
     * Resize an image
     *
     * @param integer $width 
     * @param integer $height
     */
    protected function _do_resize($width, $height)
    {
        $filein = ( ! is_null($this->filetmp) ) ? $this->filetmp : $this->file;
        
        // Create a temporary file to store the new image
        $fileout = tempnam(sys_get_temp_dir(), '');

        $command = Image_ImageMagick::get_command('convert')." \"$filein\"";
        $command .= ' -quality 100 -geometry '.$width.'x'.$height.'\!';
        $command .= ' "'.$fileout.'"';

        exec($command, $response, $status);

        if ( ! $status )
        {
            // Delete old tmp file if exist
            if ( ! is_null($this->filetmp) )
            {
                unlink($this->filetmp);
            }

            // Update image data
            $this->filetmp = $fileout;
            $this->width = $width;
            $this->height = $height;

            return TRUE;
        }

        return FALSE;
    }

    /**
     * Do crop
     * 
     * @param integer $width
     * @param integer $height
     * @param integer $offset_x
     * @param integer $offset_y 
     */
    protected function _do_crop($width, $height, $offset_x, $offset_y)
    {
        $filein = ( ! is_null($this->filetmp) ) ? $this->filetmp : $this->file;

        // Create a temporary file to store the new image
        $fileout = tempnam(sys_get_temp_dir(), '');

        $command = Image_ImageMagick::get_command('convert')." \"$filein\"";
        $command .= ' -quality 100 -crop '.$width.'x'.$height.'+'.$offset_x.'+'.$offset_y;
        $command .= ' "'.$fileout.'"';

        exec($command, $response, $status);

        if ( ! $status )
        {
            // Delete old tmp file if exist
            if ( ! is_null($this->filetmp) )
            {
                unlink($this->filetmp);
            }

            // Get the image information
            $info = $this->get_info($fileout);

            // Update image data
            $this->filetmp = $fileout;
            $this->width = $info->width;
            $this->height = $info->height;

            return TRUE;
        }

        return FALSE;
    }

    /**
     * Rotate image
     * 
     * @param float $degrees 
     */
    protected function _do_rotate($degrees)
    {
        $filein = ( ! is_null($this->filetmp) ) ? $this->filetmp : $this->file;

        // Create a temporary file to store the new image
        $fileout = tempnam(sys_get_temp_dir(), '');

        $command = Image_ImageMagick::get_command('convert')." \"$filein\"";
        $command .= ' -quality 100 -matte -background none -rotate '.$degrees;
        $command .= ' "PNG:'.$fileout.'"'; // Save as PNG for transparency

        exec($command, $response, $status);

        if ( ! $status )
        {
            // Delete old tmp file if exist
            if ( ! is_null($this->filetmp) )
            {
                unlink($this->filetmp);
            }

            // Get the image information
            $info = $this->get_info($fileout);

            // Update image data
            $this->filetmp = $fileout;
            $this->width = $info->width;
            $this->height = $info->height;
            $this->type = $info->type;
            $this->mime = $info->mime;

            return TRUE;
        }

        return FALSE;
    }

    /**
     * Do flip
     * 
     * @param integer $direction Image::HORIZONTAL or Image::VERTICAL
     */
    protected function _do_flip($direction)
    {
        $flip_command = ($direction === Image::HORIZONTAL) ? '-flop': '-flip';

        $filein = ( ! is_null($this->filetmp) ) ? $this->filetmp : $this->file;

        // Create a temporary file to store the new image
        $fileout = tempnam(sys_get_temp_dir(), '');

        $command = Image_ImageMagick::get_command('convert')." \"$filein\"";
        $command .= ' -quality 100 '.$flip_command;
        $command .= ' "'.$fileout.'"';

        exec($command, $response, $status);

        if ( ! $status )
        {
            // Delete old tmp file if exist
            if ( ! is_null($this->filetmp) )
            {
                unlink($this->filetmp);
            }

            // Update image data
            $this->filetmp = $fileout;

            return TRUE;
        }

        return FALSE;
    }

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
        // If tmp image file not exist, use original
        $filein =  (! is_null($this->filetmp) ) ? $this->filetmp : $this->file;

        $command = Image_ImageMagick::get_command('convert')." \"$filein\"";
        $command .= (isset($quality)) ? ' -quality '.$quality : '';
        $command .= ' "'.$file.'"';

        exec($command, $response, $status);

        if (! $status )
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

        // If tmp image file not exist, use original
        $filein = ( ! is_null($this->filetmp) ) ? $this->filetmp : $this->file;

        $command = Image_ImageMagick::get_command('convert')." \"$filein\"";
        $command .= (isset($quality)) ? ' -quality '.$quality : '';
        $command .= ' "'.strtoupper($type).':'.$tmpfile.'"';

        exec($command, $response, $status);

        if ( ! $status)
        {
            // Capture the output
            ob_start();

            readfile($tmpfile);

            // Delete tmp file
            unlink($tmpfile);

            return ob_get_clean();
        }

        return FALSE;
    }

    /**
     * Return a especified command for the current OS
     * @param string $command
     * @return string command in current OS
     */
    protected static function get_command($command)
    {
        // Running OS
        static $os;

        if ( is_null($os) )
        {
            $os = (strtoupper(substr(php_uname(), 0, 3)) === 'WIN') ? 'windows' : 'unix';
        }

        $command = Image_ImageMagick::$_imagemagick.'/'.$command;
        
        return ($os == 'windows') ? $command.'.exe' : $command;
    }

    /**
     * Get and return file info
     *
     * @param string $file path to file
     * @return object file info
     */
    private function get_info($file)
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

    /**
     * Class destructor
     */
    public function __destruct()
    {
        if ( ! is_null($this->filetmp) )
        {
            // Free all resources
            unlink($this->filetmp);
        }
    }
}

?>
