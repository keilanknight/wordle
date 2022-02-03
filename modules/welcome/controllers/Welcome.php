<?php
class Welcome extends Trongate
{

    function index()
    {
        // $this->module("sessions");

        // /* Construct user token based on IP and useragent */
        // $token = $this->sessions->_create_id();

        // $session = $this->model->get_one_where("session_id", $token);

        // /* If no existing session, create a new one */ 
        // if(!$session)
        //     $session = $this->sessions->_create_new_session($token);

        $data['keyboard'] = [
            ["Q", "W", "E", "R", "T", "Y", "U", "I", "O", "P"],
            ["A", "S", "D", "F", "G", "H", "J", "K", "L"],
            ["Enter", "Z", "X", "C", "V", "B", "N", "M", "Del"]
        ];

        $this->view('welcome', $data);
    }
}
