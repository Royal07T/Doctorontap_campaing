#!/usr/bin/env php
<?php

/**
 * PWA Icon Generator for DoctorOnTap
 * 
 * This script generates PWA icons in multiple sizes from a source image.
 * Requires GD or Imagick PHP extension.
 */

$sourceImage = __DIR__ . '/public/img/sitelogo.png';
$outputDir = __DIR__ . '/public/img/pwa/';

// Icon sizes needed for PWA
$sizes = [72, 96, 128, 144, 152, 192, 384, 512];

// Check if source image exists
if (!file_exists($sourceImage)) {
    echo "Error: Source image not found at: $sourceImage\n";
    echo "Please ensure your logo file exists.\n";
    exit(1);
}

// Create output directory if it doesn't exist
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
    echo "Created directory: $outputDir\n";
}

// Check for image processing capabilities
$useImagick = extension_loaded('imagick');
$useGD = extension_loaded('gd');

if (!$useImagick && !$useGD) {
    echo "Error: Neither Imagick nor GD extension is available.\n";
    echo "Please install one of these PHP extensions to generate icons.\n";
    echo "\nAlternatively, you can use online tools like:\n";
    echo "- https://www.pwabuilder.com/imageGenerator\n";
    echo "- https://realfavicongenerator.net/\n";
    exit(1);
}

echo "Generating PWA icons from: $sourceImage\n";
echo "Using: " . ($useImagick ? "Imagick" : "GD") . "\n\n";

foreach ($sizes as $size) {
    $outputFile = $outputDir . "icon-{$size}x{$size}.png";
    
    try {
        if ($useImagick) {
            generateIconImagick($sourceImage, $outputFile, $size);
        } else {
            generateIconGD($sourceImage, $outputFile, $size);
        }
        echo "✓ Generated: icon-{$size}x{$size}.png\n";
    } catch (Exception $e) {
        echo "✗ Failed to generate icon-{$size}x{$size}.png: " . $e->getMessage() . "\n";
    }
}

// Generate screenshot placeholders
echo "\nGenerating placeholder screenshots...\n";
createPlaceholderScreenshot($outputDir . 'screenshot-mobile.png', 750, 1334);
createPlaceholderScreenshot($outputDir . 'screenshot-wide.png', 1280, 720);

echo "\n✓ PWA icon generation complete!\n";
echo "\nNext steps:\n";
echo "1. Review the generated icons in: $outputDir\n";
echo "2. Replace placeholder screenshots with actual app screenshots\n";
echo "3. Test your PWA installation on mobile devices\n";

/**
 * Generate icon using Imagick
 */
function generateIconImagick($source, $output, $size) {
    $image = new Imagick($source);
    $image->setBackgroundColor(new ImagickPixel('transparent'));
    $image->thumbnailImage($size, $size, true, true);
    $image->setImageFormat('png');
    $image->writeImage($output);
    $image->clear();
}

/**
 * Generate icon using GD
 */
function generateIconGD($source, $output, $size) {
    $sourceImage = imagecreatefrompng($source);
    if (!$sourceImage) {
        throw new Exception("Failed to load source image");
    }
    
    $width = imagesx($sourceImage);
    $height = imagesy($sourceImage);
    
    $newImage = imagecreatetruecolor($size, $size);
    
    // Preserve transparency
    imagealphablending($newImage, false);
    imagesavealpha($newImage, true);
    $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
    imagefilledrectangle($newImage, 0, 0, $size, $size, $transparent);
    
    imagecopyresampled(
        $newImage, $sourceImage,
        0, 0, 0, 0,
        $size, $size,
        $width, $height
    );
    
    imagepng($newImage, $output, 9);
    imagedestroy($newImage);
    imagedestroy($sourceImage);
}

/**
 * Create placeholder screenshot
 */
function createPlaceholderScreenshot($output, $width, $height) {
    if (extension_loaded('gd')) {
        $image = imagecreatetruecolor($width, $height);
        
        // Purple gradient background
        $purple = imagecolorallocate($image, 147, 51, 234);
        imagefilledrectangle($image, 0, 0, $width, $height, $purple);
        
        // White text
        $white = imagecolorallocate($image, 255, 255, 255);
        $text = "Replace with actual screenshot";
        $fontSize = 5;
        $textWidth = imagefontwidth($fontSize) * strlen($text);
        $textHeight = imagefontheight($fontSize);
        $x = ($width - $textWidth) / 2;
        $y = ($height - $textHeight) / 2;
        imagestring($image, $fontSize, $x, $y, $text, $white);
        
        imagepng($image, $output);
        imagedestroy($image);
        echo "✓ Generated placeholder: " . basename($output) . "\n";
    }
}

