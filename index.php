<?php

include_once ("config.php");
include_once ("SHV_Api.php");
include_once ("Calendar.php");

echo __DIR__;

$api = new SHV_Api(AUTHORIZATION);
$teams = $api->getTeams(CLUB_NUMBER);

// ewige kalender: z.B. M2-06 wird unter M2 gespeichert, so dass dieses file nächsten Jahr gleich heisst
// und nicht neu abboniert werden muss
foreach ($teams as $team) {
    $groupText = $team->groupText;
    $teamName = substr($groupText, 0, strpos($groupText, "-"));
    createCalendarForTeam($team, $teamName, $api);
}

// genaue kalender: z.B. M2-06 wird unter M2-06 gespeichert. Falls die Gruppe wechselt, muss der Kalender
// im nächsten Jahr neu abboniert werden. Kann insbesondere dann verwendet werden, wenn mehrere Teams gleich heissen
foreach ($teams as $team) {
    $teamName = $team->groupText . "_" . $team->teamId;
    createCalendarForTeam($team, $teamName, $api);
}

function createCalendarForTeam($team, $teamName, $api) {
    $teamCalendar = new Calendar($teamName, $team->teamId);

    // get future games from SHV api
    $gamesPlanned = $api->getPlannedGames($team->teamId);
    $gamesPlayed = $api->getPlayedGames($team->teamId);
    $teamCalendar->setGames(array_merge($gamesPlayed, $gamesPlanned));
    $teamCalendar->save();
}

