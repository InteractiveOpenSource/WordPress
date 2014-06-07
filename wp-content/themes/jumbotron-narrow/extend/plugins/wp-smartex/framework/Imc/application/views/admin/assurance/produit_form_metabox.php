<div>
    <table class="junten-table-fields">

        <?php foreach($fields as $field_name => $field): ?>
            <tr>
                <th><?php echo Junten::label($field_name, $field['title']); ?></th>
                <td>
                    <?php
                    if(isset($field['_type']) && $field['_type'] == 'select'):
                        $select_option = (is_callable($field['_options'])) ? call_user_func($field['_options']) : $field['_options'];
                        echo Helper_Form::select($field_name, $select_option, $prefills->{$field_name}, $field["html"]);
                    elseif(isset($field['type']) && $field['type'] == 'bool'):
                        echo Helper_Form::radio($field_name, array('Oui', 'Non'), $prefills->{$field_name}, $field["html"]);
                    else:
                        echo Junten::input($field_name, $prefills->{$field_name}, $field["html"]);
                    endif
                    ?>
                </td>
            </tr>
        <?php endforeach ?>
        <tr>
            <th><?php echo $hiddens; ?></th>
        </tr>
    </table>
</div>