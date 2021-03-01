<?php 
defined('ABSPATH') or die('No no no');
if (!current_user_can('administrator')) {
    wp_die(__('Sorry, you are not allowed to manage options for this site.'));
}
?>

<div class="sa-edition-zone">
    <table class="form-table" role="presentation">
        <tbody>
            <?php 
            foreach ($sa_custom_fields as $key => $type) {
                ?>

                <tr>
                    <th scope="row">
                        <label for="<?= $key ?>"><?php
                        
                        $title = ucfirst($key);
                        $title = str_replace('_', ' ', $title);
                        echo $title;

                        ?></label>
                    </th>
                    <td>
                        <input 
                        name="<?= $key ?>" 
                        type="text" 
                        id="<?= $key ?>"
                        value="<?= $$key ?>"
                        class="regular-text">
                    </td>
                </tr>

                <?php
            }
            ?>
        </tbody>
    </table>
</div>
