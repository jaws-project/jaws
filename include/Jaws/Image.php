<?php
/**
 * Simple and cross-library package to doing image transformations and manipulations.
 *
 * @category   Image
 * @package    Core
 * @author     Jonathan Hernandez  <ion@suavizado.com>
 * @author     Ali Fazelzadeh <afz@php.net>
 * @copyright  2005-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/lesser.html
 * @link       http://pear.php.net/package/Image_Transform
 */
class Jaws_Image
{
    /**
     * Loaded image file
     * @var string
     */
    var $_ifname = '';

    /**
     * read only
     * @return  object  Jaws_Error
     */
    var $_readonly = false;

    /**
     * Type of the image file (eg. jpg, gif png ...)
     * @var string
     */
    var $_itype = '';

    /**
     * Original image width
     * @var int
     */
    var $_img_w = '';

    /**
     * Original image height
     * @var int
     */
    var $_img_h = '';

    /**
     * Holds the image resource for manipulation
     *
     * @var resource $_hImage
     * @access  protected
     */
    var $_hImage = null;

    /**
     * Holds the image raw data
     *
     * @var resource $_iData
     * @access  protected
     */
    var $_iData = '';

    /**
     *
     */
    var $_img_types = array(
        1 => 'gif',
        2 => 'jpg',
        3 => 'png',
        4 => 'swf',
        5 => 'psd',
        6 => 'bmp',
        7 => 'tiff',
        8 => 'tiff',
        9 => 'jpc',
        10 => 'jp2',
        11 => 'jpx',
        12 => 'jb2',
        13 => 'swc',
        14 => 'iff',
        15 => 'wbmp',
        16 => 'xbm',
        17 => 'ico'
    );
 
    /**
     * @var array General options
     * @access  protected
     */
    var $_options = array(
        'quality'     => 75,
        'scaleMethod' => 'smooth',
        'canvasColor' => array(255, 255, 255),
        'pencilColor' => array(0, 0, 0),
        'textColor'   => array(0, 0, 0)
        );

    /**
     * Supported image types
     * @var array
     * @access  protected
     */
    var $_supported_image_types = array();

    /**
     * An interface for available drivers
     *
     * @access  public
     */
    function &factory($imgDriver = '')
    {
        //extension_loaded
        $extensions = array(
            'gd'      => 'GD',
            'imagick' => 'Imagick',
        );

        foreach ($extensions as $ext => $ext_driver) {
            if (!extension_loaded($ext)) {
                $extensions[$ext] = null;
            }
        }

        if (empty($imgDriver)) {
            $imgDriver = $GLOBALS['app']->Registry->fetch('img_driver', 'Settings');
        }
        $imgDriver = preg_replace('/[^[:alnum:]_-]/', '', $imgDriver);

        if (empty($imgDriver) || !in_array($imgDriver, $extensions)) {
            return Jaws_Error::raiseError('No image library specified and none can be found.',
                                          __FUNCTION__);
        }

        $imgDriverFile = JAWS_PATH . 'include/Jaws/Image/'. $imgDriver .'.php';
        if (!file_exists($imgDriverFile)) {
            $GLOBALS['log']->Log(JAWS_LOG_DEBUG, 'Loading image driver failed.');
            return Jaws_Error::raiseError('Loading image driver failed.',
                                          __FUNCTION__);
        }

        include_once $imgDriverFile;
        $className = 'Jaws_Image_' . $imgDriver;
        $obj = new $className();
        return $obj;
    }

    /**
     * Parses input for number format and convert
     * If either parameter is 0 it will be scaled proportionally
     *
     * @param   mixed $new_size (0, number or percentage)
     * @param   mixed $old_size (0, number or percentage)
     * @return  mixed Integer or Jaws_Error
     * @access  protected
     */
    function _parse_size($new_size, $old_size)
    {
        if (substr($new_size, -1) == '%') {
            $new_size = substr($new_size, 0, -1);
            $new_size = round(($new_size / 100) * $old_size);
        }

        return (int) $new_size;
    }

