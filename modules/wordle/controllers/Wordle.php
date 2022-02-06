<?php
class Wordle extends Trongate
{
    function index()
    {
        $this->module("sessions");

        /* Construct user token based on IP and useragent */
        $token = $this->sessions->_create_id();

        $session = $this->model->get_one_where("token", $token, "sessions");

        /* If no existing session, create a new one */
        if (!$session)
            $session = $this->sessions->_create_new_session();

        $data['token'] = $token;

        /* If view file is loaded screen has been refreshed so pick a new word */
        $this->sessions->_new_word($token);

        $data['games'] = $session->games_played;
        $data['won'] = $session->games_won;
        $data['streak'] = $session->current_streak;
        $data['best'] = $session->max_streak;

        $data['keyboard'] = [
            ["Q", "W", "E", "R", "T", "Y", "U", "I", "O", "P"],
            ["A", "S", "D", "F", "G", "H", "J", "K", "L"],
            ["Enter", "Z", "X", "C", "V", "B", "N", "M", "Del"]
        ];

        $this->view('wordle', $data);
    }
}
