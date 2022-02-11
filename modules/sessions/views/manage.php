<h1><?= $headline ?></h1>
<?php
flashdata();
echo '<p>'.anchor('sessions/create', 'Create New Session Record', array("class" => "button")).'</p>'; 
echo Pagination::display($pagination_data);
if (count($rows)>0) { ?>
    <table id="results-tbl">
        <thead>
            <tr>
                <th colspan="8">
                    <div>
                        <div><?php
                        echo form_open('sessions/manage/1/', array("method" => "get"));
                        echo form_input('searchphrase', '', array("placeholder" => "Search records..."));
                        echo form_submit('submit', 'Search', array("class" => "alt"));
                        echo form_close();
                        ?></div>
                        <div>Records Per Page: <?php
                        $dropdown_attr['onchange'] = 'setPerPage()';
                        echo form_dropdown('per_page', $per_page_options, $selected_per_page, $dropdown_attr); 
                        ?></div>

                    </div>                    
                </th>
            </tr>
            <tr>
                <th>IP Address</th>
                <th>User Agent</th>
                <th>Last Call</th>
                <th>Games Played</th>
                <th>Games Won</th>
                <th>Current Streak</th>
                <th>Max Streak</th>
                <th style="width: 20px;">Action</th>            
            </tr>
        </thead>
        <tbody>
            <?php 
            $attr['class'] = 'button alt';
            foreach($rows as $row) { ?>
            <tr>
                <td><?= $row->ip_address ?></td>
                <td><?= $row->user_agent ?></td>
                <td><?= $row->last_call ?></td>
                <td><?= $row->games_played ?></td>
                <td><?= $row->games_won ?></td>
                <td><?= $row->current_streak ?></td>
                <td><?= $row->max_streak ?></td>
                <td><?= anchor('sessions/show/'.$row->id, 'View', $attr) ?></td>        
            </tr>
            <?php
            }
            ?>
        </tbody>
    </table>
<?php 
    if(count($rows)>9) {
        unset($pagination_data['include_showing_statement']);
        echo Pagination::display($pagination_data);
    }
}
?>