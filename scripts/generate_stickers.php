<?php
// Create public/stickers directory if it doesn't exist
$dir = __DIR__ . '/../public/stickers';
if (!file_exists($dir)) {
    mkdir($dir, 0777, true);
}

// Helper to create a sticker
function createSticker($name, $color, $shape) {
    $width = 200;
    $height = 200;
    $img = imagecreatetruecolor($width, $height);
    
    // Transparent background
    imagesavealpha($img, true);
    $transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
    imagefill($img, 0, 0, $transparent);
    
    $c = imagecolorallocate($img, $color[0], $color[1], $color[2]);
    
    if ($shape === 'circle') {
        imagefilledellipse($img, 100, 100, 150, 150, $c);
    } elseif ($shape === 'rect') {
        imagefilledrectangle($img, 50, 80, 150, 120, $c); // Like sunglasses
    } elseif ($shape === 'triangle') {
        // Like a hat
        $points = [
            100, 20,
            40, 100,
            160, 100
        ];
        imagefilledpolygon($img, $points, 3, $c);
    }
    
    imagepng($img, __DIR__ . '/../public/stickers/' . $name);
    imagedestroy($img);
    echo "Created $name\n";
}

createSticker('hat.png', [255, 0, 0], 'triangle');
createSticker('glasses.png', [0, 0, 0], 'rect');
createSticker('badge.png', [0, 255, 0], 'circle');
