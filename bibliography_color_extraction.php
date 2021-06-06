<?php
/**
 * @Created by          : Waris Agung Widodo (ido.alit@gmail.com)
 * @Date                : 05/06/2021 10:27
 * @File name           : bibliography_color_extraction.php
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

defined('INDEX_AUTH') OR die('Direct access not allowed!');

// IP based access limitation
require LIB . 'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-bibliography');
// start the session
require SB . 'admin/default/session.inc.php';

// privileges checking
$can_read = utility::havePrivilege('bibliography', 'r');

if (!$can_read) {
    die('<div class="errorBox">' . __('You are not authorized to view this section') . '</div>');
}

if (isset($_GET['action'])) {
    if ($_GET['action'] === 'list') {
        include_once 'bibliography_color_list.php';
        die();
    } elseif ($_GET['action'] === 're-extract') {
        include_once 'bibliography_color_reextract.php';
        die();
    }
}

?>

<div style="min-height: calc(100vh - 150px)">
    <div class="jumbotron jumbotron-fluid">
        <div class="container">
            <h1 class="display-4">🎨 Color Extraction</h1>
            <p class="lead">Extract color from image cover. Use it for grouping bibliography.</p>
        </div>
    </div>
    <div class="container-fluid">

        <div id="alert-new-version" class="alert alert-info border-0 mt-3 hidden">
            <strong>News!</strong> New version of this plugin (<code id="new_version"></code>)
            available to <a class="notAJAX" target="_blank"
                            href="https://github.com/slims/slims9_bulian/releases/latest">download</a>.
        </div>

        <div class="row">
            <div class="col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Show Bibliography List</h5>
                        <p class="card-text">Show bibliography list with color pallet</p>
                        <a href="<?= BiblioColour::self(['action' => 'list']) ?>" class="btn btn-primary">
                            Show Me
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Re-extract All Cover</h5>
                        <p class="card-text">Extract color from all image cover of bibliography</p>
                        <a href="<?= BiblioColour::self(['action' => 're-extract']) ?>" class="btn btn-primary">
                            Do it
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    <?php if ($_SESSION['uid'] === '1') : ?>
    // get lastest release
    fetch('https://api.github.com/repos/idoalit/slims-browse-by-color/releases/latest')
        .then(res => res.json())
        .then(res => {
            if (res.tag_name !== '1.0.0') {
                $('#new_version').text(res.tag_name);
                $('#alert-new-version').removeClass('hidden');
                $('#alert-new-version a').attr('href', res.html_url)
            }
        })
    <?php endif; ?>
</script>
