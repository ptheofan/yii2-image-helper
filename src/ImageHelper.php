<?php
namespace ptheofan\helpers;

use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Imagine\Image\ManipulatorInterface;
use Imagine\Image\Palette\RGB;
use Imagine\Image\Point;
use yii\base\InvalidConfigException;
use yii\imagine\BaseImage;

class ImageHelper extends BaseImage
{
    /**
     * @param string|ImageInterface $image
     * @param string $color
     * @param int $alpha
     * @return ImageInterface
     * @throws InvalidConfigException
     */
    public static function makeOpaque($image, $color = '#fff', $alpha = 100): ImageInterface
    {
        if (!$image instanceof ImageInterface) {
            $image = static::getImagine()->load($image);
        }

        $topLeft = new Point(0, 0);
        $palette = new RGB();
        $background = $palette->color($color, $alpha);
        $imagine = self::createImagine();
        $img = $imagine->create($image->getSize(), $background);
        $img->paste($image, $topLeft);

        return $img;
    }

    /**
     * If $image is ImageInterface it will generate a copy. Otherwise it will load the image data into a new ImageInterface
     * @param $image
     * @return ImageInterface
     */
    public static function makeInstance($image): ImageInterface
    {
        if ($image instanceof ImageInterface) {
            $img = $image->copy();
        } else {
            // Load the image
            $img = static::getImagine()->load($image);
        }

        return $img;
    }

    /**
     * @param string|ImageInterface $image
     * @param $w
     * @param $h
     * @return ImageInterface
     */
    public static function smartResize($image, $w, $h = null): ImageInterface
    {
        if ($image instanceof ImageInterface) {
            $img = $image->copy();
        } else {
            // Load the image
            $img = static::getImagine()->load($image);
        }

        $imgSz = $img->getSize();

        if ($w !== null && $h === null)
        {
            $ratio = $imgSz->getHeight() / $imgSz->getWidth();
            $box = new Box($w, $w * $ratio);
        }
        elseif ($w === null && $h !== null)
        {
            $ratio = $imgSz->getWidth() / $imgSz->getHeight();
            $box = new Box($h * $ratio, $h);
        }
        else
        {
            $box = new Box($w, $h);
        }

        $img->resize($box, ImageInterface::FILTER_LANCZOS);
        return $img;
    }

    /**
     * Creates a thumbnail image. The function differs from `\Imagine\Image\ImageInterface::thumbnail()` function that
     * it keeps the aspect ratio of the image.
     * @param string|ImageInterface $image - binary string with image contents
     * @param integer $width the width in pixels to create the thumbnail
     * @param integer $height the height in pixels to create the thumbnail
     * @param string $mode
     * @return ImageInterface
     */
    public static function thumbnailFromImage($image, $width, $height, $mode = ManipulatorInterface::THUMBNAIL_OUTBOUND): ImageInterface
    {
        $box = new Box($width, $height);

        if ($image instanceof ImageInterface) {
            $img = $image->copy();
        } else {
            $img = static::getImagine()->load($image);
        }

        if (($img->getSize()->getWidth() <= $box->getWidth() && $img->getSize()->getHeight() <= $box->getHeight()) || (!$box->getWidth() && !$box->getHeight())) {
            return $img->copy();
        }

        $img = $img->thumbnail($box, $mode);

        // create empty image to preserve aspect ratio of thumbnail

        $thumb = static::getImagine()->create($box, (new RGB())->color('FFF', 100));

        // calculate points
        $size = $img->getSize();

        $startX = 0;
        $startY = 0;
        if ($size->getWidth() < $width) {
            $startX = ceil($width - $size->getWidth()) / 2;
        }
        if ($size->getHeight() < $height) {
            $startY = ceil($height - $size->getHeight()) / 2;
        }

        $thumb->paste($img, new Point($startX, $startY));

        return $thumb;
    }
}
