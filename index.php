<?php

include_once ("config.php");
include_once ("SHV_Api.php");
include_once ("ApiClient.php");
include_once ("Calendar.php");

echo __DIR__;

$api = new SHV_Api(AUTHORIZATION);
$teams = $api->getTeams(CLUB_NUMBER);

// extract names of currently existing calendars
$currentCalendars = ApiClient::getAllCalendars();

foreach ($teams as $team) {
    $teamCalendar = null;
    foreach ($currentCalendars as $calendar) {
        if (strpos($calendar["description"], "TeamId: " . $team->teamId) !== false) {
            $teamCalendar = new Calendar($calendar);
            break;
        }
    }
    if ($teamCalendar == null) {
        $teamCalendar = new Calendar(ApiClient::makeCalendar($team->groupText, $team->teamId));
    }

    // get future games from SHV api
    $games = $api->getPlannedGames($team->teamId);
    $teamCalendar->setGames($games);
    $teamCalendar->adaptFutureGamesIfNecessary();

}