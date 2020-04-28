<?php


namespace splitbrain\slika;


class ImageMagickAdapter
{

    /**
     * resize images using external ImageMagick convert program
     *
     * @author Pavel Vitis <Pavel.Vitis@seznam.cz>
     * @author Andreas Gohr <andi@splitbrain.org>
     *
     * @param string $ext     extension
     * @param string $from    filename path to file
     * @param int    $from_w  original width
     * @param int    $from_h  original height
     * @param string $to      path to resized file
     * @param int    $to_w    desired width
     * @param int    $to_h    desired height
     * @return bool
     */
    function media_resize_imageIM($ext,$from,$from_w,$from_h,$to,$to_w,$to_h){
        global $conf;

        // check if convert is configured
        if(!$conf['im_convert']) return false;

        // prepare command
        $cmd  = $conf['im_convert'];
        $cmd .= ' -resize '.$to_w.'x'.$to_h.'!';
        if ($ext == 'jpg' || $ext == 'jpeg') {
            $cmd .= ' -quality '.$conf['jpg_quality'];
        }
        $cmd .= " $from $to";

        @exec($cmd,$out,$retval);
        if ($retval == 0) return true;
        return false;
    }


    /**
     * crop images using external ImageMagick convert program
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     *
     * @param string $ext     extension
     * @param string $from    filename path to file
     * @param int    $from_w  original width
     * @param int    $from_h  original height
     * @param string $to      path to resized file
     * @param int    $to_w    desired width
     * @param int    $to_h    desired height
     * @param int    $ofs_x   offset of crop centre
     * @param int    $ofs_y   offset of crop centre
     * @return bool
     */
    function media_crop_imageIM($ext,$from,$from_w,$from_h,$to,$to_w,$to_h,$ofs_x,$ofs_y){
        global $conf;

        // check if convert is configured
        if(!$conf['im_convert']) return false;

        // prepare command
        $cmd  = $conf['im_convert'];
        $cmd .= ' -crop '.$to_w.'x'.$to_h.'+'.$ofs_x.'+'.$ofs_y;
        if ($ext == 'jpg' || $ext == 'jpeg') {
            $cmd .= ' -quality '.$conf['jpg_quality'];
        }
        $cmd .= " $from $to";

        @exec($cmd,$out,$retval);
        if ($retval == 0) return true;
        return false;
    }

}
