<?php
/**
 * Plugin Name: Browse by Color
 * Plugin URI: https://github.com/idoalit/slims-browse-by-color
 * Description: Group bibliography collection by color of image cover
 * Version: 1.2.0
 * Author: Waris Agung Widodo
 * Author URI: https://github.com/idoalit
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

use Lib\BiblioColour;
use SLiMS\Plugins;

/**
 * Before of all, we need get plugin instance
 * and composer autoload
 */
require_once __DIR__ . '/lib/autoload.php';
$plugin = Plugins::getInstance();

/**
 * For new bibliography
 * we will extract color of image after bibliography saved successful
 */
$plugin->register('bibliography_after_save', function ($biblio) {
    BiblioColour::extractSaveById($biblio['biblio_id']);
});


/**
 * For old bibliography
 * we will extract color of image after bibliography updated
 */
$plugin->register('bibliography_after_update', function ($biblio) {
    BiblioColour::extractSaveById($biblio['biblio_id']);
});

/**
 * For manage extraction
 * we will register a menu for it
 */
$plugin->registerMenu('bibliography', 'Color Extraction', __DIR__ . '/bibliography_color_extraction.php');

/**
 * We need custom page in OPAC for browsing bibliography by color
 */
$plugin->registerMenu('opac', 'Browse by Color', __DIR__ . '/opac_browse_by_color.php');