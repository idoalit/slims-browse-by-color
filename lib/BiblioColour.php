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

    static function saveColor($color, $biblio_id) {
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
            $save = DB::getInstance('mysqli')->query("UPDATE `biblio_custom` SET `color_palette` = '".$palette."', `color_dominant` = '".$dominant."' WHERE `biblio_id` = '".$biblio_id."';");
        } else {
            $save = DB::getInstance('mysqli')->query("INSERT INTO `biblio_custom` (`biblio_id`, `color_palette`, `color_dominant`) VALUES ('".$biblio_id."', '".$palette."', '".$dominant."');");
        }

        return $save;
    }

    static function extractSaveById($biblio_id) {
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
        list($red, $green, $blue) = $rgb;

        #white
        if ($red > 240 && $green > 240 && $blue > 240) return 'white';
        #black
        if (($red < 82 && $green < 82 && $blue < 82) || (abs($red - $green) < 10 && abs($green - $blue) < 10)) return 'black';
        #yellow
        if ($blue < $red && $blue < $green && abs($red - $green) < 10 && $red >= 160 && $green >= 160 && $blue < 100) return 'yellow';
        #orange
        if ($red > $green && $green > $blue && $red > 200 && $green > 126 && $blue < 20) return 'orange';
        #red
        if ($red > $green && $red > $blue && $red > 150 && $green < 70 && $blue < 70) return 'red';
        #purple
        if ($blue > $red && $red > $green) return 'purple';
        #magenta
        if (abs($blue - $red) < 10 && $green < $red) return 'magenta';
        #green
        if ($green > $red && $green > $blue) return 'green';
        #teal
        if (abs($blue - $green) < 10 && $red < $blue) return 'teal';
        #blue
        if ($blue > $green && $blue > $red && $blue > 150) return 'blue';

        # uncategorized
        return null;
    }
}