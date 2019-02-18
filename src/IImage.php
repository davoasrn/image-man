<?php

namespace davoasrn;


interface IImage
{
    public function __construct();

    public function createImage(string $file);

    public function imageFlip(string $pos);

    public function rotate(int $angle);

    public function rectangle(int $x1, int $y1, int $x2, int $y2, string $color, int $thickness);

    public function resize(int $width, int $height);

    public function colorize(string $color);

    public function blur(string $type, int $passes);

    public function generate();

    public function getWidth();

    public function getHeight();

    public function getAspectRatio();
}