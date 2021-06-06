<?php
/**
 * @Created by          : Waris Agung Widodo (ido.alit@gmail.com)
 * @Date                : 05/06/2021 15:10
 * @File name           : bibliography_color_reextract.php
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

// do extract
if (isset($_GET['extract']) && $_GET['extract'] === 'do-it') {
    // respond all request with json
    header('Content-type: application/json');

    $last_id = $_SESSION['extract']['last_id'] ?? 0;
    $query = DB::getInstance('mysqli')->query('SELECT biblio_id, title, image FROM biblio WHERE image IS NOT NULL AND biblio_id > '.$last_id.' ORDER BY biblio_id LIMIT 10');

    while ($data = $query->fetch_assoc()) {
        // get colors
        $color = BiblioColour::extract(IMGBS . 'docs/' . $data['image']);
        if (!is_null($color)) {
            // save to database
            BiblioColour::saveColor($color, $data['biblio_id']);
        }

        $_SESSION['extract']['last_id'] = $data['biblio_id'];
    }

    echo json_encode(['status' => 'success', 'message' => 'Extract from ID ' . $last_id . ' to ' . $_SESSION['extract']['last_id'], 'next' => $query->num_rows >= 10]);
    die();
}

// initialize extraction
if (isset($_GET['extract']) && $_GET['extract'] === 'init') {
    // respond all request with json
    header('Content-type: application/json');
    $query = DB::getInstance('mysqli')->query('SELECT COUNT(*) FROM biblio WHERE image IS NOT NULL');
    $data = $query->fetch_row();
    $_SESSION['extract'] = ['total_biblio' => $data[0]];
    echo json_encode(['status' => 'info', 'message' => 'Bibliography with image is ' . $data[0] . ' data', 'action' => 'next']);
    die();
}

?>
<div class="jumbotron jumbotron-fluid">
    <div class="container">
        <h1 class="display-4">🔥 Extractor</h1>
        <p class="lead">Please wait until task is finished!</p>
    </div>
</div>
<div class="container-fluid">
    <div id="console-log" class="card card-body bg-dark text-light mb-4" style="height: 50vh"></div>
</div>
<script>
    const container = $('#console-log')
    const uriInit = '<?= BiblioColour::self(['extract' => 'init']) ?>'
    const uriNext = '<?= BiblioColour::self(['extract' => 'do-it']) ?>'

    let doRequest = (url, callback) => {
        fetch(url)
            .then(res => res.json())
            .then(data => {
                console.log('DATA', data)
                log(container, data.status, data.message)
                if (data.next) {
                    log(container, 'info', 'Extracting next batch...')
                    doRequest(url)
                } else if(data.next === false) {
                    log(container, 'info', 'Extract DONE!')
                    log(container, 'info', '---------------END---------------')
                }
            })
            .catch(err => log(container, 'danger', err.message))
            .then(() => {
                if (typeof callback === "function") callback()
            })
    }

    $(document).ready(() => {
        log(container, 'info', 'Initialize extraction engine...')
        setTimeout(() => doRequest(uriInit, () => doRequest(uriNext)), 1000)
    })
</script>