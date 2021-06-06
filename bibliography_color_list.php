<?php
/**
 * @Created by          : Waris Agung Widodo (ido.alit@gmail.com)
 * @Date                : 05/06/2021 15:10
 * @File name           : bibliography_color_list.php
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

require_once SIMBIO . 'simbio_GUI/paging/simbio_paging.inc.php';

$color = utility::filterData('color', 'get', true, true, true);
$criteria = '';
$limit = 12;
$page = intval($_GET['page'] ?? 1);
$offset = ($page - 1) * $limit;
if ($color !== '') $criteria = " where bc.color_dominant LIKE '%".$color."%'";
$query = DB::getInstance('mysqli')->query('select b.image, b.title, bc.color_dominant, bc.color_palette 
        from biblio b left join biblio_custom bc on b.biblio_id = bc.biblio_id' . $criteria . ' limit '.$offset.', ' . $limit);
$query_count = DB::getInstance('mysqli')->query('select count(*) from biblio b 
    left join biblio_custom bc on b.biblio_id = bc.biblio_id ' . $criteria);
$data_count = $query_count->fetch_row();
$paging = '';
if ($data_count > $limit) {
    $paging = '<div class="paging-area mb-4">';
    $paging .= simbio_paging::paging($data_count[0], $limit, 6);
    $paging .= '</div>';
}

$filters = [
    ['title' => 'White', 'color' => 'rgb(255, 255, 255)'],
    ['title' => 'Black', 'color' => 'rgb(77, 77, 77)'],
    ['title' => 'Yellow', 'color' => 'rgb(252, 220, 0)'],
    ['title' => 'Orange', 'color' => 'rgb(254, 146, 0)'],
    ['title' => 'Red', 'color' => 'rgb(244, 78, 59)'],
    ['title' => 'Purple', 'color' => 'rgb(123, 100, 255)'],
    ['title' => 'Magenta', 'color' => 'rgb(171, 20, 158)'],
    ['title' => 'Green', 'color' => 'rgb(164, 221, 0)'],
    ['title' => 'Teal', 'color' => 'rgb(104, 204, 202)'],
    ['title' => 'Blue', 'color' => 'rgb(0, 156, 224)']
];

?>
<div style="min-height: calc(100vh - 150px)">
    <div class="jumbotron jumbotron-fluid">
        <div class="container">
            <h1 class="display-4">📚 Bibliography List</h1>
            <hr>
            <div class="d-flex flex-row justify-content-start align-items-center pt-4">
                <div><a title="All"
                        class="text-decoration-none d-flex flex-row justify-content-center align-items-center"
                        style="height: 20px; margin-right: 8px" href="<?= BiblioColour::self([], ['color']) ?>">🎨</a></div>
            <?php
            foreach ($filters as $filter) {
                $link = BiblioColour::self(['color' => strtolower($filter['title'])]);
                $check = $color === strtolower($filter['title']) ? '✔' : '';
                echo '<a href="' . $link . '" style="text-align: center; display: inline-block; width: 20px; height: 20px; border-radius: 50%; background-color: ' . $filter['color'] . '; margin-right: 4px" title="' . $filter['title'] . '">'.$check.'</a>';
            }
            ?>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <?= $paging ?>
        <div class="row">
            <?php
            if ($query->num_rows < 1) {
                echo '<div class="col-md-12"><div class="alert alert-warning">Oops..., no matching colors found!</div></div>';
            }
            while ($data = $query->fetch_assoc()):
                ?>
                <div class="col-sm-3 mb-2">
                    <div class="card card-body d-flex flex-row">
                        <div style="width: 50px" class="flex-grow-0 flex-shrink-0">
                            <img src="<?= BiblioColour::image($data['image']) ?>" alt="cover" class="img-thumbnail">
                        </div>
                        <div class="flex-grow-1 pl-2 d-flex flex-column justify-content-between">
                            <h4 style="font-size: 11px"><?= $data['title'] ?></h4>
                            <div class="d-flex flex-row mt-2">
                                <?php
                                if (!is_null($data['color_palette'])) {
                                    $colours = unserialize($data['color_palette']);
                                    foreach ($colours as $colour) {
                                        echo '<div style="width: 30px; height: 10px; background-color: rgb(' . implode(', ', $colour) . '); margin: 0 2px 2px 0"></div>';
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>
