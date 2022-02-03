<?php
class Sessions extends Trongate {

    private $default_limit = 20;
    private $per_page_options = array(10, 20, 50, 100);    

    function _create_id()
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        return sha1($ip.$user_agent);
    }

    

    function create() {
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
            $data['headline'] = 'Update Session Record';
            $data['cancel_url'] = BASE_URL.'sessions/show/'.$update_id;
        } else {
            $data['headline'] = 'Create New Session Record';
            $data['cancel_url'] = BASE_URL.'sessions/manage';
        }

        $data['form_location'] = BASE_URL.'sessions/submit/'.$update_id;
        $data['view_file'] = 'create';
        $this->template('admin', $data);
    }

    function manage() {
        $this->module('trongate_security');
        $this->trongate_security->_make_sure_allowed();

        if (segment(4) !== '') {
            $data['headline'] = 'Search Results';
            $searchphrase = trim($_GET['searchphrase']);
            $params['ip_address'] = '%'.$searchphrase.'%';
            $params['user_agent'] = '%'.$searchphrase.'%';
            $params['session_id'] = '%'.$searchphrase.'%';
            $params['current_streak'] = '%'.$searchphrase.'%';
            $sql = 'select * from sessions
            WHERE ip_address LIKE :ip_address
            OR user_agent LIKE :user_agent
            OR session_id LIKE :session_id
            OR current_streak LIKE :current_streak
            ORDER BY id';
            $all_rows = $this->model->query_bind($sql, $params, 'object');
        } else {
            $data['headline'] = 'Manage Sessions';
            $all_rows = $this->model->get('id');
        }

        $pagination_data['total_rows'] = count($all_rows);
        $pagination_data['page_num_segment'] = 3;
        $pagination_data['limit'] = $this->_get_limit();
        $pagination_data['pagination_root'] = 'sessions/manage';
        $pagination_data['record_name_plural'] = 'sessions';
        $pagination_data['include_showing_statement'] = true;
        $data['pagination_data'] = $pagination_data;

        $data['rows'] = $this->_reduce_rows($all_rows);
        $data['selected_per_page'] = $this->_get_selected_per_page();
        $data['per_page_options'] = $this->per_page_options;
        $data['view_module'] = 'sessions';
        $data['view_file'] = 'manage';
        $this->template('admin', $data);
    }

    function show() {
        $this->module('trongate_security');
        $token = $this->trongate_security->_make_sure_allowed();
        $update_id = segment(3);

        if ((!is_numeric($update_id)) && ($update_id != '')) {
            redirect('sessions/manage');
        }

        $data = $this->_get_data_from_db($update_id);
        $data['token'] = $token;

        if ($data == false) {
            redirect('sessions/manage');
        } else {
            $data['update_id'] = $update_id;
            $data['headline'] = 'Session Information';
            $data['view_file'] = 'show';
            $this->template('admin', $data);
        }
    }
    
    function _reduce_rows($all_rows) {
        $rows = [];
        $start_index = $this->_get_offset();
        $limit = $this->_get_limit();
        $end_index = $start_index + $limit;

        $count = -1;
        foreach ($all_rows as $row) {
            $count++;
            if (($count>=$start_index) && ($count<$end_index)) {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    function submit() {
        $this->module('trongate_security');
        $this->trongate_security->_make_sure_allowed();

        $submit = post('submit', true);

        if ($submit == 'Submit') {

            $this->validation_helper->set_rules('ip_address', 'IP Address', 'required|min_length[2]|max_length[255]');
            $this->validation_helper->set_rules('user_agent', 'User Agent', 'required|min_length[2]|max_length[255]');
            $this->validation_helper->set_rules('session_id', 'Session ID', 'required|min_length[2]|max_length[255]');
            $this->validation_helper->set_rules('games_played', 'Games Played', 'required|max_length[11]|numeric|greater_than[0]|integer');
            $this->validation_helper->set_rules('games_won', 'Games Won', 'required|max_length[11]|numeric|greater_than[0]|integer');
            $this->validation_helper->set_rules('current_streak', 'Current Streak', 'required|min_length[2]|max_length[255]');
            $this->validation_helper->set_rules('max_streak', 'Max Streak', 'required|max_length[11]|numeric|greater_than[0]|integer');

            $result = $this->validation_helper->run();

            if ($result == true) {

                $update_id = segment(3);
                $data = $this->_get_data_from_post();

                if (is_numeric($update_id)) {
                    //update an existing record
                    $this->model->update($update_id, $data, 'sessions');
                    $flash_msg = 'The record was successfully updated';
                } else {
                    //insert the new record
                    $update_id = $this->model->insert($data, 'sessions');
                    $flash_msg = 'The record was successfully created';
                }

                set_flashdata($flash_msg);
                redirect('sessions/show/'.$update_id);

            } else {
                //form submission error
                $this->create();
            }

        }

    }

    function submit_delete() {
        $this->module('trongate_security');
        $this->trongate_security->_make_sure_allowed();

        $submit = post('submit');
        $params['update_id'] = segment(3);

        if (($submit == 'Yes - Delete Now') && (is_numeric($params['update_id']))) {
            //delete all of the comments associated with this record
            $sql = 'delete from trongate_comments where target_table = :module and update_id = :update_id';
            $params['module'] = 'sessions';
            $this->model->query_bind($sql, $params);

            //delete the record
            $this->model->delete($params['update_id'], 'sessions');

            //set the flashdata
            $flash_msg = 'The record was successfully deleted';
            set_flashdata($flash_msg);

            //redirect to the manage page
            redirect('sessions/manage');
        }
    }

    function _get_limit() {
        if (isset($_SESSION['selected_per_page'])) {
            $limit = $this->per_page_options[$_SESSION['selected_per_page']];
        } else {
            $limit = $this->default_limit;
        }

        return $limit;
    }

    function _get_offset() {
        $page_num = segment(3);

        if (!is_numeric($page_num)) {
            $page_num = 0;
        }

        if ($page_num>1) {
            $offset = ($page_num-1)*$this->_get_limit();
        } else {
            $offset = 0;
        }

        return $offset;
    }

    function _get_selected_per_page() {
        if (!isset($_SESSION['selected_per_page'])) {
            $selected_per_page = $this->per_page_options[1];
        } else {
            $selected_per_page = $_SESSION['selected_per_page'];
        }

        return $selected_per_page;
    }

    function set_per_page($selected_index) {
        $this->module('trongate_security');
        $this->trongate_security->_make_sure_allowed();

        if (!is_numeric($selected_index)) {
            $selected_index = $this->per_page_options[1];
        }

        $_SESSION['selected_per_page'] = $selected_index;
        redirect('sessions/manage');
    }

    function _get_data_from_db($update_id) {
        $record_obj = $this->model->get_where($update_id, 'sessions');

        if ($record_obj == false) {
            $this->template('error_404');
            die();
        } else {
            $data = (array) $record_obj;
            return $data;        
        }
    }

    function _get_data_from_post() {
        $data['ip_address'] = post('ip_address', true);
        $data['user_agent'] = post('user_agent', true);
        $data['session_id'] = post('session_id', true);
        $data['games_played'] = post('games_played', true);
        $data['games_won'] = post('games_won', true);
        $data['current_streak'] = post('current_streak', true);
        $data['max_streak'] = post('max_streak', true);        
        return $data;
    }

}