<?php
/**
 * @Created by          : Waris Agung Widodo (ido.alit@gmail.com)
 * @Date                : 05/06/2021 11:09
 * @File name           : 1_AddColoursColumn.php
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

use SLiMS\DB;
use SLiMS\Migration\Migration;

class AddColoursColumn extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    function up()
    {
        DB::getInstance()->query("
            ALTER TABLE `biblio_custom`
                ADD `color_palette` text COLLATE 'utf8mb4_unicode_ci' NULL,
                ADD `color_dominant` varchar(255) COLLATE 'utf8mb4_unicode_ci' NULL AFTER `color_palette`;
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    function down()
    {
        DB::getInstance()->query("
            ALTER TABLE `biblio_custom`
                DROP `color_palette`,
                DROP `color_dominant`;
        ");
    }
}