    /**
     * Get new dimensions of image by fixed aspect ratio
     *
     * @param   mixed $new_w
     * @param   mixed $new_h
     * @return  bool
     * @access  protected
     */
    function _parse_size_by_aspect_ratio(&$new_w, &$new_h)
    {
        $new_w = $this->_parse_size($new_w, $this->_img_w);
        $new_h = $this->_parse_size($new_h, $this->_img_h);

        if (($new_h == 0) || (($new_w !=0) && ($this->_img_w > $this->_img_h))) {
            $ratio = ($new_w == 0)? 1 : ($new_w / $this->_img_w);
        } else {
            $ratio = $new_h / $this->_img_h;
        }

        $new_w = round($this->_img_w * $ratio);
        $new_h = round($this->_img_h * $ratio);
        return true;
    }

    /**
     * Sets the image information(width, height, type)
     *
     * @param   string $image Image filename
     * @return  mixed True or Jaws_Error
     * @access  public
     */
    function get_image_details($image)
    {
        $data = @getimagesize($image);
        if (!is_array($data)) {
            return Jaws_Error::raiseError('Cannot fetch image or images details.',
                                          __FUNCTION__);
        }

        // if loaded statically return pure data
        if (!is_subclass_of($this, 'Jaws_Image')) {
            return $data;
        }

        if (!isset($this->_img_types[$data[2]])) {
            return Jaws_Error::raiseError('Cannot recognize image format.',
                                          __FUNCTION__);
        }

        $this->_img_w = $data[0];
        $this->_img_h = $data[1];
        $this->_itype = $this->_img_types[$data[2]];
        $this->_itype = ($this->_itype == 'jpg')? 'jpeg' : $this->_itype;

        return true;
    }

    /**
     * Returns the image type of the extension
     *
     * @param   string  $extension Image type
     * @return  int     The image type if available, or zero
     * @access  public
     */
    function get_image_extension_to_type($extension)
    {
        static $_img_flip_types;
        if (!isset($_img_flip_types)) {
            $_img_flip_types = array_flip($this->_img_types);
        }

        return $_img_flip_types[$extension];
    }

    /**
     * Returns the image type from mime type
     *
     * @param   string   $mime_type  Mime type
     * @return  int      The image type if available, or zero
     * @access  public
     */
    function mime_type_to_image_type($mime_type)
    {
        switch( $mime_type ) {
            case 'image/gif':
                return IMAGETYPE_GIF;
            case 'image/jpeg':
            case 'image/pjpeg':
            case 'image/jpg':
                return IMAGETYPE_JPEG;
            case 'image/png':
                return IMAGETYPE_PNG;
            case 'image/psd':
                return IMAGETYPE_PSD;
            case 'image/bmp':
                return IMAGETYPE_BMP;
            case 'image/tiff':
                return IMAGETYPE_TIFF_II;
            case 'image/jp2':
                return IMAGETYPE_JP2;
            case 'image/iff':
                return IMAGETYPE_IFF;
            case 'image/vnd.wap.wbmp':
                return IMAGETYPE_WBMP;
            case 'image/xbm':
                return IMAGETYPE_XBM;
            case 'image/vnd.microsoft.icon':
                return 17;
            default:
                return false;
        }
    }

    /**
     * Checks if the rectangle passed intersects with the current image
     *
     * @param   int $width  Width of rectangle
     * @param   int $height Height of rectangle
     * @param   int $x      X-coordinate
     * @param   int $y      Y-coordinate
     *
     * @return  bool True if intersects, False if not
     * @access  protected
     */
    function _intersects($width, $height, $x, $y)
    {
        $left  = $x;
        $right = $x + $width;
        if ($right < $left) {
            $left  = $right;
            $right = $x;
        }

        $top    = $y;
        $bottom = $y + $height;
        if ($bottom < $top) {
            $top    = $bottom;
            $bottom = $y;
        }

        return (bool) ($left < $this->_img_w
                       && $right >= 0
                       && $top < $this->_img_h
                       && $bottom >= 0);
    }

    /**
     * Returns if the driver supports a given image type
     *
     * @param   string $type Image type (gif, png, jpeg...)
     * @param   string $mode 'r' for read, 'w' for write, 'rw' for both
     *
     * @return True if type (and mode) is supported false otherwise
     * @access  protected
     */
    function _typeSupported($type, $mode = 'rw')
    {
        return (strpos(@$this->_supported_image_types[strtolower($type)], $mode) === false) ? false : true;
    }

    /**
     * Converts a color string into an array of RGB values
     *
     * @param   string $colorhex A color following the #FFFFFF format
     * @return  array 3-element array with 0-255 values
     * @access  protected
     */
    function _colorhex2colorarray($colorhex)
    {
        $r = hexdec(substr($colorhex, 1, 2));
        $g = hexdec(substr($colorhex, 3, 2));
        $b = hexdec(substr($colorhex, 5, 2));
        return array($r, $g, $b, 'type' => 'RGB');
    }

