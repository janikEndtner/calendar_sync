<?php

include_once ("config.php");
include_once ("SHV_Api.php");
include_once ("Calendar.php");

echo __DIR__;

$api = new SHV_Api(AUTHORIZATION);
$teams = $api->getTeams(CLUB_NUMBER);

foreach ($teams as $team) {
    $groupText = $team->groupText;
    $teamName = substr($groupText, 0, strpos($groupText, "-"));
    $teamCalendar = new Calendar($teamName, $team->teamId);

    // get future games from SHV api
    $games = $api->getPlannedGames($team->teamId);
    $teamCalendar->setGames($games);
    $teamCalendar->save();

}