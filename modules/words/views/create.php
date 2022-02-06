<h1><?= $headline ?></h1>
<?= validation_errors() ?>
<div class="card">
    <div class="card-heading">
        Word Details
    </div>
    <div class="card-body">
        <?php
        echo form_open($form_location);
        echo form_label('Word Date');
        $attr = array("class"=>"date-picker", "autocomplete"=>"off", "placeholder"=>"Select Word Date");
        echo form_input('word_date', $word_date, $attr);
        echo form_label('Word');
        echo form_input('word', $word, array("placeholder" => "Enter Word"));
        echo form_submit('submit', 'Submit');
        echo anchor($cancel_url, 'Cancel', array('class' => 'button alt'));
        echo form_close();
        ?>
    </div>
</div>