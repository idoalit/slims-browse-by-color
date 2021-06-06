<?php
/**
 * @Created by          : Waris Agung Widodo (ido.alit@gmail.com)
 * @Date                : 05/06/2021 10:36
 * @File name           : opac_browse_by_color.php
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
use SLiMS\DB;

// require result list template
require_once LIB . 'biblio_list_model.inc.php';
require_once LIB . 'biblio_list.inc.php';
require_once SB . $sysconf['template']['dir'] . '/' . $sysconf['template']['theme'] . '/biblio_list_template.php';
require_once SB . $sysconf['template']['dir'] . '/' . $sysconf['template']['theme'] . '/custom_frontpage_record.inc.php';
require_once SIMBIO . 'simbio_GUI/paging/simbio_paging.inc.php';

$page_title = '🎨 Browse by Color';
$color = utility::filterData('color', 'get', true, true, true);
$criteria = '';
if ($color !== '') $criteria = " AND bc.color_dominant LIKE '%" . $color . "%'";

$page = intval($_GET['page'] ?? 1);
$offset = ($page - 1) * 10;

$sql = <<<SQL
SELECT biblio.biblio_id, biblio.title, biblio.image, biblio.isbn_issn, biblio.publish_year, 
                           pbl.publisher_name AS `publisher`, pplc.place_name AS `publish_place`, biblio.labels, 
                           biblio.input_date, biblio.edition, biblio.collation, biblio.series_title, biblio.call_number,
                           bc.color_dominant
FROM biblio LEFT JOIN mst_publisher AS pbl ON biblio.publisher_id=pbl.publisher_id 
    LEFT JOIN mst_place AS pplc ON biblio.publish_place_id=pplc.place_id 
    LEFT JOIN biblio_custom bc on biblio.biblio_id = bc.biblio_id
WHERE opac_hide=0 {$criteria} ORDER BY biblio.last_update DESC LIMIT {$offset}, 10
SQL;

$sql_count = <<<SQL
SELECT COUNT(*) FROM biblio b LEFT JOIN biblio_custom bc on b.biblio_id = bc.biblio_id WHERE opac_hide=0 {$criteria}
SQL;

$query = DB::getInstance('mysqli')->query($sql);
$query_count = DB::getInstance('mysqli')->query($sql_count);
$data_count = $query_count->fetch_row();
$paging = '';
if ($data_count[0] > 10) {
    $paging = '<div class="biblioPaging">';
    $paging .= simbio_paging::paging($data_count[0], 10, 5);
    $paging .= '</div>';
}

$filters = [
    'white' => ['title' => 'White', 'color' => 'rgb(255, 255, 255)'],
    'black' => ['title' => 'Black', 'color' => 'rgb(77, 77, 77)'],
    'yellow' => ['title' => 'Yellow', 'color' => 'rgb(252, 220, 0)'],
    'orange' => ['title' => 'Orange', 'color' => 'rgb(254, 146, 0)'],
    'red' => ['title' => 'Red', 'color' => 'rgb(219, 27, 13)'],
    'purple' => ['title' => 'Purple', 'color' => 'rgb(123, 100, 255)'],
    'magenta' => ['title' => 'Magenta', 'color' => 'rgb(171, 20, 158)'],
    'green' => ['title' => 'Green', 'color' => 'rgb(164, 221, 0)'],
    'teal' => ['title' => 'Teal', 'color' => 'rgb(104, 204, 202)'],
    'blue' => ['title' => 'Blue', 'color' => 'rgb(0, 156, 224)']
];

?>
<div class="row">
    <div class="col-md-8 order-2 order-md-1">
        <?php
        echo $paging;
        $n = 0;
        while ($data = $query->fetch_assoc()) {
            echo biblio_list_format(DB::getInstance('mysqli'), $data, $n, ['keywords' => '', 'enable_custom_frontpage' => true, 'custom_fields' => $custom_fields]);
            $n++;
        }
        if ($n < 1) echo '<div class="alert alert-warning">Oops..., no matching colors found!</div>';
        echo $paging;
        ?>
    </div>
    <div class="col-md-4 order-1 order-md-2">
        <div class="jumbotron py-3">
            <div class="d-flex flex-row justify-content-start align-items-center">
                <div><a title="All"
                        class="text-decoration-none d-flex flex-row justify-content-center align-items-center"
                        style="height: 20px; margin-right: 8px" href="index.php?p=browse_by_color">🎨</a></div>
                <?php
                foreach ($filters as $key => $filter) {
                    $link = BiblioColour::self(['color' => $key]);
                    $check = $color === $key ? '✔' : '';
                    $text_color = $key === 'black' ? 'white' : 'black';
                    echo '<div><a class="shadow-sm d-flex flex-row justify-content-center align-items-center" href="' . $link . '" style="color: '.$text_color.';text-decoration: none; text-align: center; display: inline-block; width: 20px; height: 20px; border-radius: 50px; background-color: ' . $filter['color'] . '; margin-right: 7px" title="' . $filter['title'] . '">' . $check . '</a></div>';
                }
                ?>
            </div>
        </div>
    </div>
</div>