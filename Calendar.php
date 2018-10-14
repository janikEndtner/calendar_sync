<?php
/**
 * Created by PhpStorm.
 * User: janik
 * Date: 11-Oct-18
 * Time: 12:17
 */

require_once 'ApiClient.php';

class Calendar
{
    private $calendar;
    private $games;

    public function __construct($calendar)
    {
        $this->calendar = $calendar;
    }
    public function setGames(array $games) {
        $this->games = $games;
    }
    public function adaptFutureGamesIfNecessary() {
        $gamesInCalendar = ApiClient::getFutureEvents($this->calendar->getId());

        foreach ($gamesInCalendar as $gic) {
            $found = null;
            foreach ($this->games as $key=>$g) {
                $dateGic = new DateTime($gic->getStart()->dateTime);
                $dateG = new DateTime($g->gameDateTime);
                $a = $dateGic->format("Y-m-d\TH:i:s.u") == $dateG->format('Y-m-d\TH:i:s.u');
                $b = $gic->summary == $this->createSummary($g);
                $c = $gic->location == $this->createLocation($g);
                if ($a && $b && $c) {
                        $found = $key;
                }
            }
            if ($found != null) {
                unset($this->games[$found]);
            } else {
                ApiClient::removeEvent($gic->id, $this->calendar->getId());
            }
        }
        // add remaining games into calendar
        foreach ($this->games as $game) {
            $summary = $this->createSummary($game);
            $endDate = $this->createEndDate($game);
            $location = $this->createLocation($game);
            ApiClient::addEvent($game->gameDateTime, $endDate->format('c'), $summary, $location, $this->calendar->getId());
        }

    }

    private function createSummary($game) {
        return $game->teamAName . " vs. " . $game->teamBName;
    }
    private function createEndDate($game) {
        $endDate = new DateTime($game->gameDateTime, new DateTimeZone('Europe/Zurich'));
        return $endDate->add(new DateInterval('PT2H'));
    }
    private function createLocation($game) {
        return $game->venue . " \n " . $game->venueAddress . ", " . $game->venueZip . " " . $game->venueCity;
    }
}