    /**
     * Converts an array of RGB value into a #FFFFFF format color.
     *
     * @param   array $color 3-element array with 0-255 values
     * @return  mixed A color following the #FFFFFF format or FALSE
     *               if the array couldn't be converted
     * @access  protected
     */
    function _colorarray2colorhex($color)
    {
        if (!is_array($color)) {
            return false;
        }
        $color = sprintf('#%02X%02X%02X', @$color[0], @$color[1], @$color[2]);
        return (strlen($color) != 7) ? false : $color;
    }

    /**
     * Returns a color option
     *
     * @param   string $colorOf one of 'canvasColor', 'pencilColor', 'fontColor'
     * @param   array  $options configuration options
     * @param   array  $default default value to return if color not found
     *
     * @return  array an RGB color array
     * @access  protected
     */
    function _getColor($colorOf, $options = array(), $default = array(0, 0, 0))
    {
        $opt = array_merge($this->_options, (array) $options);
        if (isset($opt[$colorOf])) {
            $color = $opt[$colorOf];
            if (is_array($color)) {
                return $color;
            }
            if ($color{0} == '#') {
                return $this->_colorhex2colorarray($color);
            }
            static $colornames = array();
            include_once 'Image/Transform/Driver/ColorsDefs.php';
            return (isset($colornames[$color])) ? $colornames[$color] : $default;
        }
        return $default;
    }

    /**
     * Returns an option
     *
     * @access  protected
     * @param   string $name    name of option
     * @param   array  $options local override option array
     * @param   array  $default default value to return if option is not found
     * @return  mixed the option
     */
    function _getOption($name, $options = array(), $default = null)
    {
        $opt = array_merge($this->_options, (array) $options);
        return (isset($opt[$name])) ? $opt[$name] : $default;
    }

    /**
     * Loads an image file
     *
     * @param   string    $filename filename
     * @param   bool      $readonly readonly
     * @return  object  Jaws_Error
     * @access  public
     */
    function load($filename, $readonly = false)
    {
        $this->free();
        $this->_ifname = $filename;

        $result = $this->get_image_details($filename);
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        if (!$this->_typeSupported($this->_itype, 'r')) {
            return Jaws_Error::raiseError('Image type not supported for input.',
                                          __FUNCTION__);
        }

        $this->_iData  = '';
        $this->_readonly = $readonly;
        return true;
    }

    /**
     * Loads an image from raw data
     *
     * @param   string    $data  image raw data
     * @param   bool      $readonly readonly
     * @return  mixed True or a Jaws_Error object on error
     * @access  public
     */
    function setData($data, $readonly = false)
    {
        $this->free();
        if (extension_loaded('fileinfo')) {
            $finfo = new finfo(FILEINFO_MIME);
            $mimetype = $finfo->buffer($data);
            $this->_itype = $this->mime_type_to_image_type($mimetype);
        }

        $this->_ifname = '';
        if ($readonly) {
            $this->_readonly = $readonly;
            $this->_iData  = $data;
        }

        return true;
    }

    /**
     * Returns the image handle so that one can further try to manipulate the image
     *
     * @return  object  Jaws_Error
     * @access  public
     */
    function &getHandle()
    {
        return Jaws_Error::raiseError('getHandle() method not supported by driver.',
                                      __FUNCTION__);
    }

    /**
     * Resizes the image in the X and/or Y direction(s)
     *
     * If either is 0 it will keep the original size for that dimension
     *
     * @param   arrayed $new_w   (0, number or percentage)
     * @param   arrayed $new_h   (0, number or percentage)
     * @param   array $options Options
     *
     * @return  mixed True or Jaws_Error object on error
     * @access  public
     */
    function resize($new_w = 0, $new_h = 0, $options = null)
    {
        return Jaws_Error::raiseError('resize() method not supported by driver.',
                                      __FUNCTION__);
    }

    /**
     * Crops an image
     *
     * @param   int $width  Cropped image width
     * @param   int $height Cropped image height
     * @param   int $x      X-coordinate to crop at
     * @param   int $y      Y-coordinate to crop at
     *
     * @return  mixed True or a Jaws_Error object on error
     * @access  public
     **/
    function crop($width, $height, $x = 0, $y = 0)
    {
        return Jaws_Error::raiseError('crop() method not supported by driver.',
                                      __FUNCTION__);
    }

