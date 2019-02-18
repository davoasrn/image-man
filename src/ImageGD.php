<?php

namespace davoasrn;


class ImageGD implements IImage
{
    private $mimeType;
    private $image;
    private $width;
    private $height;

    public function __construct()
    {
        if(!extension_loaded('gd'))
        {
            throw new \Exception('GD extension for manipulationg images is required, please install it at first');
        }
    }
    /**
     * Get image necessary information
     * and assign it to width, height and mime-type
     * @param string $file image file name
    */
    private function getImageInfo(string $file)
    {
        $imgSettings = getimagesize($file);

        if(!$imgSettings){
            throw new \Exception('Not an Image');
        }else{
            ["0"=>$this->width, "1"=>$this->height, "mime"=>$this->mimeType] = $imgSettings;
        }
    }

    /**
     * Create new image from file
     *   by using current image mime-type
     * @param string $file image file name
    */
    public function createImage($file)
    {
        $this->getImageInfo($file);
        switch ($this->mimeType){
            case'image/jpeg':
                $this->image = imagecreatefromjpeg($file);
                break;
            case 'image/png':
                $this->image = imagecreatefrompng($file);
                break;
            default :
                throw new \Exception('Wrong file format or not supported, allowed only JPEG an PNG');
        }
    }

    /**
     * Flip an image by using the given position
     * @param string $direction This parameter is used
     *   to flip the image in different modes
     *   such as Horizontal,Vertical or Both
    */
    public function imageFlip(string $direction)
    {
        switch($direction) {
            case 'x':
                $pos = IMG_FLIP_HORIZONTAL;
                break;
            case 'y':
                $pos = IMG_FLIP_VERTICAL;
                break;
            case 'both':
                $pos = IMG_FLIP_BOTH;
                break;
            default:
                throw new \Exception('Wrong flip mode');
        }

        imageflip($this->image, $pos);
    }

    /**
     * Rotate an image with a given angle
     * @param int $angle Rotation angle, in degrees
    */
    public function rotate(int $angle)
    {
        if($angle < -360 || $angle > 360)
            throw new \Exception('Wrong angle');

        imagerotate(
            $this->image,
            -$angle,
            0
        );
    }

    /**
     * Draw a rectangle starting at the specified coordinates
     * @param int $x1 Upper left x coordinate
     * @param int $y1 Upper left y coordinate 0
     * @param int $x2 Bottom right x coordinate
     * @param int $y2 Bottom right y coordinate
     * @param string $color hex color
     * @param int $thickness thickness for line drawing
    */
    public function rectangle(int $x1, int $y1, int $x2, int $y2, string $color, int $thickness = 1)
    {
        if(!preg_match('/#([a-f0-9]{3}){1,2}\b/i', $color))
            throw new \Exception('Wrong color');

        $color = $this->allocateColor($color);

        imagesetthickness($this->image, $thickness);
        imagerectangle($this->image, $x1, $y1, $x2, $y2, $color);
    }

    /**
     * Resize image
     * @param int $width Width of new image
     * @param int $height Height of new image
    */
    public function resize(int $width, int $height)
    {

        $newImage = imagecreatetruecolor($width, $height);
        imagecopyresampled(
            $newImage,
            $this->image,
            0, 0, 0, 0,
            $width,
            $height,
            $this->width,
            $this->height
        );

        //Assigning new image to our existing class
        $this->image = $newImage;
    }

    /**
     * Generate new image source for output
     * @return array Returnes mime type of image and data of image
    */
    public function generate() : array
    {
        $mimeType = $this->mimeType;

        $quality = 100;

        // Capture output
        ob_start();

        // Generate the image
        switch($mimeType) {
            case 'image/jpeg':
                imageinterlace($this->image, true);
                imagejpeg($this->image, null, $quality);
                break;
            case 'image/png':
                imagesavealpha($this->image, true);
                imagepng($this->image, null, round(9 * $quality / 100));
                break;
            default:
                throw new \Exception('Unsupported format: ' . $mimeType);
        }

        // Stop capturing
        $data = ob_get_contents();
        ob_end_clean();

        return [
            'data' => $data,
            'mimeType' => $mimeType
        ];
    }

    /**
     * Colorize an image
     * @param string $color HEX color
    */
    public function colorize(string $color)
    {
        if(!preg_match('/#([a-f0-9]{3}){1,2}\b/i', $color))
            throw new \Exception('Wrong color');

        [$red, $green, $blue] = $this->getColorHex($color);

        $this->imageFilter(IMG_FILTER_COLORIZE, $red ,  $green, $blue,  0);
    }

    /**
     * Allocate a color for an image
     * @param string $color HEX color
     * @return color identifier
    */
    public function allocateColor(string $color)
    {
        if(!preg_match('/#([a-f0-9]{3}){1,2}\b/i', $color))
            throw new \Exception('Wrong color');

        [$red, $green, $blue] = $this->getColorHex($color);

        // Is this color allocated
        $index = imagecolorexactalpha(
            $this->image,
            $red,
            $green,
            $blue,
            0
        );
        if($index > -1) {
            // Yes, return this color index
            return $index;
        }

        // Allocate a new color index
        $imageAllocate = imagecolorallocatealpha(
            $this->image,
            $red,
            $green,
            $blue,
            0
        );
        if(!$imageAllocate)
            throw new \Exception('Allocation failed');

        return $imageAllocate;
    }

    /**
     * Converts HEX color to RGB
     * @param string $color HEX color
     * @return array RGB colors
    */
    public function getColorHex(string $color) : array
    {
        if(!preg_match('/#([a-f0-9]{3}){1,2}\b/i', $color))
            throw new \Exception('Wrong color');

        [$red, $green, $blue] = sscanf($color, "#%02x%02x%02x");

        return [$red, $green, $blue];
    }

    /**
     * Blur image
     * @param string $type apply filter type
     * @param int $passes apply filter $passes times
    */
    public function blur(string $type = 'selective', int $passes = 1)
    {
        $filter = $type === 'gaussian' ? IMG_FILTER_GAUSSIAN_BLUR : IMG_FILTER_SELECTIVE_BLUR;

        for($i = 0; $i < $passes; $i++) {
            $this->imageFilter($filter);
        }
    }

    /**
     * Applies a filter to an image
     * @param int $filter filter type can be one of the following
     * @param int $red Value of red component
     * @param int $green Value of green component
     * @param int $blue Value of blue component
     * @param int $alpha Alpha channel, from 0 to 127
    */
    public function imageFilter(int $filter, int $red = null, int $green = null, int $blue = null, int $alpha = null)
    {
        imagefilter(
            $this->image,
            $filter,
            $red,
            $green,
            $blue,
            $alpha
        );
    }

    /**
     * @return float aspect ratio
     */
    public function getAspectRatio() : float
    {
        return $this->height / $this->height;
    }

    /**
     * @return int image Width
    */
    public function getWidth() : int
    {
        return $this->width;
    }

    /**
     * @return int image height
    */
    public function getHeight() : int
    {
        return $this->height;
    }
}