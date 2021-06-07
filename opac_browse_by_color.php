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
use Lib\BiblioList;
use SLiMS\DB;

// require result list template
require_once LIB . 'biblio_list_model.inc.php';
require_once LIB . 'biblio_list.inc.php';
require_once SB . $sysconf['template']['dir'] . '/' . $sysconf['template']['theme'] . '/biblio_list_template.php';
require_once SB . $sysconf['template']['dir'] . '/' . $sysconf['template']['theme'] . '/custom_frontpage_record.inc.php';
require_once SIMBIO.'simbio_UTILS/simbio_tokenizecql.inc.php';
require_once SIMBIO . 'simbio_GUI/paging/simbio_paging.inc.php';

$page_title = '🎨 Browse by Color';
$biblio_list = new BiblioList(DB::getInstance('mysqli'), 10);

$criteria = '';
$keywords = trim(strip_tags(urldecode(utility::filterData('keywords'))));
if ($keywords && !preg_match('@[a-z0-9_.]+=[^=]+\s+@i', $keywords.' ')) {
    $criteria = 'title='.$keywords.' OR author='.$keywords.' OR subject='.$keywords;
    $biblio_list->setSQLcriteria($criteria);
} else {
    $biblio_list->setSQLcriteria($keywords);
}

$query = DB::getInstance('mysqli')->query($biblio_list->compileSQL());
$query_count = DB::getInstance('mysqli')->query($biblio_list->compileSQLCount());
$data_count = $query_count->fetch_row();
$paging = '';
if ($data_count[0] > 10) {
    $paging = '<div class="biblioPaging">';
    $paging .= simbio_paging::paging($data_count[0], 10, 5);
    $paging .= '</div>';
}

$keywords_info = '<span class="search-keyword-info" title="'.htmlentities($keywords).'">'.((strlen($keywords)>30)?substr($keywords, 0, 30).'...':$keywords).'</span>';
$search_result_info = __('Found <strong>{biblio_list->num_rows}</strong> from your keywords').': <strong class="search-found-info-keywords">'.$keywords_info.'</strong>';
// set result number info
$search_result_info = str_replace('{biblio_list->num_rows}', $data_count[0], $search_result_info);

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
        <form action="index.php">
            <?php
            foreach ($_GET as $k => $g) {
                if ($k !== 'keywords') {
                    echo '<input type="hidden" name="'.$k.'" value="'.$g.'" />';
                }
            }
            ?>
            <div class="input-group mb-3">
                <input type="text" class="form-control" placeholder="Enter keywords" name="keywords">
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary" type="submit" id="button-addon2">Search</button>
                </div>
            </div>
        </form>
        <div class="jumbotron py-3 mb-3">
            <div class="d-flex flex-row justify-content-start align-items-center">
                <div><a title="All"
                        class="text-decoration-none d-flex flex-row justify-content-center align-items-center"
                        style="height: 20px; margin-right: 8px" href="<?= BiblioColour::self([], ['color']) ?>">🎨</a></div>
                <?php
                $color = \utility::filterData('color', 'get', true, true, true);
                foreach ($filters as $key => $filter) {
                    $link = BiblioColour::self(['color' => $key]);
                    $check = $color === $key ? '✔' : '';
                    $text_color = $key === 'black' ? 'white' : 'black';
                    echo '<div><a class="shadow-sm d-flex flex-row justify-content-center align-items-center" href="' . $link . '" style="color: '.$text_color.';text-decoration: none; text-align: center; display: inline-block; width: 20px; height: 20px; border-radius: 50px; background-color: ' . $filter['color'] . '; margin-right: 7px" title="' . $filter['title'] . '">' . $check . '</a></div>';
                }
                ?>
            </div>
        </div>
        <div class="alert alert-info"><?= $search_result_info ?></div>
    </div>
</div>
<script>
    $(document).ready(function () {
        $('input[name="keywords"]').closest('.card-body').hide()
        $('body > div.result-search.pb-5 > section.container.mt-8').addClass('pt-3')
    })
</script>