<?php /** @noinspection PhpComposerExtensionStubsInspection */

namespace splitbrain\slika\tests;


class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * Get the path to where the current test may store its artefact
     *
     * @param string $ext the image extension to append
     * @return string
     */
    protected function artefact($ext)
    {
        $class = get_class($this);
        $class = substr($class, strrpos($class, '\\') + 1);
        $test = $this->getName();

        return __DIR__ . '/artefacts/' . $class . '-' . $test . '.' . $ext;
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

        $distance = abs($wanted[0] - $found['red']) +
            abs($wanted[1] - $found['green']) +
            abs($wanted[2] - $found['blue']);

        $this->assertLessThan(
            10, $distance,
            $path . "\n" .
            'Color (' . join(',', $found) . ') seems not be ' . $color . ' at position "' . $position . '"'
        );
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
        $this->assertLessThan(
            20, abs($value - $found['alpha']),
            $path . "\n" .
            'Color difference for ALPHA channel too high at position "' . $position . '"');
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
        $found = imagecolorsforindex($image, $rgb);
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

}
