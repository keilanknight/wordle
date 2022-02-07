<?php
class Words extends Trongate
{
    private $default_limit = 20;
    private $per_page_options = array(10, 20, 50, 100);
    private $todays_word;
    private $word;
    private $result = [];

    function api()
    {
        /* Filter out GET requests */
        if ($_SERVER['REQUEST_METHOD'] == "GET") {
            redirect('wordle');
        }

        date_default_timezone_set('Europe/London');

        $this->_get_todays_word();

        $body = file_get_contents('php://input');
        $word = trim($body);
        $word = strip_tags($word);

        if (strlen($word) > 5 || strlen($word) < 5)
            return;

        $this->word = strtoupper($word);

        /* Validate word first */
        if ($this->_word_not_valid())
            return $this->_invalid_word();

        /* Determine if answer is correct */
        if ($this->word == $this->todays_word) {
            $this->module("sessions");
            $this->sessions->_win_game();
            $data['answer'] = "correct";
        } else {
            $this->module("sessions");
            $data['answer'] = "wrong";
            if ($this->sessions->_lose_game())
                $data['todays_word'] = $this->todays_word;
        }

        /* Prepare game logic for the display */
        $this->_get_result();

        $data['result'] = $this->result;

        header('Content-Type: application/json');
        echo json_encode($data);
    }

    function _word_not_valid()
    {
        $validate = $this->model->get_one_where("word", $this->word, "dictionaries");
        return (!$validate);
    }

    function _invalid_word()
    {
        for ($i = 0; $i < 5; $i++) {
            $result[] = ["style" => "wrong", "letter" => $this->word[$i]];
        }

        $data['answer'] = "invalid";
        $data['result'] = $result;
        $data['todays_word'] = $this->_get_todays_word();

        $this->module("sessions");
        $this->sessions->_reset_streak();

        header('Content-Type: application/json');
        echo json_encode($data);
    }

    function _get_result()
    {
        for ($i = 0; $i < 5; $i++) {
            $result[] = ["style" => "missing", "letter" => $this->word[$i]];
        }

        /* One pass for the matches */
        for ($i = 0; $i < 5; $i++) {
            if ($this->_letter_matches($i))
                $result[$i] = ["style" => "matches", "letter" => $this->word[$i]];
        }

        /* And a second pass for present letters */
        for ($i = 0; $i < 5; $i++) {
            if ($this->_letter_is_present($i))
                $result[$i] = ["style" => "present", "letter" => $this->word[$i]];
        }

        $this->result = $result;
    }

    function _letter_matches($i)
    {
        /* remove any matches so we don't get a false positive
           when searching the same letter in another position */
        if ($this->word[$i] == $this->todays_word[$i]) {
            $this->todays_word[$i] = " ";
            return true;
        }

        return false;
    }

    function _letter_is_present($i)
    {
        /* Check we haven't already matched this letter */
        if ($this->todays_word[$i] == " ")
            return false;

        $match_pos = strpos($this->todays_word, $this->word[$i]);

        if ($match_pos !== false) {
            /* remove any found letters so we don't count them twice */
            $this->todays_word[$match_pos] = " ";
            return true;
        }

        return false;
    }

    function _get_todays_word()
    {
        // $date = strtotime('today midnight');
        // $result = $this->model->get_one_where("word_date", date("Y-m-d", $date));
        $result = false;

        if (!$result) {
            /* Pick random word instead */
            $this->module("sessions");
            $token = $this->sessions->_create_id();

            $sql = "SELECT w.id, w.word FROM words AS w
                    INNER JOIN sessions AS s
                    ON w.id = s.current_word_id
                    WHERE s.token = '$token'";

            $result = $this->model->query($sql, "object");

            return $this->todays_word = strtoupper($result[0]->word);
        }

        $this->todays_word = strtoupper($result->word);
    }

    function create()
    {
        $this->module('trongate_security');
        $this->trongate_security->_make_sure_allowed();

        $update_id = segment(3);
        $submit = post('submit');

        if (($submit == '') && (is_numeric($update_id))) {
            $data = $this->_get_data_from_db($update_id);
        } else {
            $data = $this->_get_data_from_post();
        }

        if (is_numeric($update_id)) {
            $data['headline'] = 'Update Word Record';
            $data['cancel_url'] = BASE_URL . 'words/show/' . $update_id;
        } else {
            $data['headline'] = 'Create New Word Record';
            $data['cancel_url'] = BASE_URL . 'words/manage';
        }

        $data['multiple_words'] = false;
        $data['form_location'] = BASE_URL . 'words/submit/' . $update_id;
        $data['view_file'] = 'create';

        $this->template('admin', $data);
    }

    function create_words()
    {
        $this->module('trongate_security');
        $this->trongate_security->_make_sure_allowed();

        $data['headline'] = 'Add Multiple Words';
        $data['cancel_url'] = BASE_URL . 'words/manage';
        $data['form_location'] = BASE_URL . 'words/submit_words/';
        $data['view_file'] = 'create_words';

        $this->template('admin', $data);
    }

