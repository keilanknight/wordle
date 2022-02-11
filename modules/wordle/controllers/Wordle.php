<?php
class Wordle extends Trongate
{
    function index()
    {
        $this->module("sessions");

        /* Log the time of this request */
        $this->sessions->_update_last_call();

        /* short hand reference to session */
        $session = &$this->sessions->session;

        /* If view file is loaded screen has been refreshed so pick a new word */
        $this->sessions->_new_word();

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
