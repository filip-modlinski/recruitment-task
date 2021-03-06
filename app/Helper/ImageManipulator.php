<?php


namespace App\Helper;


use Imagick;
use ImagickException;

/**
 * Class ImageManipulator
 * @package App\Helper
 */
class ImageManipulator
{
    /**
     * To be honest, this function has a few responsibilities, but I did it this way, because as You wrote:
     * "Code should follow SOLID principles, but not over complicated, be readable."
     *
     * @param string $base64Image
     * @param string $filename
     * @param float|null $angle
     * @param int $bgdColor
     * @return bool
     */
    public static function saveBase64Image(string $base64Image, string $filename, float $angle = null, int $bgdColor = 0): bool
    {
        $image = base64_decode($base64Image);
        if ($image === false) {
            return false;
        }
        $imageResource = imagecreatefromstring($image);
        if ($imageResource === false) {
            return false;
        }
        if (is_float($angle)) {
            $imageResource = imagerotate($imageResource, $angle, $bgdColor);
        }
        if ($imageResource === false) {
            return false;
        }

        return imagepng($imageResource, $filename);
    }

    /**
     * @param array $imagesFilenamesArray
     * @param string $pdfFilename
     * @return bool
     * @throws ImagickException
     */
    public static function mergeImagesToPdf(array $imagesFilenamesArray, string $pdfFilename): bool
    {
        $labelsPdf = new Imagick($imagesFilenamesArray);
        /**
         * To read and write pdf files it is necessary to change one line in /etc/ImageMagick-6/policy.xml
         * From: <policy domain="coder" rights="none "pattern="PDF" />
         * To: <policy domain="coder" rights="read|write "pattern="PDF" />
         */
        $labelsPdf->setImageFormat('pdf');
        return $labelsPdf->writeImages($pdfFilename, true);
    }
}
