<?php
require_once __DIR__ . '/vendor/autoload.php';

use davoasrn\ImageMan;

try {
    // Create a new object
    $image = new ImageMan();

    // Manipulate
        $image
            ->loadFile('penguin.jpg')           // load image
            //->doRotate(20)                        // rotate by angle -360 360
            ->doFlip('both')               // flip x horizontally, y vertically, both both
            //->doColorize('#ffd700')               // tint hex color
            ->border('#ffd700', 15)  // add a 15 pixel yellow border, hex color
            //->doBlur('gaussian', 5)               // blur default to selective, 5 depth
            //->doResize(400, 300)                  // resize dimensions: 400 width and 300 height
            ->toFile('new_penguin.jpg');        // output to file with prefix new

} catch(Exception $err) {
    // Handle errors
    echo $err->getMessage();
}
