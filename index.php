<?php

include_once ("config.php");
include_once ("SHV_Api.php");
include_once ("Calendar.php");

echo __DIR__;

$api = new SHV_Api(AUTHORIZATION);
$teams = $api->getTeams(CLUB_NUMBER);

foreach ($teams as $team) {
    $teamCalendar = new Calendar($team->groupText, $team->teamId);

    // get future games from SHV api
    $games = $api->getPlannedGames($team->teamId);
    $teamCalendar->setGames($games);
    $teamCalendar->save();

}