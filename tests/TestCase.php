<?php /** @noinspection PhpComposerExtensionStubsInspection */

namespace splitbrain\slika\tests;


class TestCase extends \PHPUnit\Framework\TestCase
{

    protected $tempdir;

    /**
     * Create temporary directory
     */
    protected function setUp()
    {
        parent::setUp();
        $this->tempdir = sys_get_temp_dir() . '/slika';
        if (!is_dir($this->tempdir)) {
            mkdir($this->tempdir, 0777, true);
        }
    }

    /**
     * Clean up temporary directory
     */
    protected function tearDown()
    {
        parent::tearDown();
        $this->rrmdir($this->tempdir);
    }


    /**
     * Check that the given image has the proper dimension
     *
     * @param string $path
     * @param int $width
     * @param int $height
     */
    public function assertSize($path, $width, $height)
    {
        $this->assertFileExists($path);
        $info = getimagesize($path);
        $this->assertSame($width, $info[0], 'Unexpected image width');
        $this->assertSame($height, $info[1], 'Unexpecetd image height');
    }

    /**
     * Check that the given image has the proper color at the given position
     *
     * @param string $path
     * @param string $position
     * @param string $color
     * @throws \Exception
     */
    public function assertColor($path, $position, $color)
    {
        $this->assertFileExists($path);
        $found = $this->getColor($path, $position);
        $wanted = $this->resolveColor($color);

        // allow a difference of 2 per channel
        $this->assertLessThan(2, abs($wanted[0] - $found['red']), 'Color difference for RED channel too high');
        $this->assertLessThan(2, abs($wanted[1] - $found['green']), 'Color difference for GREEN channel too high');
        $this->assertLessThan(2, abs($wanted[2] - $found['blue']), 'Color difference for BLUE channel too high');
    }

    /**
     * Check that the given image has the proper alpha value at the given position
     *
     * @param string $path
     * @param string $position
     * @param string $color
     * @throws \Exception
     */
    public function assertAlpha($path, $position, $value)
    {
        $this->assertFileExists($path);
        $found = $this->getColor($path, $position);

        // allow a difference of 20
        $this->assertLessThan(20, abs($value - $found['alpha']), 'Color difference for ALPHA channel too high');
    }

    /**
     * Get color info for given position
     *
     * @param string $path
     * @param string $position
     * @return array
     * @throws \Exception
     */
    public function getColor($path, $position)
    {
        $info = getimagesize($path);
        $ext = image_type_to_extension($info[2], false);
        $opener = 'imagecreatefrom' . $ext;

        list($x, $y) = $this->resolvePosition($info[0], $info[1], $position);

        $image = $opener($path);
        $rgb = imagecolorat($image, $x, $y);
        $found =  imagecolorsforindex($image, $rgb);
        imagedestroy($image);
        return $found;
    }

    /**
     * Calculate pixel position based on gitven position description
     *
     * @param int $w
     * @param int $h
     * @param string $pos
     * @return array
     * @throws \Exception
     */
    protected function resolvePosition($w, $h, $pos)
    {
        switch ($pos) {
            case 'top':
                return [round($w / 2), 1];
            case 'right':
                return [$w - 1, round($h / 2)];
            case 'bottom':
                return [round($w / 2), $h - 1];
            case 'left':
                return [1, round($h / 2)];
            case 'center':
                return [round($w / 2), round($h / 2)];
            default:
                throw new \Exception('unknown position given');
        }
    }

    /**
     * Return the RGB values of the given color name
     *
     * @param string $name
     * @return array (r,g,b)
     * @throws \Exception
     */
    protected function resolveColor($name)
    {
        $colors = [
            'red' => [255, 0, 0],
            'green' => [0, 255, 0],
            'blue' => [0, 0, 255],
            'yellow' => [255, 255, 0],
            'pink' => [255, 0, 255],
            'white' => [255, 255, 255],
            'black' => [0, 0, 0]
        ];
        if (isset($colors[$name])) {
            return $colors[$name];
        }

        throw new \Exception('Unknown color name');
    }

    /**
     * Recursive deletion
     *
     * @param string $dir
     * @link https://stackoverflow.com/a/3338133
     */
    public function rrmdir($dir)
    {
        if (!is_dir($dir)) {
            return;
        }
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != '.' && $object != '..') {
                $path = $dir . '/' . $object;
                if (is_dir($path) && !is_link($path)) {
                    $this->rrmdir($path);
                } else {
                    unlink($path);
                }
            }
        }
        rmdir($dir);
    }

}
