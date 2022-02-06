<h1><?= $headline ?></h1>
<?= validation_errors() ?>
<div class="card">
    <div class="card-heading">
        Session Details
    </div>
    <div class="card-body">
        <?php
        echo form_open($form_location);
        echo form_label('IP Address');
        echo form_input('ip_address', $ip_address, array("placeholder" => "Enter IP Address"));
        echo form_label('User Agent');
        echo form_input('user_agent', $user_agent, array("placeholder" => "Enter User Agent"));
        echo form_label('Session ID');
        echo form_input('token', $token, array("placeholder" => "Enter Token"));
        echo form_label('Games Played');
        echo form_number('games_played', $games_played, array("placeholder" => "Enter Games Played"));
        echo form_label('Games Won');
        echo form_number('games_won', $games_won, array("placeholder" => "Enter Games Won"));
        echo form_label('Current Streak');
        echo form_input('current_streak', $current_streak, array("placeholder" => "Enter Current Streak"));
        echo form_label('Max Streak');
        echo form_number('max_streak', $max_streak, array("placeholder" => "Enter Max Streak"));
        echo form_submit('submit', 'Submit');
        echo anchor($cancel_url, 'Cancel', array('class' => 'button alt'));
        echo form_close();
        ?>
    </div>
</div>