    function manage()
    {
        $this->module('trongate_security');
        $this->trongate_security->_make_sure_allowed();

        if (segment(4) !== '') {
            $data['headline'] = 'Search Results';
            $searchphrase = trim($_GET['searchphrase']);
            $params['word'] = '%' . $searchphrase . '%';
            $sql = 'select * from words
            WHERE word LIKE :word
            ORDER BY id';
            $all_rows = $this->model->query_bind($sql, $params, 'object');
        } else {
            $data['headline'] = 'Manage Word Of The Day';
            $all_rows = $this->model->get('id');
        }

        $pagination_data['total_rows'] = count($all_rows);
        $pagination_data['page_num_segment'] = 3;
        $pagination_data['limit'] = $this->_get_limit();
        $pagination_data['pagination_root'] = 'words/manage';
        $pagination_data['record_name_plural'] = 'words';
        $pagination_data['include_showing_statement'] = true;
        $data['pagination_data'] = $pagination_data;

        $data['rows'] = $this->_reduce_rows($all_rows);
        $data['selected_per_page'] = $this->_get_selected_per_page();
        $data['per_page_options'] = $this->per_page_options;
        $data['view_module'] = 'words';
        $data['view_file'] = 'manage';
        $this->template('admin', $data);
    }

    function show()
    {
        $this->module('trongate_security');
        $token = $this->trongate_security->_make_sure_allowed();
        $update_id = segment(3);

        if ((!is_numeric($update_id)) && ($update_id != '')) {
            redirect('words/manage');
        }

        $data = $this->_get_data_from_db($update_id);
        $data['token'] = $token;

        if ($data == false) {
            redirect('words/manage');
        } else {
            $data['update_id'] = $update_id;
            $data['headline'] = 'Word Information';
            $data['view_file'] = 'show';
            $this->template('admin', $data);
        }
    }

    function _reduce_rows($all_rows)
    {
        $rows = [];
        $start_index = $this->_get_offset();
        $limit = $this->_get_limit();
        $end_index = $start_index + $limit;

        $count = -1;
        foreach ($all_rows as $row) {
            $count++;
            if (($count >= $start_index) && ($count < $end_index)) {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    function submit()
    {
        $this->module('trongate_security');
        $this->trongate_security->_make_sure_allowed();

        $submit = post('submit', true);

        if ($submit == 'Submit') {

            $this->validation_helper->set_rules('word_date', 'Word Date', 'required|valid_datepicker_us');
            $this->validation_helper->set_rules('word', 'Word', 'required|min_length[5]|max_length[5]');

            $result = $this->validation_helper->run();

            if ($result == true) {

                $update_id = segment(3);
                $data = $this->_get_data_from_post();
                $data['word_date'] = date('Y-m-d', strtotime($data['word_date']));

                if (is_numeric($update_id)) {
                    //update an existing record
                    $this->model->update($update_id, $data, 'words');
                    $flash_msg = 'The record was successfully updated';
                } else {
                    //insert the new record
                    $update_id = $this->model->insert($data, 'words');
                    $flash_msg = 'The record was successfully created';
                }

                set_flashdata($flash_msg);
                redirect('words/show/' . $update_id);
            } else {
                //form submission error
                $this->create();
            }
        }
    }

    function submit_words()
    {
        $this->module('trongate_security');
        $this->trongate_security->_make_sure_allowed();

        $words  = explode(" ", post('words'));
        $values = "";

        foreach ($words as $word) {
            $values .= "('$word')";
            $values .= ($word != end($words)) ? "," : ";";
        }

        $sql = "INSERT INTO words (word) VALUES $values";

        $this->model->query($sql);

        $flash_msg = 'Words have been added into the word list';

        set_flashdata($flash_msg);
        redirect('words/manage');
    }

    function submit_delete()
    {
        $this->module('trongate_security');
        $this->trongate_security->_make_sure_allowed();

        $submit = post('submit');
        $params['update_id'] = segment(3);

        if (($submit == 'Yes - Delete Now') && (is_numeric($params['update_id']))) {
            //delete all of the comments associated with this record
            $sql = 'delete from trongate_comments where target_table = :module and update_id = :update_id';
            $params['module'] = 'words';
            $this->model->query_bind($sql, $params);

            //delete the record
            $this->model->delete($params['update_id'], 'words');

            //set the flashdata
            $flash_msg = 'The record was successfully deleted';
            set_flashdata($flash_msg);

            //redirect to the manage page
            redirect('words/manage');
        }
    }

    function _get_limit()
    {
        if (isset($_SESSION['selected_per_page'])) {
            $limit = $this->per_page_options[$_SESSION['selected_per_page']];
        } else {
            $limit = $this->default_limit;
        }

        return $limit;
    }

    function _get_offset()
    {
        $page_num = segment(3);

        if (!is_numeric($page_num)) {
            $page_num = 0;
        }

        if ($page_num > 1) {
            $offset = ($page_num - 1) * $this->_get_limit();
        } else {
            $offset = 0;
        }

        return $offset;
    }

    function _get_selected_per_page()
    {
        if (!isset($_SESSION['selected_per_page'])) {
            $selected_per_page = $this->per_page_options[1];
        } else {
            $selected_per_page = $_SESSION['selected_per_page'];
        }

        return $selected_per_page;
    }

    function set_per_page($selected_index)
    {
        $this->module('trongate_security');
        $this->trongate_security->_make_sure_allowed();

        if (!is_numeric($selected_index)) {
            $selected_index = $this->per_page_options[1];
        }

        $_SESSION['selected_per_page'] = $selected_index;
        redirect('words/manage');
    }

    function _get_data_from_db($update_id)
    {
        $record_obj = $this->model->get_where($update_id, 'words');

        if ($record_obj == false) {
            $this->template('error_404');
            die();
        } else {
            $data = (array) $record_obj;
            return $data;
        }
    }

    function _get_data_from_post()
    {
        $data['word_date'] = post('word_date', true);
        $data['word'] = post('word', true);
        return $data;
    }
}
