<?php

namespace davoasrn;


class ImageMan extends ImageGD
{

    /**
     * Load file for modifications
     * @param string $file Image file
     * @return ImageMan
    */
    public function loadFile(string $file) : ImageMan
    {
        if(!file_exists($file))
            throw new \Exception('File Not exists');


        //create new Image
        $this->createImage($file);

        return $this;
    }

    /**
     * Flip an image
     * @param string $direction can tak only:
     *      x HORIZONTAL
     *      y VERTICAL
     *      both BOTH
     * @return ImageMan
    */
    public function doFlip(string $direction) : ImageMan
    {
        $this->imageFlip($direction);

        return $this;
    }

    /**
     * Rotate an Image
     * @param int $angle Rotation angle
     * @return ImageMan
    */
    public function doRotate(int $angle) : ImageMan
    {

        $this->rotate($angle);

        return $this;
    }

    /**
     * Colorize an image
     * @param string $color
     * @return ImageMan
    */
    public function doColorize(string $color) : ImageMan
    {
        $this->colorize($color);

        return $this;
    }

    /**
     * Add border to image
     * @param string $color HEX color
     * @param int $thickness
     * @return ImageMan
    */
    public function border(string $color, int $thickness = 1) : ImageMan
    {
        $x1 = 0;
        $y1 = 0;
        $x2 = $this->getWidth() - 1;
        $y2 = $this->getHeight() - 1;

        $this->rectangle($x1++, $y1++, $x2--, $y2--, $color, $thickness);

        return $this;
    }

    /**
     * Save new image to file
     * @param string $file file name
     * @return ImageMan
    */
    public function toFile(string $file) : ImageMan
    {
        $image = $this->generate();

        // Save the image to file
        if(!file_put_contents($file, $image['data'])) {
            throw new \Exception("Failed to write image to file: $file");
        }

        return $this;
    }

    /**
     * Blur an image
     * @param string $type blur type
     * @param int $passes Times that blur option will apply
     * @return ImageMan
    */
    public function doBlur(string $type = 'selective', int $passes = 1) : ImageMan
    {
        $this->blur($type, $passes);

        return $this;
    }

    /**
     * Resize an image
     * @param int $width Width of new image
     * @param int $height Height of new image
     * @return ImageMan
    */
    public function doResize(int $width = null, int $height= null) : ImageMan
    {

        if(!$width)
            $width = $this->getWidth();

        if(!$height)
            $height = $this->getHeight();


        // Resize to width
        if($width && !$height) {
            $height = $width / $this->getAspectRatio();
        }

        // Resize to height
        if(!$width && $height) {
            $width = $height * $this->getAspectRatio();
        }

        $this->resize($width, $height);

        return $this;
    }

}