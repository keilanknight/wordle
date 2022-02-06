<h1><?= $headline ?> <span class="smaller hide-sm">(Record ID: <?= $update_id ?>)</span></h1>
<?= flashdata() ?>
<div class="card">
    <div class="card-heading">
        Options
    </div>
    <div class="card-body">
        <?php 
        echo anchor('sessions/manage', 'View All Sessions', array("class" => "button alt"));
        echo anchor('sessions/create/'.$update_id, 'Update Details', array("class" => "button"));
        $attr_delete = array( 
            "class" => "danger go-right",
            "id" => "btn-delete-modal",
            "onclick" => "openModal('delete-modal')"
        );
        echo form_button('delete', 'Delete', $attr_delete);
        ?>
    </div>
</div>
<div class="two-col">
    <div class="card">
        <div class="card-heading">
            Session Details
        </div>
        <div class="card-body">
            <div class="record-details">
                <div class="row">
                    <div>IP Address</div>
                    <div><?= $ip_address ?></div>
                </div>
                <div class="row">
                    <div>User Agent</div>
                    <div><?= $user_agent ?></div>
                </div>
                <div class="row">
                    <div>Session ID</div>
                    <div><?= $token ?></div>
                </div>
                <div class="row">
                    <div>Games Played</div>
                    <div><?= $games_played ?></div>
                </div>
                <div class="row">
                    <div>Games Won</div>
                    <div><?= $games_won ?></div>
                </div>
                <div class="row">
                    <div>Current Streak</div>
                    <div><?= $current_streak ?></div>
                </div>
                <div class="row">
                    <div>Max Streak</div>
                    <div><?= $max_streak ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-heading">
            Comments
        </div>
        <div class="card-body">
            <div class="text-center">
                <p><button class="alt" onclick="openModal('comment-modal')">Add New Comment</button></p>
                <div id="comments-block"><table></table></div>
            </div>
        </div>
    </div>
</div>
<div class="modal" id="comment-modal" style="display: none;">
    <div class="modal-heading"><i class="fa fa-commenting-o"></i> Add New Comment</div>
    <div class="modal-body">
        <p><textarea placeholder="Enter comment here..."></textarea></p>
        <p><?php
            $attr_close = array( 
                "class" => "alt",
                "onclick" => "closeModal()"
            );
            echo form_button('close', 'Cancel', $attr_close);
            echo form_button('submit', 'Submit Comment', array("onclick" => "submitComment()"));
            ?>
        </p>
    </div>
</div>
<div class="modal" id="delete-modal" style="display: none;">
    <div class="modal-heading danger"><i class="fa fa-trash"></i> Delete Record</div>
    <div class="modal-body">
        <?= form_open('sessions/submit_delete/'.$update_id) ?>
        <p>Are you sure?</p>
        <p>You are about to delete a session record.  This cannot be undone.  Do you really want to do this?</p> 
        <?php 
        echo '<p>'.form_button('close', 'Cancel', $attr_close);
        echo form_submit('submit', 'Yes - Delete Now', array("class" => 'danger')).'</p>';
        echo form_close();
        ?>
    </div>
</div>
<script>
var token = '<?= $token ?>';
var baseUrl = '<?= BASE_URL ?>';
var segment1 = '<?= segment(1) ?>';
var updateId = '<?= $update_id ?>';
var drawComments = true;
</script>