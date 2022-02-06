<h1><?= $headline ?></h1>
<?= validation_errors() ?>
<div class="card">
    <div class="card-heading">
        Enter a list of words, all in one line with a space between them.
    </div>
    <div class="card-body">
        <?php
        echo form_open($form_location);
        echo form_label('Words');
        echo form_textarea('words', $word, array("placeholder" => "Enter Words", "rows" => "20"));
        echo form_submit('submit', 'Submit');
        echo anchor($cancel_url, 'Cancel', array('class' => 'button alt'));
        echo form_close();
        ?>
    </div>
</div>