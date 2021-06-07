<?php
/**
 * @Created by          : Waris Agung Widodo (ido.alit@gmail.com)
 * @Date                : 05/06/2021 11:17
 * @File name           : BiblioColour.php
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 */

namespace Lib;

use ColorThief\ColorThief;
use SLiMS\DB;

class BiblioColour
{
    static function self($query = [], $remove = []): string
    {
        $qq = array_unique(array_merge($_GET, $query));
        foreach ($remove as $key) unset($qq[$key]);
        $query = http_build_query($qq);
        return $_SERVER['PHP_SELF'] . '?' . $query;
    }

    static function image($filename = null, $width = 80, $height = 100): string
    {
        if (!is_null($filename) || trim($filename) !== '') {
            return SWB . 'lib/minigalnano/createthumb.php?filename=../../images/docs/' . $filename . '&width=' . $width . '&height=' . $height;
        }
        return SWB . 'lib/minigalnano/createthumb.php?filename=../../images/default/image.png&width=' . $width . '&height=' . $height;
    }

    static function extract($imagePath): ?array
    {
        if (is_file($imagePath)) {
            $palette = ColorThief::getPalette($imagePath, 2);
            $dominantColor = ColorThief::getColor($imagePath);

            return ['dominant' => $dominantColor, 'palette' => $palette];
        }

        return null;
    }

    static function saveColor($color, $biblio_id)
    {
        // save to database
        $dominants = [];
        foreach ($color['palette'] as $item) {
            $dominant = BiblioColour::groupOf($item);
            if (!is_null($dominant)) $dominants[] = $dominant;
        }
        $dominant = implode(' ', array_unique($dominants));

        $palette = serialize($color['palette']);
        // check if data exist
        $_query = DB::getInstance('mysqli')->query('SELECT color_palette, color_dominant FROM biblio_custom WHERE biblio_id = ' . $biblio_id);
        if ($_query->num_rows > 0) {
            $save = DB::getInstance('mysqli')->query("UPDATE `biblio_custom` SET `color_palette` = '" . $palette . "', `color_dominant` = '" . $dominant . "' WHERE `biblio_id` = '" . $biblio_id . "';");
        } else {
            $save = DB::getInstance('mysqli')->query("INSERT INTO `biblio_custom` (`biblio_id`, `color_palette`, `color_dominant`) VALUES ('" . $biblio_id . "', '" . $palette . "', '" . $dominant . "');");
        }

        return $save;
    }

    static function extractSaveById($biblio_id)
    {
        $query = DB::getInstance('mysqli')->query('SELECT image FROM biblio WHERE biblio_id = ' . $biblio_id);
        $data = $query->fetch_assoc();
        // get colors
        $color = self::extract(IMGBS . 'docs/' . $data['image']);
        if (!is_null($color)) {
            // save to database
            self::saveColor($color, $biblio_id);
        }
    }

    static function groupOf($rgb): ?string
    {
        $hsv = self::rgb2hsv($rgb);
        $hue = $hsv[0];

        #white
        if ($hsv[1] <= 5 && $hsv[2] >= 95) return 'white';
        #black
        if ($hsv[2] <= 10) return 'black';
        #red
        if ($hue < 10 || $hue > 345) return 'red';
        #orange
        if ($hue < 40 && $hue > 10) return 'orange';
        #yellow
        if ($hue < 60 && $hue > 40) return 'yellow';
        #green
        if ($hue < 160 && $hue > 60 && $hsv[1] >= 30) return 'green';
        #teal
        if ($hue < 180 && $hue > 160) return 'teal';
        #blue
        if ($hue < 250 && $hue > 180) return 'blue';
        #purple
        if ($hue < 310 && $hue > 250) return 'yellow';
        #magenta
        if ($hue < 345 && $hue > 310) return 'yellow';

        # uncategorized
        return null;
    }

    /**
     * @source: https://stackoverflow.com/a/13887939
     */
    static function rgb2hsv($rgb)    // RGB values:    0-255, 0-255, 0-255
    {                                // HSV values:    0-360, 0-100, 0-100
        list($R, $G, $B) = $rgb;
        // Convert the RGB byte-values to percentages
        $R = ($R / 255);
        $G = ($G / 255);
        $B = ($B / 255);

        // Calculate a few basic values, the maximum value of R,G,B, the
        //   minimum value, and the difference of the two (chroma).
        $maxRGB = max($R, $G, $B);
        $minRGB = min($R, $G, $B);
        $chroma = $maxRGB - $minRGB;

        // Value (also called Brightness) is the easiest component to calculate,
        //   and is simply the highest value among the R,G,B components.
        // We multiply by 100 to turn the decimal into a readable percent value.
        $computedV = 100 * $maxRGB;

        // Special case if hueless (equal parts RGB make black, white, or grays)
        // Note that Hue is technically undefined when chroma is zero, as
        //   attempting to calculate it would cause division by zero (see
        //   below), so most applications simply substitute a Hue of zero.
        // Saturation will always be zero in this case, see below for details.
        if ($chroma == 0)
            return array(0, 0, $computedV);

        // Saturation is also simple to compute, and is simply the chroma
        //   over the Value (or Brightness)
        // Again, multiplied by 100 to get a percentage.
        $computedS = 100 * ($chroma / $maxRGB);

        // Calculate Hue component
        // Hue is calculated on the "chromacity plane", which is represented
        //   as a 2D hexagon, divided into six 60-degree sectors. We calculate
        //   the bisecting angle as a value 0 <= x < 6, that represents which
        //   portion of which sector the line falls on.
        if ($R == $minRGB)
            $h = 3 - (($G - $B) / $chroma);
        elseif ($B == $minRGB)
            $h = 1 - (($R - $G) / $chroma);
        else // $G == $minRGB
            $h = 5 - (($B - $R) / $chroma);

        // After we have the sector position, we multiply it by the size of
        //   each sector's arc (60 degrees) to obtain the angle in degrees.
        $computedH = 60 * $h;

        return array($computedH, $computedS, $computedV);
    }
}