    /**
     * Rotates the image clockwise
     *
     * @param float $angle   Angle of rotation in degres
     * @param   arrayed $options Rotation options
     *
     * @return  mixed True or a Jaws_Error object on error
     * @access  public
     */
    function rotate($angle, $options = null)
    {
        return Jaws_Error::raiseError('rotate() method not supported by driver.',
                                      __FUNCTION__);
    }

    /**
     * Adjusts the image gamma
     *
     * @param float $gamma
     *
     * @return  mixed True or a Jaws_Error on error
     * @access  public
     **/
    function gamma($gamma = 1.0)
    {
        return Jaws_Error::raiseError('gamma() method not supported by driver.',
                                      __FUNCTION__);
    }

    /**
     * Horizontal mirroring
     *
     * @return  mixed True or Jaws_Error on error
     * @access  public
     **/
    function mirror()
    {
        return Jaws_Error::raiseError('mirror() method not supported by driver.',
                                      __FUNCTION__);
    }

    /**
     * Vertical mirroring
     *
     * @return  mixed True or Jaws_Error on error
     * @access  public
     **/
    function flip()
    {
        return Jaws_Error::raiseError('flip() method not supported by driver.',
                                      __FUNCTION__);
    }

    /**
     * Converts an image into grayscale colors
     *
     * @return  mixed True or Jaws_Error on error
     **/
    function grayscale()
    {
        return Jaws_Error::raiseError('grayscale() method not supported by driver.',
                                      __FUNCTION__);
    }

    /**
     * Saves image to file
     *
     * @param   string $filename Filename to save image to
     * @param   string $type     Format of image to save as
     * @param   arrayed  $quality  Format-dependent
     *
     * @return  object  Jaws_Error
     * @access  public
     */
    function save($filename, $type, $quality = null)
    {
        return Jaws_Error::raiseError('save() method not supported by driver.',
                                      __FUNCTION__);
    }

    /**
     * Displays image without saving and lose changes.
     * This method adds the Content-type HTTP header
     *
     * @param   string $type (JPEG, PNG...);
     * @param   arrayed  $quality  Format-dependent
     * @param   int    $expires  set Cache-Control and Expires of HTTP header
     *
     * @return  mixed True on success or Jaws_Error object on error
     * @access  public
     */
    function display($type = '', $quality = null, $expires = 0)
    {
        if ($this->_readonly) {
            if (!empty($this->_ifname)) {
                $this->_iData = @file_get_contents($this->_ifname);
            }

            $type = empty($type)? 'png' : $type;
            header('Content-type: ' . image_type_to_mime_type($this->get_image_extension_to_type($type)));
            if (!empty($expires)) {
                header("Cache-Control: max-age=". $expires);
                header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $expires). ' GMT');
            }

            return $this->_iData;
        }

        return Jaws_Error::raiseError('display() method not supported by driver.',
                                      __FUNCTION__);
    }

    /**
     * Gets EXIF thumbnail
     *
     * @param   string  $source Image path
     * @param   string  $unkown Unknown image to return if image doesn't have a thumb
     * @return  binary  Exif thumbnail
     */
    function get_exif_thumbnail($source, $unknown)
    {
        if (strpos($source, '../')) {
            return false;
        }
        $ext = strtolower(substr($source, strrpos($source,'.')+1));
        $valid_ext = array('jpg', 'jpeg');
        if (in_array($ext, $valid_ext)) {
            if ((function_exists('exif_thumbnail')) && (filesize($source) > 0)) {
                $image = exif_thumbnail($source, $width, $height, $type);
                if ($image !== false) {
                    header('Content-type: ' .image_type_to_mime_type($type));
                    return $image;
                }
            }
        }

        $unknown = Jaws::CheckImage($unknown);
        $ext = strtolower(substr($unknown, strrpos($unknown,'.')+1));
        header('Content-type: image/'.$ext);
        return file_get_contents($unknown);
    }

    /**
     * Releases resource
     *
     * @return  object  Jaws_Error
     * @access  public
     */
    function free()
    {
        $this->_itype    = '';
        $this->_img_w    = '';
        $this->_img_h    = '';
        $this->_ifname   = '';
        $this->_hImage   = null;
        $this->_iData    = '';
        $this->_readonly = false;
    }

}